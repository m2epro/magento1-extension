<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Orders_Update
    extends Ess_M2ePro_Model_Amazon_Synchronization_Orders_Abstract
{
    const LOCK_ITEM_PREFIX = 'synchronization_amazon_orders_update';

    const MAX_UPDATES_PER_TIME = 1000;

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

        foreach ($permittedAccounts as $accounts) {

            /** @var Ess_M2ePro_Model_Account $account */

            $accountsIds = array();
            $accountsTitles = array();
            foreach ($accounts as $account) {
                $accountsIds[] = $account->getId();
                $accountsTitles[] = $account->getTitle();
            }
            $accountsIds = implode(', ',$accountsIds);
            $accountsTitles = implode(', ',$accountsTitles);

            // ---------------------------------------
            $this->getActualOperationHistory()->addText('Starting Accounts "'.$accountsTitles.'"');
            // M2ePro_TRANSLATIONS
            // The "Update" Action for Amazon Accounts: "%account_title%" is started. Please wait...
            $status = 'The "Update" Action for Amazon Accounts: "%account_title%" is started. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $accountsTitles));
            // ---------------------------------------

            // ---------------------------------------
            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'process'.$accountsIds,
                'Process Accounts '.$accountsTitles
            );
            // ---------------------------------------

            $this->processAccounts($accounts);

            // ---------------------------------------
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$accountsIds);
            // ---------------------------------------

            // ---------------------------------------
            // M2ePro_TRANSLATIONS
            // The "Update" Action for Amazon Accounts: "%account_title%" is finished. Please wait...
            $status = 'The "Update" Action for Amazon Accounts: "%account_title%" is finished. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $accountsTitles));
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
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');

        $accounts = array();
        foreach ($accountsCollection->getItems() as $accountItem) {
            /** @var $accountItem Ess_M2ePro_Model_Account */

            $merchantId = $accountItem->getChildObject()->getMerchantId();
            if (!isset($accounts[$merchantId])) {
                $accounts[$merchantId] = array();
            }

            $accounts[$merchantId][] = $accountItem;
        }

        return $accounts;
    }

    // ---------------------------------------

    private function processAccounts(array $accounts)
    {
        $relatedChanges = array();

        foreach ($accounts as $index => $account) {

            $tempChanges = $this->getRelatedChanges($account);

            if (empty($tempChanges)) {
                unset($accounts[$index]);
                continue;
            }

            $relatedChanges = array_merge($relatedChanges, $tempChanges);
        }

        if (empty($relatedChanges) || empty($accounts)) {
            return;
        }

        $items = array();
        $changesIds = array();

        foreach ($relatedChanges as $change) {
            $changeParams = $change->getParams();

            $items[] = array(
                'order_id'         => $change->getOrderId(),
                'change_id'        => $change->getId(),
                'amazon_order_id'  => $changeParams['amazon_order_id'],
                'tracking_number'  => $changeParams['tracking_number'],
                'carrier_name'     => $changeParams['carrier_name'],
                'fulfillment_date' => $changeParams['fulfillment_date'],
                'shipping_method'  => isset($changeParams['shipping_method']) ? $changeParams['shipping_method'] : null,
                'items'            => $changeParams['items']
            );

            $changesIds[] = $change->getId();
        }

        if (empty($items)) {
            return;
        }

        Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount($changesIds);

        /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Amazon_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector('orders', 'update', 'itemsRequester',
                                                        array('accounts' => $accounts,
                                                              'items' => $items));
        $dispatcherObject->process($connectorObj);
    }

    //########################################

    private function getRelatedChanges(Ess_M2ePro_Model_Account $account)
    {
        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->addLockedObjectFilter('update_shipping_status');
        $changesCollection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $changesCollection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING);
        $changesCollection->setPageSize(self::MAX_UPDATES_PER_TIME);
        $changesCollection->getSelect()->group(array('order_id'));

        return $changesCollection->getItems();
    }

    // ---------------------------------------

    private function deleteNotActualChanges()
    {
        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByProcessingAttemptCount(
                Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
                Ess_M2ePro_Helper_Component_Amazon::NICK
            );
    }

    //########################################
}
