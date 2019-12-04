<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData_ProcessingRunner as ProcessingRunner;

class Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/listing/other/channel/synchronize_data';

    /**
     * @var int (in seconds)
     */
    protected $_interval = 86400;

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
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $synchronizationLog;
    }

    //########################################

    protected function performActions()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization', 1);

        $accounts = $accountsCollection->getItems();

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
                    $params = array();
                    if (!$this->isFullItemsDataAlreadyReceived($account)) {
                        $params['full_items_data'] = true;

                        $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($account->getAdditionalData());
                        $additionalData['is_amazon_other_listings_full_items_data_already_received'] = true;
                        $account->setSettings('additional_data', $additionalData)->save();
                    }

                    $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
                    $connectorObj = $dispatcherObject->getCustomConnector(
                        'Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData_Requester',
                        $params, $account
                    );

                    $dispatcherObject->process($connectorObj);
                } catch (Exception $exception) {
                    $message = Mage::helper('M2ePro')->__(
                        'The "3rd Party Listings" Action for Amazon Account "%account%" was completed with error.',
                        $account->getTitle()
                    );

                    $this->processTaskAccountException($message, __FILE__, __LINE__);
                    $this->processTaskException($exception);
                }

                $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }

            $this->getLockItemManager()->activate();
        }
    }

    //########################################

    protected function isLockedAccount(Ess_M2ePro_Model_Account $account)
    {
        $lockItemNick = ProcessingRunner::LOCK_ITEM_PREFIX.'_'.$account->getId();

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

    protected function isFullItemsDataAlreadyReceived(Ess_M2ePro_Model_Account $account)
    {
        $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($account->getAdditionalData());
        return !empty($additionalData['is_amazon_other_listings_full_items_data_already_received']);
    }

    //########################################
}
