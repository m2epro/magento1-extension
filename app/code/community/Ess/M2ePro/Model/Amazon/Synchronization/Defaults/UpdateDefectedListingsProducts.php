<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateDefectedListingsProducts
    extends Ess_M2ePro_Model_Amazon_Synchronization_Defaults_Abstract
{
    const INTERVAL_COEFFICIENT_VALUE = 50000;
    const LOCK_ITEM_PREFIX = 'synchronization_amazon_default_update_defected_listings_products';

    //####################################

    protected function getNick()
    {
        return '/update_defected_listings_products/';
    }

    protected function getTitle()
    {
        return 'Update Defected Listings Products';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 10;
    }

    protected function getPercentsEnd()
    {
        return 40;
    }

    // -----------------------------------

    protected function intervalIsEnabled()
    {
        return true;
    }

    protected function intervalIsLocked()
    {
        if ($this->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER) {
            return false;
        }

        return parent::intervalIsLocked();
    }

    //####################################

    protected function performActions()
    {
        $accounts = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account')->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = $this->getPercentsInterval() / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Update Defected Listings Products" Action for Amazon Account: "%account_title%" is started. Please wait...
            $status = 'The "Update Defected Listings Products" Action for Amazon Account: ';
            $status .= '"%account_title%" is started. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            if (!$this->isLockedAccount($account)) {

                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process Account '.$account->getTitle()
                );

                /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
                $collection = Mage::getModel('M2ePro/Listing')->getCollection();
                $collection->addFieldToFilter('component_mode',Ess_M2ePro_Helper_Component_Amazon::NICK);
                $collection->addFieldToFilter('account_id',(int)$account->getId());

                if ($collection->getSize()) {

                    $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
                    $connectorObj = $dispatcherObject->getConnector(
                        'defaults', 'updateDefectedListingsProducts' ,'requester',
                        array(), $account, 'Ess_M2ePro_Model_Amazon_Synchronization'
                    );
                    $dispatcherObject->process($connectorObj);
                }

                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }

            // M2ePro_TRANSLATIONS
            // The "Update Defected Listings Products" Action for Amazon Account: "%account_title%" is finished. Please wait...
            $status = 'The "Update Defected Listings Products" Action for Amazon Account: ';
            $status .= '"%account_title%" is finished. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    //####################################

    private function isLockedAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$account->getId());
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        return $lockItem->isExist();
    }

    //####################################
}