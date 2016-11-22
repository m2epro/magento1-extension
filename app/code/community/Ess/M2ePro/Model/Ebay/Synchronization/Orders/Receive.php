<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Orders_Receive
    extends Ess_M2ePro_Model_Ebay_Synchronization_Orders_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/receive/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Orders Receive';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        $iteration = 1;
        $percentsForOneAccount = $this->getPercentsInterval() / count($permittedAccounts);

        foreach ($permittedAccounts as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$account->getId(),'Get Orders from eBay');

            // M2ePro_TRANSLATIONS
            // The "Receive" Action for eBay Account "%account_title%" is in data receiving state...
            $status = 'The "Receive" Action for eBay Account "%account_title%" is in data receiving state...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            // ---------------------------------------

            $ebayOrders = $this->processEbayOrders($account);

            $this->getActualLockItem()->setPercents(
                $this->getPercentsStart() + $iteration * $percentsForOneAccount * 0.3
            );

            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$account->getId());
            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'create_magento_orders'.$account->getId(),
                'Create Magento Orders'
            );

            // M2ePro_TRANSLATIONS
            // The "Receive" Action for eBay Account "%account_title%" is in Order Creation state...
            $status = 'The "Receive" Action for eBay Account "%account_title%" is in Order Creation state...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            // ---------------------------------------

            if (count($ebayOrders) > 0) {
                $percentsForOneOrder = (int)(($this->getPercentsStart() + $iteration * $percentsForOneAccount * 0.7)
                    / count($ebayOrders));

                $this->createMagentoOrders($ebayOrders, $percentsForOneOrder);
            }

            // ---------------------------------------
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'create_magento_orders'.$account->getId());

            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneAccount);
            $this->getActualLockItem()->activate();
            // ---------------------------------------

            $iteration++;
        }
    }

    //########################################

    private function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    private function processEbayOrders($account)
    {
        $fromTime = $this->prepareFromTime($account);
        $toTime = $this->prepareToDate();

        if (strtotime($fromTime) >= strtotime($toTime)) {
            $fromTime = new DateTime($toTime);
            $fromTime->modify('- 5 minutes');

            $fromTime = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($fromTime);
        }

        $params = array(
            'from_update_date' => $fromTime,
            'to_update_date'=> $toTime
        );

        $jobToken = $account->getData('job_token');
        if (!empty($jobToken)) {
            $params['job_token'] = $jobToken;
        }

        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getConnector('order', 'receive', 'items', $params, NULL, $account);

        $response = $dispatcherObj->process($connectorObj);
        $this->processResponseMessages($connectorObj);

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$account->getId());

        if (!isset($response['items']) || !isset($response['to_update_date'])) {
            return array();
        }

        $orders = array();

        foreach ($response['items'] as $ebayOrderData) {
            /** @var $ebayOrder Ess_M2ePro_Model_Ebay_Order_Builder */
            $ebayOrder = Mage::getModel('M2ePro/Ebay_Order_Builder');
            $ebayOrder->initialize($account, $ebayOrderData);

            $orders[] = $ebayOrder->process();
        }

        /** @var Ess_M2ePro_Model_Ebay_Account $ebayAccount */
        $ebayAccount = $account->getChildObject();

        if (!empty($response['job_token'])) {
            $ebayAccount->setData('job_token', $response['job_token']);
            $ebayAccount->setData('orders_last_synchronization', $response['to_update_date']);
        } else {
            $ebayAccount->setData('job_token', NULL);
            $ebayAccount->setData('orders_last_synchronization', $response['to_update_date']);
        }

        $ebayAccount->save();

        return array_filter($orders);
    }

    private function processResponseMessages(Ess_M2ePro_Model_Connector_Protocol $connectorObj)
    {
        foreach ($connectorObj->getErrorMessages() as $message) {

            if (!$connectorObj->isMessageError($message) && !$connectorObj->isMessageWarning($message)) {
                continue;
            }

            $logType = $connectorObj->isMessageError($message) ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                                               : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getLog()->addMessage(
                Mage::helper('M2ePro')->__($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    private function createMagentoOrders($ebayOrders, $percentsForOneOrder)
    {
        $iteration = 1;
        $currentPercents = $this->getActualLockItem()->getPercents();

        foreach ($ebayOrders as $order) {
            /** @var $order Ess_M2ePro_Model_Order */

            if ($order->canCreateMagentoOrder()) {
                try {
                    $order->createMagentoOrder();
                } catch (Exception $exception) {
                    continue;
                }
            }

            if ($order->getReserve()->isNotProcessed() && $order->isReservable()) {
                $order->getReserve()->place();
            }

            if ($order->getChildObject()->canCreatePaymentTransaction()) {
                $order->getChildObject()->createPaymentTransactions();
            }
            if ($order->getChildObject()->canCreateInvoice()) {
                $order->createInvoice();
            }
            if ($order->getChildObject()->canCreateShipment()) {
                $order->createShipment();
            }
            if ($order->getChildObject()->canCreateTracks()) {
                $order->getChildObject()->createTracks();
            }
            if ($order->getStatusUpdateRequired()) {
                $order->updateMagentoOrderStatus();
            }

            $currentPercents = $currentPercents + $percentsForOneOrder * $iteration;
            $this->getActualLockItem()->setPercents($currentPercents);

            if ($iteration % 5 == 0) {
                $this->getActualLockItem()->activate();
            }
        }
    }

    //########################################

    private function prepareFromTime(Ess_M2ePro_Model_Account $account)
    {
        $lastSynchronizationDate = $account->getData('orders_last_synchronization');

        if (is_null($lastSynchronizationDate)) {
            $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $sinceTime = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($sinceTime);

            /** @var Ess_M2ePro_Model_Ebay_Account $ebayAccount */
            $ebayAccount = $account->getChildObject();
            $ebayAccount->setData('orders_last_synchronization', $sinceTime)->save();

            return $sinceTime;
        }

        $sinceTime = new DateTime($lastSynchronizationDate, new DateTimeZone('UTC'));

        // Get min date for synch
        // ---------------------------------------
        $minDate = new DateTime('now',new DateTimeZone('UTC'));
        $minDate->modify('-90 days');
        // ---------------------------------------

        // Prepare last date
        // ---------------------------------------
        if ((int)$sinceTime->format('U') < (int)$minDate->format('U')) {
            $sinceTime = $minDate;
        }
        // ---------------------------------------

        return Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($sinceTime);
    }

    private function prepareToDate()
    {
        $operationHistory = $this->getActualOperationHistory()->getParentObject('synchronization');
        if (!is_null($operationHistory)) {
            $toDate = $operationHistory->getData('start_date');
        } else {
            $toDate = new DateTime('now', new DateTimeZone('UTC'));
            $toDate = $toDate->format('Y-m-d H:i:s');
        }

        return Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($toDate);
    }

    //########################################
}