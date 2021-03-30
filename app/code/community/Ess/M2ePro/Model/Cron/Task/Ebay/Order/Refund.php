<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Order_Refund extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/order/refund';

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

            /** @var Ess_M2ePro_Model_Account $account */

            // ---------------------------------------
            $this->getOperationHistory()->addText('Starting account "' . $account->getTitle() . '"');
            // ---------------------------------------

            // ---------------------------------------
            $this->getOperationHistory()->addTimePoint(
                __METHOD__ . 'process' . $account->getId(),
                'Process account ' . $account->getTitle()
            );
            // ---------------------------------------

            try {
                $this->processAccount($account);
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Refund" Action for eBay Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            // ---------------------------------------
            $this->getOperationHistory()->saveTimePoint(__METHOD__ . 'process' . $account->getId());
            // ---------------------------------------
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
        $relatedChanges = $this->getRelatedChanges($account);
        if (empty($relatedChanges)) {
            return;
        }

        Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount(array_keys($relatedChanges));

        /** @var $dispatcherObject Ess_M2ePro_Model_Ebay_Connector_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');

        $failedChangesIds = array();

        foreach ($relatedChanges as $change) {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', $change->getOrderId());

            if (!$order->getChildObject()->canRefund()) {
                $failedChangesIds[] = $change->getId();
                continue;
            }

            // ---------------------------------------

            $cancelParams = array(
                'order_id'  => $change->getOrderId(),
                'change_id' => $change->getId(),
            );

            $connectorObj = $dispatcherObject->getCustomConnector(
                'Ebay_Connector_Order_Cancellation_Cancel',
                $cancelParams,
                $order->getMarketplaceId(),
                $order->getAccountId()
            );
            $dispatcherObject->process($connectorObj);

            $cancelResponseData = $connectorObj->getRequestData();

            if (!$cancelResponseData['result'] && $cancelResponseData['cancelId'] === null) {
                continue;
            }

            // ---------------------------------------

            $refundParams = array(
                'order_id'   => $change->getOrderId(),
                'change_id'  => $change->getId(),
                'cancelId'   => $cancelResponseData['cancelId'],
                'refundDate' => new DateTime($change->getData('create_date'), new DateTimeZone('UTC'))
            );

            $connectorObj = $dispatcherObject->getCustomConnector(
                'Ebay_Connector_Order_Cancellation_Refund',
                $refundParams,
                $order->getMarketplaceId(),
                $order->getAccountId()
            );
            $dispatcherObject->process($connectorObj);
        }

        if (!empty($failedChangesIds)) {
            Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds($failedChangesIds);
        }
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @return Ess_M2ePro_Model_Order_Change[]
     */
    protected function getRelatedChanges(Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Resource_Order_Change_Collection $changesCollection */
        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Ebay::NICK);
        $changesCollection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_REFUND);
        $changesCollection->getSelect()->joinLeft(
            array('pl' => Mage::getResourceModel('M2ePro/Processing_Lock')->getMainTable()),
            'pl.object_id = main_table.order_id AND pl.model_name = \'M2ePro/Order\'',
            array()
        );
        $changesCollection->addFieldToFilter('pl.id', array('null' => true));
        $changesCollection->getSelect()->group(array('order_id'));

        return $changesCollection->getItems();
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
