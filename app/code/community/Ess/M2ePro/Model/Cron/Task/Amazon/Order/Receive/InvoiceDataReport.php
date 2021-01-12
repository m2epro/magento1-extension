<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive_InvoiceDataReport
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/order/receive/invoice_data_report';

    /** @var int $_interval (in seconds) */
    protected $_interval = 3600;

    //####################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        return $synchronizationLog;
    }

    //########################################

    protected function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $account) {
            /** @var Ess_M2ePro_Model_Account $account */

            $this->getOperationHistory()->addText('Starting account "'.$account->getTitle().'"');

            $this->getOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Process account '.$account->getTitle()
            );

            try {
                $this->processAccount($account);
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Invoice Data Report" Action for Amazon Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }

            $this->getOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
        }
    }

    //########################################

    protected function getPermittedAccounts()
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Account_Collection $accountsCollection */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter(
            'auto_invoicing',
            Ess_M2ePro_Model_Amazon_Account::AUTO_INVOICING_VAT_CALCULATION_SERVICE
        );
        $accountsCollection->addFieldToFilter(
            'invoice_generation',
            Ess_M2ePro_Model_Amazon_Account::INVOICE_GENERATION_BY_EXTENSION
        );
        $accountsCollection->setOrder('update_date', 'desc');

        $accountsByMerchantId = array();
        foreach ($accountsCollection->getItems() as $account) {
            /** @var $account Ess_M2ePro_Model_Account */

            $merchantId = $account->getChildObject()->getMerchantId();
            if (!isset($accountsByMerchantId[$merchantId])) {
                $accountsByMerchantId[$merchantId] = array();
            }

            $accountsByMerchantId[$merchantId][] = $account;
        }

        $accounts = array();
        foreach ($accountsByMerchantId as $accountItems) {
            $accounts[] = $accountItems[0];
        }

        return $accounts;
    }

    // ---------------------------------------

    protected function processAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher $dispatcherObject */
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getCustomConnector(
            'Cron_Task_Amazon_Order_Receive_InvoiceDataReport_Requester',
            array(),
            $account
        );
        $dispatcherObject->process($connectorObj);
    }

    //########################################
}
