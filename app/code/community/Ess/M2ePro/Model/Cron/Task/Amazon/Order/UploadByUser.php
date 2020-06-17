<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_UploadByUser extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'amazon/order/upload_by_user';

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

        /** @var Ess_M2ePro_Model_Cron_Task_Amazon_Order_Creator $ordersCreator */
        $ordersCreator = Mage::getModel('M2ePro/Cron_Task_Amazon_Order_Creator');
        $ordersCreator->setSynchronizationLog($this->getSynchronizationLog());
        $ordersCreator->setValidateAccountCreateDate(false);

        foreach ($permittedAccounts as $merchantId => $accounts) {
            /** @var $account Ess_M2ePro_Model_Account **/

            /** @var Ess_M2ePro_Model_Cron_Task_Amazon_Order_UploadByUser_Manager $manager */
            $manager = Mage::getModel('M2ePro/Cron_Task_Amazon_Order_UploadByUser_Manager');
            $manager->setIdentifier($merchantId);
            if (!$manager->isEnabled()) {
                continue;
            }

            try {
                $accountsByServerHash = array();
                foreach ($accounts as $account) {
                    $accountsByServerHash[$account->getChildObject()->getServerHash()] = $account;
                }

                $amazonData = $this->receiveAmazonOrdersData($manager, $merchantId, $accountsByServerHash);
                if (empty($amazonData)) {
                    continue;
                }

                !empty($amazonData['job_token'])
                    ? $manager->setJobToken($amazonData['job_token'])
                    : $manager->setJobToken(null);

                $processedAmazonOrders = array();
                foreach ($amazonData['items'] as $accountAccessToken => $ordersData) {
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

                $manager->setCurrentFromDate($amazonData['to_create_date']);

                if (empty($amazonData['job_token']) &&
                    $manager->getCurrentFromDate()->getTimestamp() >= $manager->getToDate()->getTimestamp()
                ) {
                    $manager->clear();
                }
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Upload Orders By User" Action for Amazon Account "%merchant%" was completed with error.',
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

    //########################################

    protected function receiveAmazonOrdersData(
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_UploadByUser_Manager $manager,
        $merchantId,
        $accounts
    ) {
        $toTime   = $manager->getToDate();
        $fromTime = $manager->getCurrentFromDate();
        $fromTime === null && $fromTime = $manager->getFromDate();

        $params = array(
            'accounts'         => $accounts,
            'from_create_date' => $fromTime->format('Y-m-d H:i:s'),
            'to_create_date'   => $toTime->format('Y-m-d H:i:s')
        );

        $jobToken = $manager->getJobToken();
        if (!empty($jobToken)) {
            $params['job_token'] = $jobToken;
        }

        /** @var Ess_M2ePro_Model_Connector_Command_RealTime $connectorObj */
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getCustomConnector(
            'Amazon_Connector_Orders_Get_Items', $params
        );

        $dispatcherObject->process($connectorObj);

        $this->processResponseMessages($connectorObj->getResponseMessages());

        return $connectorObj->getResponseData();
    }

    protected function processResponseMessages(array $messages)
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
                $message->getText(),
                $logType
            );
        }
    }

    //########################################
}
