<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_PickupStore_UpdateOnChannel extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/pickup_store/update_on_channel';

    const MAX_ITEMS_COUNT = 10000;

    //########################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    public function performActions()
    {
        $account = Mage::helper('M2ePro/Component_Ebay_PickupStore')->getEnabledAccount();
        if (!$account) {
            return;
        }

        $this->getOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');

        $this->getOperationHistory()->addTimePoint(
            __METHOD__.'process'.$account->getId(),
            'Process Account '.$account->getTitle()
        );

        $this->processAccount($account);

        $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());

        $this->getLockItemManager()->activate();
    }

    //########################################

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $collection = Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State_Collection');
        $collection->getSelect()->where('(is_deleted = 1) OR (target_qty != online_qty)');
        $collection->addFieldToFilter('is_in_processing', 0);

        $collection->getSelect()->joinLeft(
            array('eaps' => Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore')->getMainTable()),
            'eaps.id = main_table.account_pickup_store_id',
            array('account_id')
        );

        $collection->addFieldToFilter('eaps.account_id', $account->getId());

        $collection->getSelect()->limit(self::MAX_ITEMS_COUNT);

        $pickupStoreStateItems = $collection->getItems();
        if (empty($pickupStoreStateItems)) {
            return;
        }

        $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');

        /** @var Ess_M2ePro_Model_Ebay_Connector_AccountPickupStore_Synchronize_ProductsRequester $connector */
        $connector = $dispatcher->getConnector(
            'accountPickupStore', 'synchronize', 'productsRequester', array(), NULL, $account
        );
        $connector->setPickupStoreStateItems($pickupStoreStateItems);
        $dispatcher->process($connector);
    }

    //########################################
}