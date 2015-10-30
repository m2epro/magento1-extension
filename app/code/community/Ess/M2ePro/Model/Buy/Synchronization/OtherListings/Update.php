<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Buy_Synchronization_OtherListings_Update
    extends Ess_M2ePro_Model_Buy_Synchronization_OtherListings_Abstract
{
    const LOCK_ITEM_PREFIX = 'synchronization_buy_other_listings_update';

    //########################################

    protected function getNick()
    {
        return '/update/';
    }

    protected function getTitle()
    {
        return 'Update';
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    // ---------------------------------------

    protected function intervalIsEnabled()
    {
        return true;
    }

    protected function intervalIsLocked()
    {
        if ($this->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_USER ||
            $this->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER) {
            return false;
        }

        if (!in_array(Ess_M2ePro_Model_Synchronization_Task_Abstract::DEFAULTS, $this->getAllowedTasksTypes())) {
            return parent::intervalIsLocked();
        }

        $operationHistoryParentId = $this->getParentOperationHistory()->getObject()->getData('parent_id');
        if (empty($operationHistoryParentId)) {
            return parent::intervalIsLocked();
        }

        $operationHistoryObject = Mage::getModel('M2ePro/OperationHistory')->loadInstance(
            $operationHistoryParentId
        );

        $synchronizationStartTime = $operationHistoryObject->getData('start_date');
        $updateListingsProductsLastTime = $this->getConfigValue(
            '/buy/defaults/update_listings_products/', 'last_time'
        );

        return strtotime($synchronizationStartTime) > strtotime($updateListingsProductsLastTime);
    }

    //########################################

    protected function performActions()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
            Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $accounts = $accountsCollection->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = ($this->getPercentsInterval() / 2) / count($accounts);

        /** @var $account Ess_M2ePro_Model_Account **/
        foreach ($accounts as $account) {

            $this->processAccountInventory($account);

            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    //########################################

    private function processAccountInventory(Ess_M2ePro_Model_Account $account)
    {
        $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
        // M2ePro_TRANSLATIONS
        // The "3rd Party Listings" Action for Rakuten.com Account: "%account_title%" is started. Please wait...
        $status = 'The "3rd Party Listings" Action for Rakuten.com Account: "%account_title%" is started.';
        $status .= ' Please wait...';
        $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

        if (!$this->isLockedAccount($account)) {

            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Process Account '.$account->getTitle()
            );

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
            $connectorObj = $dispatcherObject->getConnector('otherListings', 'update' ,'requester',
                array(), $account, 'Ess_M2ePro_Model_Buy_Synchronization');

            $dispatcherObject->process($connectorObj);

            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
        }

        // M2ePro_TRANSLATIONS
        // The "3rd Party Listings" Action for Rakuten.com Account: "%account_title%" is finished. Please wait...
        $status = 'The "3rd Party Listings" Action for Rakuten.com Account: "%account_title%" is finished.';
        $status .= ' Please wait...';
        $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
    }

    //########################################

    private function isLockedAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$account->getId());
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        return $lockItem->isExist();
    }

    //########################################
}