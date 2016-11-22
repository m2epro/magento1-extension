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

        foreach ($permittedAccounts as $merchantId => $accounts) {

            /** @var $account Ess_M2ePro_Model_Account **/

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
            // The "Receive" Action for Amazon Accounts: "%account_title%" is started. Please wait...
            $status = 'The "Receive" Action for Amazon Accounts: "%account_title%" is started. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $accountsTitles));
            // ---------------------------------------

            // ---------------------------------------
            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'process'.$accountsIds,
                'Process Accounts '.$accountsTitles
            );
            // ---------------------------------------

            $this->processAccounts($merchantId, $accounts);

            // ---------------------------------------
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$accountsIds);
            // ---------------------------------------

            // ---------------------------------------
            // M2ePro_TRANSLATIONS
            // The "Receive" Action for Amazon Accounts: "%account_title%" is finished. Please wait...
            $status = 'The "Receive" Action for Amazon Accounts: "%account_title%" is finished. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $accountsTitles));
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

    private function processAccounts($merchantId, array $accounts)
    {
        $updateSinceTime = Mage::getSingleton('M2ePro/Config_Synchronization')->getGroupValue(
            "/amazon/orders/receive/{$merchantId}/", "from_update_date"
        );

        $fromDate = $this->prepareFromDate($updateSinceTime);
        $toDate = $this->prepareToDate();

        if (strtotime($fromDate) >= strtotime($toDate)) {
            $fromDate = new DateTime($toDate, new DateTimeZone('UTC'));
            $fromDate->modify('- 5 minutes');

            $fromDate = $fromDate->format('Y-m-d H:i:s');
        }

        $params = array(
            'accounts' => $accounts,
            'from_update_date' => $fromDate,
            'to_update_date'=> $toDate
        );

        $jobToken = Mage::getSingleton('M2ePro/Config_Synchronization')->getGroupValue(
            "/amazon/orders/receive/{$merchantId}/", "job_token"
        );

        if (!empty($jobToken)) {
            $params['job_token'] = $jobToken;
        }

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getConnector(
            'orders', 'receive', 'requester', $params, NULL,
            'Ess_M2ePro_Model_Amazon_Synchronization'
        );
        $dispatcherObject->process($connectorObj);
    }

    private function prepareFromDate($lastFromDate)
    {
        // Get last from date
        // ---------------------------------------
        if (empty($lastFromDate)) {
            $lastFromDate = new DateTime('now', new DateTimeZone('UTC'));
        } else {
            $lastFromDate = new DateTime($lastFromDate, new DateTimeZone('UTC'));
        }
        // ---------------------------------------

        // Get min date for synch
        // ---------------------------------------
        $minDate = new DateTime('now',new DateTimeZone('UTC'));
        $minDate->modify('-30 days');
        // ---------------------------------------

        // Prepare last date
        // ---------------------------------------
        if ((int)$lastFromDate->format('U') < (int)$minDate->format('U')) {
            $lastFromDate = $minDate;
        }
        // ---------------------------------------

        return $lastFromDate->format('Y-m-d H:i:s');
    }

    private function prepareToDate()
    {
        $operationHistory = $this->getActualOperationHistory()->getParentObject('synchronization');
        if (!is_null($operationHistory)) {
            $toDate = $operationHistory->getData('start_date');
        } else {
            $toDate = new DateTime('now', new DateTimeZone('UTC'));
            $toDate = $toDate->format('Y-m-d H:i:s');
        }

        return $toDate;
    }

    //########################################
}