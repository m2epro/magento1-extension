<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Amazon_Listing_SynchronizeInventory_ProcessingRunner as ProcessingRunner;

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_SynchronizeInventory
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK                         = 'amazon/listing/synchronize_inventory';
    const DEFAULT_INTERVAL_PER_ACCOUNT = 86400;

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
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_LISTINGS);

        return $synchronizationLog;
    }

    //########################################

    /**
     * @throws Exception
     */
    protected function performActions()
    {
        if ($this->isTaskInProgress()) {
            return;
        }

        $account = $this->getAccountForProcess();

        if (!$account->getId()) {
            return;
        }

        $this->getOperationHistory()->addText('Starting Account "' . $account->getTitle() . '"');
        $this->getOperationHistory()->addTimePoint(
            __METHOD__ . 'process' . $account->getId(),
            'Process Account ' . $account->getTitle()
        );

        try {

            $params = array();

            if ($account->getChildObject()->getOtherListingsSynchronization() &&
                !$this->isFullItemsDataAlreadyReceived($account)
            ) {
                $params['full_items_data'] = true;

                $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($account->getAdditionalData());
                $additionalData['is_amazon_other_listings_full_items_data_already_received'] = true;
                $account->setSettings('additional_data', $additionalData)->save();
            }

            /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher $dispatcherObject */
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Cron_Task_Amazon_Listing_SynchronizeInventory_Requester',
                $params,
                $account
            );
            $dispatcherObject->process($connectorObj);
        } catch (Exception $exception) {
            $message = 'The "Inventory Synchronize" Action for Amazon Account "%account%"';
            $message .= ' was completed with error.';
            $message = Mage::helper('M2ePro')->__($message, $account->getTitle());

            $this->processTaskAccountException($message, __FILE__, __LINE__);
            $this->processTaskException($exception);
        }

        $this->getOperationHistory()->saveTimePoint(__METHOD__ . 'process' . $account->getId());
    }

    /**
     * @return Ess_M2ePro_Model_Account
     * @throws Exception
     */
    protected function getAccountForProcess()
    {
        $interval = $this->getConfigValue('interval_per_account') !== null
            ? $this->getConfigValue('interval_per_account')
            : self::DEFAULT_INTERVAL_PER_ACCOUNT;

        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify('-' . $interval . ' seconds');

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $collection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'main_table.id = l.account_id',
            array()
        );
        $collection->addFieldToFilter(
            'second_table.inventory_last_synchronization',
            array(
                array('lt' => $date->format('Y-m-d H:i:s')),
                array('null' => true)
            )
        );
        $collection->getSelect()->where('l.id IS NOT NULL OR second_table.other_listings_synchronization = 1');
        $collection->getSelect()->group('main_table.id');
        $collection->getSelect()->order(new Zend_Db_Expr('second_table.inventory_last_synchronization ASC'));

        return $collection->getFirstItem();
    }

    //########################################

    /**
     * @return bool
     */
    protected function isTaskInProgress()
    {
        /** @var $lockItemManager Ess_M2ePro_Model_Lock_Item_Manager */
        $lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager',
            array('nick' => ProcessingRunner::LOCK_ITEM_PREFIX)
        );

        if (!$lockItemManager->isExist()) {
            return false;
        }

        if ($lockItemManager->isInactiveMoreThanSeconds(Ess_M2ePro_Model_Processing_Runner::MAX_LIFETIME)) {
            $lockItemManager->remove();

            return false;
        }

        return true;
    }

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @return bool
     */
    protected function isFullItemsDataAlreadyReceived(Ess_M2ePro_Model_Account $account)
    {
        $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($account->getAdditionalData());

        return !empty($additionalData['is_amazon_other_listings_full_items_data_already_received']);
    }

    //########################################
}
