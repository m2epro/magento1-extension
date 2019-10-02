<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Other_Channel_SynchronizeData extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/listing/other/channel/synchronize_data';

    const LOCK_ITEM_PREFIX = 'synchronization_ebay_other_listings_update';

    //####################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $synchronizationLog;
    }

    //########################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    protected function performActions()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountsCollection->addFieldToFilter(
            'other_listings_synchronization',
            Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES
        );

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
                    $this->executeUpdateInventoryDataAccount($account);
                } catch (Exception $exception) {
                    $message = Mage::helper('M2ePro')->__(
                        'The "Update 3rd Party Listings" Action for eBay Account "%account%" was completed with error.',
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

    protected function executeUpdateInventoryDataAccount(Ess_M2ePro_Model_Account $account)
    {
        $sinceTime = $account->getData('other_listings_last_synchronization');

        if (empty($sinceTime)) {
            $marketplaceCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace');
            $marketplaceCollection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $marketplace = $marketplaceCollection->getFirstItem();

            if (!$marketplace->getId()) {
                $marketplace = Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_US;
            }

            $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getCustomConnector(
                'Cron_Task_Ebay_Listing_Other_Channel_SynchronizeData_Requester',
                array(), $marketplace, $account
            );
            $dispatcherObject->process($connectorObj);
            return;
        }

        $sinceTime = $this->prepareSinceTime($sinceTime);
        $changes = $this->getChangesByAccount($account, $sinceTime);

        /** @var $updatingModel Ess_M2ePro_Model_Ebay_Listing_Other_Updating */
        $updatingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Updating');
        $updatingModel->initialize($account);
        $updatingModel->processResponseData($changes);
    }

    //########################################

    protected function getChangesByAccount(Ess_M2ePro_Model_Account $account, $sinceTime)
    {
        $nextSinceTime = new DateTime($sinceTime, new DateTimeZone('UTC'));

        $operationHistory = $this->getOperationHistory()->getParentObject('synchronization');
        if ($operationHistory !== null) {
            $toTime = new DateTime($operationHistory->getData('start_date'), new DateTimeZone('UTC'));
        } else {
            $toTime = new DateTime('now', new DateTimeZone('UTC'));
        }

        $toTime->modify('-1 hour');

        if ((int)$nextSinceTime->format('U') >= (int)$toTime->format('U')) {
            $nextSinceTime = $toTime;
            $nextSinceTime->modify('-1 minute');
        }

        $response = $this->receiveChangesFromEbay(
            $account,
            array('since_time'=>$nextSinceTime->format('Y-m-d H:i:s'), 'to_time' => $toTime->format('Y-m-d H:i:s'))
        );

        if ($response) {
            return (array)$response;
        }

        $previousSinceTime = $nextSinceTime;

        $nextSinceTime = new DateTime('now', new DateTimeZone('UTC'));
        $nextSinceTime->modify('-1 day');

        if ($previousSinceTime->format('U') < $nextSinceTime->format('U')) {
            $response = $this->receiveChangesFromEbay(
                $account,
                array('since_time'=>$nextSinceTime->format('Y-m-d H:i:s'), 'to_time' => $toTime->format('Y-m-d H:i:s'))
            );

            if ($response) {
                return (array)$response;
            }

            $previousSinceTime = $nextSinceTime;
        }

        $nextSinceTime = new DateTime('now', new DateTimeZone('UTC'));
        $nextSinceTime->modify('-2 hours');

        if ($previousSinceTime->format('U') < $nextSinceTime->format('U')) {
            $response = $this->receiveChangesFromEbay(
                $account,
                array('since_time'=>$nextSinceTime->format('Y-m-d H:i:s'), 'to_time' => $toTime->format('Y-m-d H:i:s'))
            );

            if ($response) {
                return (array)$response;
            }
        }

        return array();
    }

    protected function receiveChangesFromEbay(Ess_M2ePro_Model_Account $account, array $paramsConnector = array())
    {
        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'item', 'get', 'changes',
            $paramsConnector, NULL,
            NULL, $account->getId()
        );

        $dispatcherObj->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        $this->processResponseMessages($connectorObj->getResponseMessages());

        if (!isset($responseData['items']) || !isset($responseData['to_time'])) {
            return NULL;
        }

        return $responseData;
    }

    protected function processResponseMessages(array $messages)
    {
        /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messagesSet */
        $messagesSet = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
        $messagesSet->init($messages);

        foreach ($messagesSet->getEntities() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    //########################################

    protected function prepareSinceTime($sinceTime)
    {
        $minTime = new DateTime('now', new DateTimeZone('UTC'));
        $minTime->modify("-1 month");

        if (empty($sinceTime) || strtotime($sinceTime) < (int)$minTime->format('U')) {
            $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $sinceTime = $sinceTime->format('Y-m-d H:i:s');
        }

        return $sinceTime;
    }

    protected function isLockedAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var $lockItem Ess_M2ePro_Model_Lock_Item_Manager */
        $lockItemManager = Mage::getModel(
            'M2ePro/Lock_Item_Manager', array('nick' => self::LOCK_ITEM_PREFIX.'_'.$account->getId())
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

    //########################################
}
