<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Orders_Update
    extends Ess_M2ePro_Model_Ebay_Synchronization_Orders_Abstract
{
    const MAX_UPDATES_PER_TIME = 200;

    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/update/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Update';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 100;
    }

    //########################################

    protected function performActions()
    {
        $this->deleteNotActualChanges();

        $permittedAccounts = $this->getPermittedAccounts();

        if (count($permittedAccounts) <= 0) {
            return;
        }

        $iteration = 1;
        $percentsForOneStep = $this->getPercentsInterval() / count($permittedAccounts);

        foreach ($permittedAccounts as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Update" Action for eBay Account: "%account_title%" is started. Please wait...
            $status = 'The "Update" Action for eBay Account: "%account_title%" is started. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            // ---------------------------------------

            $this->processAccount($account);

            // ---------------------------------------
            // M2ePro_TRANSLATIONS
            // The "Update" Action for eBay Account: "%account_title%" is finished. Please wait...
            $status = 'The "Update" Action for eBay Account: "%account_title%" is finished. Please wait...';
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
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ---------------------------------------

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $changes = $this->getRelatedChanges($account);
        if (empty($changes)) {
            return;
        }

        foreach ($changes as $change) {
            $this->processChange($change);
        }
    }

    //########################################

    private function getRelatedChanges(Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Order_Change_Collection $changesCollection */
        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->setPageSize(self::MAX_UPDATES_PER_TIME);
        $changesCollection->getSelect()->group(array('order_id'));

        return $changesCollection->getItems();
    }

    // ---------------------------------------

    private function processChange(Ess_M2ePro_Model_Order_Change $change)
    {
        Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount(array($change->getId()));

        if ($change->isPaymentUpdateAction()) {

            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Ebay')->getModel('Order')->load($change->getOrderId());
            $order->getId() && $order->getChildObject()->updatePaymentStatus();

            return;
        }

        if ($change->isShippingUpdateAction()) {
            $changeParams = $change->getParams();
            $params = array();

            if (!empty($changeParams['tracking_details'])) {
                $params = $changeParams['tracking_details'];
            }

            if (!empty($changeParams['item_id'])) {

                /** @var Ess_M2ePro_Model_Order_Item $item */
                $item = Mage::helper('M2ePro/Component_Ebay')->getModel('Order_Item')->load($changeParams['item_id']);
                $item->getId() && $item->getChildObject()->updateShippingStatus($params);
            } else {

                /** @var Ess_M2ePro_Model_Order $order */
                $order = Mage::helper('M2ePro/Component_Ebay')->getModel('Order')->load($change->getOrderId());
                $order->getId() && $order->getChildObject()->updateShippingStatus($params);
            }
        }
    }

    //########################################

    private function deleteNotActualChanges()
    {
        Mage::getResourceModel('M2ePro/Order_Change')->deleteByProcessingAttemptCount(
            Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
            Ess_M2ePro_Helper_Component_Ebay::NICK
        );
    }

    //########################################
}