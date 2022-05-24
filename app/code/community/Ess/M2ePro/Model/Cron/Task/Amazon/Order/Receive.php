<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/order/receive';

    //####################################

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

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //########################################

    protected function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        foreach ($permittedAccounts as $merchantId => $accounts) {
            /** @var $account Ess_M2ePro_Model_Account **/

            try {
                $this->processAccounts($merchantId, $accounts);
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Receive" Action for Amazon Account Merchant "%merchant%" was completed with error.',
                    $merchantId
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    //########################################

    protected function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
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

    protected function processAccounts($merchantId, array $accounts)
    {
        $accountsByServerHash = array();
        foreach ($accounts as $account) {
            $accountsByServerHash[$account->getChildObject()->getServerHash()] = $account;
        }

        $preparedResponseData = $this->receiveAmazonOrdersData($merchantId, $accountsByServerHash);
        if (empty($preparedResponseData)) {
            return null;
        }

        if (!empty($preparedResponseData['job_token'])) {
            Mage::helper('M2ePro/Module')->getRegistry()->setValue(
                "/amazon/orders/receive/{$merchantId}/job_token/",
                $preparedResponseData['job_token']
            );
        } else {
            Mage::helper('M2ePro/Module')->getRegistry()->deleteValue(
                "/amazon/orders/receive/{$merchantId}/job_token/"
            );
        }

        /** @var Ess_M2ePro_Model_Cron_Task_Amazon_Order_Creator $ordersCreator */
        $ordersCreator = Mage::getModel('M2ePro/Cron_Task_Amazon_Order_Creator');
        $ordersCreator->setSynchronizationLog($this->getSynchronizationLog());

        $processedAmazonOrders = array();
        foreach ($preparedResponseData['items'] as $accountAccessToken => $ordersData) {
            $amazonOrders = $ordersCreator->processAmazonOrders(
                $accountsByServerHash[$accountAccessToken],
                $ordersData
            );

            if (empty($amazonOrders)) {
                continue;
            }

            $processedAmazonOrders[] = $amazonOrders;
        }

        foreach ($processedAmazonOrders as $amazonOrders) {
            $ordersCreator->processMagentoOrders($amazonOrders);
        }

        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            "/amazon/orders/receive/{$merchantId}/from_update_date/",
            $preparedResponseData['to_update_date']
        );
    }

    protected function receiveAmazonOrdersData($merchantId, $accounts)
    {
        $updateSinceTime = Mage::helper('M2ePro/Module')->getRegistry()->getValue(
            "/amazon/orders/receive/{$merchantId}/from_update_date/"
        );

        $fromDate = $this->prepareFromDate($updateSinceTime);
        $toDate   = $this->prepareToDate();

        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');
        $fromTimestamp = (int)$helper->createGmtDateTime($fromDate)
            ->format('U');
        $toTimestamp = (int)$helper->createGmtDateTime($toDate)
            ->format('U');
        if ($fromTimestamp >= $toTimestamp) {
            $fromDate = $helper->createGmtDateTime($toDate);
            $fromDate->modify('- 5 minutes');

            $fromDate = $fromDate->format('Y-m-d H:i:s');
        }

        $params = array(
            'accounts'         => $accounts,
            'from_update_date' => $fromDate,
            'to_update_date'   => $toDate
        );

        $jobToken = Mage::helper('M2ePro/Module')->getRegistry()->getValue(
            "/amazon/orders/receive/{$merchantId}/job_token/"
        );

        if (!empty($jobToken)) {
            $params['job_token'] = $jobToken;
        }

        /** @var Ess_M2ePro_Model_Connector_Command_RealTime $connectorObj */
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getCustomConnector(
            'Amazon_Connector_Orders_Get_Items', $params
        );

        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        $this->processResponseMessages($connectorObj->getResponseMessages());

        if (!isset($responseData['items']) || !isset($responseData['to_update_date'])) {
            $logData = array(
                'from_update_date'  => $fromDate,
                'to_update_date'    => $toDate,
                'jobToken'          => $jobToken,
                'account_id'        => $merchantId,
                'response_data'     => $responseData,
                'response_messages' => $connectorObj->getResponseMessages()
            );
            Mage::helper('M2ePro/Module_Logger')->process($logData, 'Amazon orders receive task - empty response');

            return array();
        }

        return $responseData;
    }

    protected function processResponseMessages(array $messages = array())
    {
        /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messagesSet */
        $messagesSet = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
        $messagesSet->init($messages);

        foreach ($messagesSet->getEntities() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                           : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType
            );
        }
    }

    //########################################

    protected function prepareFromDate($lastFromDate)
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
        $minDate = new DateTime('now', new DateTimeZone('UTC'));
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

    protected function prepareToDate()
    {
        $operationHistory = $this->getOperationHistory()->getParentObject('cron_runner');
        if ($operationHistory !== null) {
            $toDate = $operationHistory->getData('start_date');
        } else {
            $toDate = new DateTime('now', new DateTimeZone('UTC'));
            $toDate = $toDate->format('Y-m-d H:i:s');
        }

        return $toDate;
    }

    //########################################
}
