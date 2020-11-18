<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Walmart_Listing_SynchronizeInventory_ProcessingRunner as ProcessingRunner;

class Ess_M2ePro_Model_Cron_Task_Walmart_Listing_SynchronizeInventory
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'walmart/listing/synchronize_inventory';

    const DEFAULT_INTERVAL_PER_ACCOUNT = 86400;
    const QUICKER_INTERVAL_PER_ACCOUNT = 7200;

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
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function performActions()
    {
        if ($this->isTaskInProgress()) {
            return;
        }

        if (!$account = $this->getAccountForProcess()) {
            return;
        }

        $this->getOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
        $this->getOperationHistory()->addTimePoint(
            __METHOD__.'process'.$account->getId(),
            'Process Account '.$account->getTitle()
        );

        try {
            $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Cron_Task_Walmart_Listing_SynchronizeInventory_Requester', array(), $account
            );
            $dispatcherObject->process($connectorObj);
        } catch (Exception $exception) {
            $message = 'The "Synchronize Inventory" Action for Walmart Account "%account%"';
            $message .= ' was completed with error.';
            $message = Mage::helper('M2ePro')->__($message, $account->getTitle());

            $this->processTaskAccountException($message, __FILE__, __LINE__);
            $this->processTaskException($exception);
        }

        $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
    }

    /**
     * @return bool|Ess_M2ePro_Model_Account
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Exception
     */
    protected function getAccountForProcess()
    {
        /**
         * Trying to get online data somewhat quicker after a successful List action
         */
        $quickerDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $quickerDate->modify('-' . self::QUICKER_INTERVAL_PER_ACCOUNT . ' seconds');

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');
        $collection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'main_table.id = l.account_id',
            array()
        );
        $collection->addFieldToFilter(
            'second_table.inventory_last_synchronization',
            array(
                array('lt' => $quickerDate->format('Y-m-d H:i:s')),
                array('null' => true)
            )
        );
        $collection->getSelect()->where('l.id IS NOT NULL OR second_table.other_listings_synchronization = 1');
        $collection->getSelect()->group('main_table.id');
        $collection->getSelect()->order(new Zend_Db_Expr('second_table.inventory_last_synchronization ASC'));

        $dayAgoDate = new DateTime('now', new \DateTimeZone('UTC'));
        $dayAgoDate->modify('-' . self::DEFAULT_INTERVAL_PER_ACCOUNT . ' seconds');

        foreach ($collection->getItems() as $account) {
            /**@var Ess_M2ePro_Model_Account $account */
            if (!$account->getChildObject()->getInventoryLastSynchronization()) {
                return $account;
            }

            $lastSynchDate = new DateTime(
                $account->getChildObject()->getInventoryLastSynchronization(),
                new DateTimeZone('UTC')
            );

            if ($dayAgoDate->getTimestamp() >= $lastSynchDate->getTimestamp()) {
                return $account;
            }

            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
            $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
            $collection->joinListingTable();

            $collection->addFieldToFilter('l.account_id', (int)$account->getId());
            $collection->addFieldToFilter('list_date', array('gt' => $lastSynchDate->format('Y-m-d H:i:s')));

            if ($collection->getSize() > 0) {
                return $account;
            }
        }

        return false;
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
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_LISTINGS);

        return $synchronizationLog;
    }

    //########################################
}
