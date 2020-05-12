<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_Update
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/order/update';

    //####################################

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

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
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
            $this->getOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // ---------------------------------------

            // ---------------------------------------
            $this->getOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Process Account '.$account->getTitle()
            );
            // ---------------------------------------

            try {
                $this->processAccount($account);
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Update" Action for Amazon Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
        }
    }

    //########################################

    protected function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
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

        /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

        foreach ($relatedChanges as $change) {
            $changeParams = $change->getParams();

            $connectorData = array(
                'order_id'         => $change->getOrderId(),
                'change_id'        => $change->getId(),
                'amazon_order_id'  => $changeParams['amazon_order_id'],
                'tracking_number'  => isset($changeParams['tracking_number']) ? $changeParams['tracking_number'] : null,
                'carrier_name'     => isset($changeParams['carrier_title']) ? $changeParams['carrier_title'] : null,
                'carrier_code'     => isset($changeParams['carrier_code']) ? $changeParams['carrier_code'] : null,
                'fulfillment_date' => $changeParams['fulfillment_date'],
                'shipping_method'  => isset($changeParams['shipping_method']) ? $changeParams['shipping_method'] : null,
                'items'            => $changeParams['items']
            );

            $connectorObj = $dispatcherObject->getCustomConnector(
                'Cron_Task_Amazon_Order_Update_Requester',
                array('order' => $connectorData), $account
            );
            $dispatcherObject->process($connectorObj);
        }
    }

    //########################################

    protected function getRelatedChanges(Ess_M2ePro_Model_Account $account)
    {
        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $changesCollection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING);
        $changesCollection->getSelect()->joinLeft(
            array('pl' => Mage::getResourceModel('M2ePro/Processing_Lock')->getMainTable()),
            'pl.object_id = main_table.order_id AND pl.model_name = \'M2ePro/Order\'',
            array()
        );
        $changesCollection->addFieldToFilter('pl.id', array('null' => true));
        $changesCollection->getSelect()->group(array('order_id'));

        return $changesCollection->getItems();
    }

    // ---------------------------------------

    protected function deleteNotActualChanges()
    {
        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByProcessingAttemptCount(
                Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
                Ess_M2ePro_Helper_Component_Amazon::NICK
            );
    }

    //########################################
}
