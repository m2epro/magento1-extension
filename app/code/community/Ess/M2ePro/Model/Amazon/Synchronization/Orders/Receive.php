<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Orders_Receive
    extends Ess_M2ePro_Model_Amazon_Synchronization_Orders_Abstract
{
    const LOCK_ITEM_PREFIX = 'synchronization_amazon_orders_receive';

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

    //########################################

    protected function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        $iteration = 0;
        $percentsForOneAccount = $this->getPercentsInterval() / count($permittedAccounts);

        foreach ($permittedAccounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            // ---------------------------------------
            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Receive" Action for Amazon Account: "%account_title%" is started. Please wait...
            $status = 'The "Receive" Action for Amazon Account: "%account_title%" is started. Please wait...';
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
            // The "Receive" Action for Amazon Account: "%account_title%" is finished. Please wait...
            $status = 'The "Receive" Action for Amazon Account: "%account_title%" is finished. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneAccount);
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
        $fromDate = $this->prepareFromDate($account->getData('orders_last_synchronization'));
        $params = array(
            'from_date' => $fromDate
        );

        /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
        $amazonAccount = $account->getChildObject();

        if (is_null($amazonAccount->getData('orders_last_synchronization'))) {
            $amazonAccount->setData('orders_last_synchronization', $fromDate)->save();
        }

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector('orders', 'receive', 'requester',
                                                        $params, $account, 'Ess_M2ePro_Model_Amazon_Synchronization');
        $dispatcherObject->process($connectorObj);
    }

    //########################################

    private function prepareFromDate($lastFromDate)
    {
        // Get last from date
        // ---------------------------------------
        if (is_null($lastFromDate)) {
            $lastFromDate = new DateTime('now', new DateTimeZone('UTC'));
        } else {
            $lastFromDate = new DateTime($lastFromDate, new DateTimeZone('UTC'));
        }
        // ---------------------------------------

        // Get min date for synch
        // ---------------------------------------
        $minDate = new DateTime('now',new DateTimeZone('UTC'));
        $minDate->modify('-7 days');
        // ---------------------------------------

        // Prepare last date
        // ---------------------------------------
        if ((int)$lastFromDate->format('U') < (int)$minDate->format('U')) {
            $lastFromDate = $minDate;
        }
        // ---------------------------------------

        return $lastFromDate->format('Y-m-d H:i:s');
    }

    //########################################
}