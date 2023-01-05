<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Order_UploadByUser
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'walmart/order/upload_by_user';

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();

        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
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
        $accountsCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Account');

        /** @var Ess_M2ePro_Model_Cron_Task_Walmart_Order_Creator $ordersCreator */
        $ordersCreator = Mage::getModel('M2ePro/Cron_Task_Walmart_Order_Creator');
        $ordersCreator->setSynchronizationLog($this->getSynchronizationLog());
        $ordersCreator->setValidateAccountCreateDate(false);

        foreach ($accountsCollection->getItems() as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            /** @var Ess_M2ePro_Model_Cron_Task_Walmart_Order_UploadByUser_Manager $manager */
            $manager = Mage::getModel('M2ePro/Cron_Task_Walmart_Order_UploadByUser_Manager');
            $manager->setIdentifierByAccount($account);
            if (!$manager->isEnabled()) {
                continue;
            }

            try {
                $responseData = $this->receiveWalmartOrdersData($manager, $account);
                if (empty($responseData)) {
                    continue;
                }

                $processedWalmartOrders = $ordersCreator->processWalmartOrders($account, $responseData['items']);
                $ordersCreator->processMagentoOrders($processedWalmartOrders);

                $manager->setCurrentFromDate($responseData['to_create_date']);

                if ($manager->getCurrentFromDate()->getTimestamp() >= $manager->getToDate()->getTimestamp() ||
                    empty($responseData['items'])
                ) {
                    $manager->clear();
                }
            } catch (Exception $exception) {
                $message = Mage::helper('M2ePro')->__(
                    'The "Upload Orders By User" Action for Walmart Account "%title%" was completed with error.',
                    $account->getTitle()
                );

                $this->processTaskAccountException($message, __FILE__, __LINE__);
                $this->processTaskException($exception);
            }
        }
    }

    //########################################

    protected function receiveWalmartOrdersData(
        Ess_M2ePro_Model_Cron_Task_Walmart_Order_UploadByUser_Manager $manager,
        Ess_M2ePro_Model_Account $account
    ) {
        $toTime   = $manager->getToDate();
        $fromTime = $manager->getCurrentFromDate();
        $fromTime === null && $fromTime = $manager->getFromDate();

        /** @var Ess_M2ePro_Model_Walmart_Connector_Dispatcher $dispatcherObject */
        $dispatcherObject = Mage::getModel('M2ePro/Walmart_Connector_Dispatcher');
        $orders = array(array());
        $breakDate = null;

        do {
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'orders', 'get', 'items',
                array(
                    'account'          => $account->getData('server_hash'),
                    'from_create_date' => $fromTime->format('Y-m-d H:i:s'),
                    'to_create_date'   => $toTime->format('Y-m-d H:i:s')
                )
            );
            $dispatcherObject->process($connectorObj);

            $this->processResponseMessages($connectorObj->getResponseMessages());

            $responseData = $connectorObj->getResponseData();

            $fromTime = new DateTime($responseData['to_create_date'], new DateTimeZone('UTC'));
            if ($breakDate !== null && $breakDate->getTimestamp() === $fromTime->getTimestamp()) {
                break;
            }

            $orders[] = $responseData['items'];
            $breakDate = $fromTime;

            if ($fromTime > $toTime) {
                break;
            }
        } while (!empty($responseData['items']));

        return array(
            'items'          => call_user_func_array('array_merge', $orders),
            'to_create_date' => $responseData['to_create_date']
        );
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
                $message->getText(),
                $logType
            );
        }
    }

    //########################################
}
