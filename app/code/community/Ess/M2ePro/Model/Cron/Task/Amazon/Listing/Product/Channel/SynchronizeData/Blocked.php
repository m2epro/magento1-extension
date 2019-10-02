<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Blocked_ProcessingRunner as Runner;

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Blocked
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/listing/product/channel/synchronize_data/blocked';

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

    protected function performActions()
    {
        $accounts = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account')->getItems();

        if (empty($accounts)) {
            return;
        }

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');

            if (!$this->isLockedAccount($account) && !$this->isLockedAccountInterval($account)) {
                $this->getOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process Account '.$account->getTitle()
                );

                try {
                    $this->processAccount($account);
                } catch (Exception $exception) {
                    // M2ePro_TRANSLATIONS
                    // The "Update Blocked Listings Products" Action for Amazon Account: "%account%"
                    // was completed with error.
                    $message = 'The "Update Blocked Listings Products" Action for Amazon Account "%account%"';
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
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $collection->addFieldToFilter('account_id', (int)$account->getId());

        if ($collection->getSize()) {
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Blocked_Requester', array(), $account
            );
            $dispatcherObject->process($connectorObj);
        }
    }

    protected function isLockedAccount(Ess_M2ePro_Model_Account $account)
    {
        $lockItemNick = Runner::LOCK_ITEM_PREFIX.'_'.$account->getId();

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

    protected function isLockedAccountInterval(Ess_M2ePro_Model_Account $account)
    {
        if ($this->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_USER ||
            $this->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER) {
            return false;
        }

        $additionalData = Mage::helper('M2ePro')->jsonDecode($account->getAdditionalData());
        if (!empty($additionalData['last_listing_products_synchronization'])) {
            return (strtotime($additionalData['last_listing_products_synchronization'])
                   + 86400) > Mage::helper('M2ePro')->getCurrentGmtDate(true);
        }

        return false;
    }

    //########################################
}
