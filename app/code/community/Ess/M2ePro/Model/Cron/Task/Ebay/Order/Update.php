<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
use Ess_M2ePro_Model_Order_Change as Change;

class Ess_M2ePro_Model_Cron_Task_Ebay_Order_Update extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/order/update';

    const MAX_UPDATES_PER_TIME = 200;

    //########################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    //########################################

    protected function performActions()
    {
        $this->deleteNotActualChanges();

        $permittedAccounts = $this->getPermittedAccounts();

        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // ---------------------------------------

            try {
                $this->processAccount($account);
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Update" Action for eBay Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    //########################################

    protected function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    protected function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $changes = $this->getRelatedChanges($account);
        if (empty($changes)) {
            return;
        }

        foreach ($changes as $change) {
            $this->processChange($change);
        }
    }

    //########################################

    protected function getRelatedChanges(Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Resource_Order_Change_Collection $changesCollection */
        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->addFieldToFilter('action', array(
            'in' => array(
                Change::ACTION_UPDATE_SHIPPING,
                Change::ACTION_UPDATE_PAYMENT,
            ),
        ));
        $changesCollection->setPageSize(self::MAX_UPDATES_PER_TIME);
        $changesCollection->getSelect()->group(array('order_id'));

        return $changesCollection->getItems();
    }

    // ---------------------------------------

    protected function processChange(Ess_M2ePro_Model_Order_Change $change)
    {
        Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount(array($change->getId()));
        $connectorData = array('change_id' => $change->getId());

        if ($change->isPaymentUpdateAction()) {

            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Ebay')->getModel('Order')->load($change->getOrderId());

            if ($order->getId()) {
                $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Order_Dispatcher');
                $dispatcher->process(
                    Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_PAY,
                    array($order),
                    $connectorData
                );
            }

            return;
        }

        if ($change->isShippingUpdateAction()) {
            $changeParams = $change->getParams();

            $action = Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP;
            if (!empty($changeParams['tracking_number']) && !empty($changeParams['carrier_title'])) {
                $action = Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK;
                /**
                 * TODO check(rewrite) during orders refactoring.
                 * Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher expects array of order to be proccessed.
                 * But $connectorData has no link to order instance, so appears like discrepancy between these
                 * two parameters.
                 */
                $connectorData['tracking_number'] = $changeParams['tracking_number'];
                $connectorData['carrier_code']    = $changeParams['carrier_title'];
            }

            if (!empty($changeParams['item_id'])) {

                /** @var Ess_M2ePro_Model_Order_Item $item */
                $item = Mage::helper('M2ePro/Component_Ebay')->getModel('Order_Item')->load($changeParams['item_id']);

                if ($item->getId()) {
                    $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_OrderItem_Dispatcher');
                    $dispatcher->process($action, array($item), $connectorData);
                }
            } else {

                /** @var Ess_M2ePro_Model_Order $order */
                $order = Mage::helper('M2ePro/Component_Ebay')->getModel('Order')->load($change->getOrderId());

                if ($order->getId()) {
                    $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Order_Dispatcher');
                    $dispatcher->process($action, array($order), $connectorData);
                }
            }
        }
    }

    //########################################

    protected function deleteNotActualChanges()
    {
        Mage::getResourceModel('M2ePro/Order_Change')->deleteByProcessingAttemptCount(
            Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
            Ess_M2ePro_Helper_Component_Ebay::NICK
        );
    }

    //########################################
}
