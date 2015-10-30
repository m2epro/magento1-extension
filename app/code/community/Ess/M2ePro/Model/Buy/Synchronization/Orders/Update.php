<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Buy_Synchronization_Orders_Update
    extends Ess_M2ePro_Model_Buy_Synchronization_Orders_Abstract
{
    const LOCK_ITEM_PREFIX = 'synchronization_buy_orders_update';

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

        return parent::intervalIsLocked();
    }

    //########################################

    protected function performActions()
    {
        $this->deleteNotActualChanges();

        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = $this->getPercentsInterval() / count($permittedAccounts);

        foreach ($permittedAccounts as $account) {

            /** @var Ess_M2ePro_Model_Account $account */

            // ---------------------------------------
            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Update" Action for Rakuten.com Account: "%account_title%" is started. Please wait...
            $status = 'The "Update" Action for Rakuten.com Account: "%account_title%" is started. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            // ---------------------------------------

            if (!$this->isLockedAccount($account->getId())) {

                // ---------------------------------------
                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process Account '.$account->getTitle()
                );
                // ---------------------------------------

                $this->processAccount($account);

                // ---------------------------------------
                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
                // ---------------------------------------
            }

            // ---------------------------------------
            // M2ePro_TRANSLATIONS
            // The "Update" Action for Rakuten.com Account: "%account_title%" is finished. Please wait...
            $status = 'The "Update" Action for Rakuten.com Account: "%account_title%" is finished. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();
            // ---------------------------------------

            $iteration++;
        }
    }

    //########################################

    private function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    private function isLockedAccount($accountId)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$accountId);

        return $lockItem->isExist();
    }

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $relatedChanges = $this->getRelatedChanges($account);
        if (empty($relatedChanges)) {
            return;
        }

        $items = array();
        $changesIds = array();

        foreach ($relatedChanges as $change) {
            $changeParams = $change->getParams();

            $items[] = array(
                'change_id'         => $change->getId(),
                'order_id'          => $change->getOrderId(),
                'buy_order_id'      => $changeParams['buy_order_id'],
                'buy_order_item_id' => $changeParams['buy_order_item_id'],
                'qty'               => $changeParams['qty'],
                'tracking_type'     => $changeParams['tracking_type'],
                'tracking_number'   => $changeParams['tracking_number'],
                'ship_date'         => $changeParams['ship_date'],
            );

            $changesIds[] = $change->getId();
        }

        if (empty($items)) {
            return;
        }

        Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount($changesIds);

        /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Buy_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector('orders', 'update', 'shippingRequester',
                                                        array('items' => $items), $account);
        $dispatcherObject->process($connectorObj);
    }

    //########################################

    private function getRelatedChanges(Ess_M2ePro_Model_Account $account)
    {
        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Buy::NICK);
        $changesCollection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING);
        $changesCollection->getSelect()->group(array('order_id'));

        return $changesCollection->getItems();
    }

    // ---------------------------------------

    private function deleteNotActualChanges()
    {
        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByProcessingAttemptCount(
                Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
                Ess_M2ePro_Helper_Component_Buy::NICK
            );
    }

    //########################################
}
