<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_Channel_SynchronizeData_ProcessingRunner as ProcessingRunner;

class Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_Channel_SynchronizeData
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'walmart/listing/product/channel/synchronize_data';

    const QUICKER_TASK_INTERVAL = 7200;

    //####################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    /**
     * Trying to get online data somewhat quicker after a successful List action
     * @return bool
     */
    protected function isIntervalExceeded()
    {
        $lastRun = $this->getConfigValue('last_run');
        if ($lastRun === null) {
            return true;
        }

        $borderDate = new DateTime('now', new DateTimeZone('UTC'));
        $borderDate->modify('- 24 hours');

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->addFieldToFilter('list_date', array('gt' => $borderDate->format('Y-m-d H:i:s')));

        $interval = $collection->getSize() > 0 ? self::QUICKER_TASK_INTERVAL
                                               : (int)$this->getConfigValue('interval');

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        return $currentTimeStamp > strtotime($lastRun) + $interval;
    }

    //########################################

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

    protected function performActions()
    {
        $accounts = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account')->getItems();

        if (empty($accounts)) {
            return;
        }

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');

            if (!$this->isLockedAccount($account)) {
                $this->getOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process Account '.$account->getTitle()
                );

                try {
                    $this->processAccount($account);
                } catch (Exception $exception) {
                    // M2ePro_TRANSLATIONS
                    // The "Update Listings Products" Action for Walmart Account: "%account%" was completed with error.
                    $message = 'The "Update Listings Products" Action for Walmart Account "%account%"';
                    $message .= ' was completed with error.';
                    $message = Mage::helper('M2ePro')->__($message, $account->getTitle());

                    $this->processTaskAccountException($message, __FILE__, __LINE__);
                    $this->processTaskException($exception);
                }

                $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }

            $this->getLockItemManager()->activate();
        }
    }

    //########################################

    protected function processAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->addFieldToFilter('account_id', (int)$account->getId());

        if ($collection->getSize()) {
            $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Cron_Task_Walmart_Listing_Product_Channel_SynchronizeData_Requester', array(), $account
            );
            $dispatcherObject->process($connectorObj);
        }
    }

    protected function isLockedAccount(Ess_M2ePro_Model_Account $account)
    {
        $lockItemNick = ProcessingRunner::LOCK_ITEM_PREFIX .'_'. $account->getId();

        /** @var $lockItemManager Ess_M2ePro_Model_Lock_Item_Manager */
        $lockItemManager = Mage::getModel('M2ePro/Lock_Item_Manager', array('nick' => $lockItemNick));
        if (!$lockItemManager->isExist()) {
            return false;
        }

        if ($lockItemManager->isInactiveMoreThanSeconds(Ess_M2ePro_Model_Processing_Runner::MAX_LIFETIME)) {
            $lockItemManager->remove();
            return false;
        }

        return true;
    }

    //########################################
}
