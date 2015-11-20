<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Defaults_UpdateRepricing
    extends Ess_M2ePro_Model_Amazon_Synchronization_Defaults_Abstract
{
    const LOCK_ITEM_PREFIX = 'synchronization_amazon_default_update_repricing';

    //########################################

    protected function getNick()
    {
        return '/update_repricing/';
    }

    protected function getTitle()
    {
        return 'Synchronization with Repricing Service';
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 80;
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

    protected function isPossibleToRun()
    {
        if (!parent::isPossibleToRun()) {
            return false;
        }

        return Mage::helper('M2ePro/Component_Amazon')->isRepricingEnabled();
    }

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
            // The "Update Repricing" Action for Amazon Account: "%account_title%" is started. Please wait...
            $status = 'The "Synchronization with Repricing Service" Action for Amazon Account: "%account_title%" ';
            $status .= 'is started. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            if (!$this->isLockedAccount($account)) {

                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process Account '.$account->getTitle()
                );

                /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
                $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $account);
                $repricing->synchronize();

                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }

            // M2ePro_TRANSLATIONS
            // The "Update Listings Products" Action for Amazon Account: "%account_title%" is finished. Please wait...
            $status = 'The "Synchronization with Repricing Service" Action for Amazon Account: "%account_title%" ';
            $status .= 'is finished. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
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