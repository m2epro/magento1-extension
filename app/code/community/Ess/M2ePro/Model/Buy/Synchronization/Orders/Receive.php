<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Buy_Synchronization_Orders_Receive
    extends Ess_M2ePro_Model_Buy_Synchronization_Orders_Abstract
{
    const LOCK_ITEM_PREFIX = 'synchronization_buy_orders_receive';

    //########################################

    protected function getNick()
    {
        return '/receive/';
    }

    protected function getTitle()
    {
        return 'Receive';
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
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = $this->getPercentsInterval() / count($permittedAccounts);

        foreach ($permittedAccounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            // ---------------------------------------
            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Receive" Action for Rakuten.com Account: "%account_title%" is started. Please wait...
            $status = 'The "Receive" Action for Rakuten.com Account: "%account_title%" is started. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            // ---------------------------------------

            if (!$this->isLockedAccount($account)) {

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
            // The "Receive" Action for Rakuten.com Account: "%account_title%" is finished. Please wait...
            $status = 'The "Receive" Action for Rakuten.com Account: "%account_title%" is finished. Please wait...';
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

    private function isLockedAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$account->getId());
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        return $lockItem->isExist();
    }

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector('orders', 'receive', 'requester', array(),
                                                        $account, 'Ess_M2ePro_Model_Buy_Synchronization');
        $dispatcherObject->process($connectorObj);
    }

    //########################################
}