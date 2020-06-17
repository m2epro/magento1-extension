<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Order_UploadByUser extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/order/upload_by_user';

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
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
        /** @var $accountsCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');

        /** @var Ess_M2ePro_Model_Cron_Task_Ebay_Order_Creator $ordersCreator */
        $ordersCreator = Mage::getModel('M2ePro/Cron_Task_Ebay_Order_Creator');
        $ordersCreator->setSynchronizationLog($this->getSynchronizationLog());
        $ordersCreator->setValidateAccountCreateDate(false);

        foreach ($accountsCollection->getItems() as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            /** @var Ess_M2ePro_Model_Cron_Task_Ebay_Order_UploadByUser_Manager $manager */
            $manager = Mage::getModel('M2ePro/Cron_Task_Ebay_Order_UploadByUser_Manager');
            $manager->setIdentifierByAccount($account);
            if (!$manager->isEnabled()) {
                continue;
            }

            try {
                $ebayData = $this->receiveEbayOrdersData($manager, $account);
                if (empty($ebayData)) {
                    continue;
                }

                !empty($ebayData['job_token'])
                    ? $manager->setJobToken($ebayData['job_token'])
                    : $manager->setJobToken(null);

                $processedEbayOrders = $ordersCreator->processEbayOrders($account, $ebayData['items']);
                $ordersCreator->processMagentoOrders($processedEbayOrders);

                $manager->setCurrentFromDate($ebayData['to_create_date']);

                if (empty($amazonData['job_token']) &&
                    $manager->getCurrentFromDate()->getTimestamp() >= $manager->getToDate()->getTimestamp()
                ) {
                    $manager->clear();
                }
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Upload Orders By User" Action for eBay Account "%account%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    //########################################

    protected function receiveEbayOrdersData(
        Ess_M2ePro_Model_Cron_Task_Ebay_Order_UploadByUser_Manager $manager,
        Ess_M2ePro_Model_Account $account
    ) {
        $toTime   = $manager->getToDate();
        $fromTime = $manager->getCurrentFromDate();
        $fromTime === null && $fromTime = $manager->getFromDate();

        $params = array(
            'from_create_date' => Mage::helper('M2ePro/Component_Ebay')->timeToString($fromTime),
            'to_create_date'   => Mage::helper('M2ePro/Component_Ebay')->timeToString($toTime)
        );

        $jobToken = $manager->getJobToken();
        if (!empty($jobToken)) {
            $params['job_token'] = $jobToken;
        }

        /** @var Ess_M2ePro_Model_Connector_Command_RealTime $connectorObj */
        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getCustomConnector(
            'Ebay_Connector_Order_Receive_Items', $params, null, $account
        );

        $dispatcherObj->process($connectorObj);

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
