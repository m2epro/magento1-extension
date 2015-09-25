<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Orders_Reserve_Cancellation
    extends Ess_M2ePro_Model_Ebay_Synchronization_Orders_Abstract
{
    // ##########################################################

    protected function getNick()
    {
        return '/reserve_cancellation/';
    }

    protected function getTitle()
    {
        return 'Reserve Cancellation';
    }

    // ----------------------------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    // ----------------------------------------------------------

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

    // ##########################################################

    protected function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();

        if (count($permittedAccounts) <= 0) {
            return;
        }

        $iteration = 1;
        $percentsForOneStep = $this->getPercentsInterval() / count($permittedAccounts);

        Mage::getSingleton('M2ePro/Order_Log_Manager')->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

        foreach ($permittedAccounts as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            // ----------------------------------------------------------
            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');

            // M2ePro_TRANSLATIONS
            // The "Reserve Cancellation" Action for eBay Account: "%account_title%" is started. Please wait...'
            $status = 'The "Reserve Cancellation" Action for eBay Account: "%account_title%" is started. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            // ----------------------------------------------------------

            $this->processAccount($account);

            // ----------------------------------------------------------
            //M2ePro_TRANSLATIONS
            // The "Reserve Cancellation" Action for eBay Account: "%account_title%" is finished. Please wait...
            $status = 'The "Reserve Cancellation" Action for eBay Account: "%account_title%" is finished. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();
            // ----------------------------------------------------------

            $iteration++;
        }
    }

    // ##########################################################

    private function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        return $accountsCollection->getItems();
    }

    // ----------------------------------------------------------

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        foreach ($this->getOrdersForRelease($account) as $order) {
            /** @var Ess_M2ePro_Model_Order $order */
            $order->getReserve()->release();
        }
    }

    // ##########################################################

    private function getOrdersForRelease(Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Order_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Order')
            ->addFieldToFilter('account_id', $account->getId())
            ->addFieldToFilter('reservation_state', Ess_M2ePro_Model_Order_Reserve::STATE_PLACED);

        $reservationDays = (int)$account->getChildObject()->getQtyReservationDays();

        $minReservationStartDate = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate(), new DateTimeZone('UTC'));
        $minReservationStartDate->modify('- ' . $reservationDays . ' days');
        $minReservationStartDate = $minReservationStartDate->format('Y-m-d H:i');

        $collection->addFieldToFilter('reservation_start_date', array('lteq' => $minReservationStartDate));

        return $collection->getItems();
    }

    // ##########################################################
}