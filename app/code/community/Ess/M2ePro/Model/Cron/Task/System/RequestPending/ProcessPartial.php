<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_System_RequestPending_ProcessPartial extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'system/request_pending/process_partial';

    const STATUS_NOT_FOUND  = 'not_found';
    const STATUS_COMPLETE   = 'completed';
    const STATUS_PROCESSING = 'processing';

    const MAX_PARTS_PER_ONE_ITERATION = 3;

    //####################################

    public function isPossibleToRun()
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        return parent::isPossibleToRun();
    }

    //####################################

    protected function performActions()
    {
        $this->removeOutdated();
        $this->completeExpired();
        $this->executeInProgress();
    }

    //####################################

    private function removeOutdated()
    {
        $requestPendingPartialCollection = Mage::getResourceModel('M2ePro/Request_Pending_Partial_Collection');
        $requestPendingPartialCollection->setOnlyOutdatedItemsFilter();
        $requestPendingPartialCollection->addFieldToFilter('is_completed', 1);

        /** @var Ess_M2ePro_Model_Request_Pending_Partial[] $requestsPendingPartialObjects */
        $requestsPendingPartialObjects = $requestPendingPartialCollection->getItems();

        foreach ($requestsPendingPartialObjects as $requestsPendingPartialObject) {
            $requestsPendingPartialObject->deleteInstance();
        }
    }

    private function completeExpired()
    {
        $requestPendingPartialCollection = Mage::getResourceModel('M2ePro/Request_Pending_Partial_Collection');
        $requestPendingPartialCollection->setOnlyExpiredItemsFilter();
        $requestPendingPartialCollection->addFieldToFilter('is_completed', 0);

        /** @var Ess_M2ePro_Model_Request_Pending_Partial[] $expiredRequestPendingPartialObjects */
        $expiredRequestPendingPartialObjects = $requestPendingPartialCollection->getItems();

        foreach ($expiredRequestPendingPartialObjects as $requestPendingPartialObject) {
            $this->completeRequest($requestPendingPartialObject, array($this->getFailedMessage()->asArray()));
        }
    }

    private function executeInProgress()
    {
        $requestPendingPartialCollection = Mage::getResourceModel('M2ePro/Request_Pending_Partial_Collection');
        $requestPendingPartialCollection->addFieldToFilter('is_completed', 0);

        /** @var Ess_M2ePro_Model_Request_Pending_Partial[] $requestPendingPartialObjects */
        $requestPendingPartialObjects = $requestPendingPartialCollection->getItems();

        foreach ($requestPendingPartialObjects as $requestPendingPartial) {
            $this->processRequest($requestPendingPartial);
        }
    }

    //####################################

    private function processRequest(Ess_M2ePro_Model_Request_Pending_Partial $requestPendingPartial)
    {
        for ($requestCount = 1; $requestCount <= self::MAX_PARTS_PER_ONE_ITERATION; $requestCount++) {
            $serverData = $this->getServerData($requestPendingPartial);

            if ($serverData['status'] == self::STATUS_NOT_FOUND) {
                $this->completeRequest($requestPendingPartial, array($this->getFailedMessage()->asArray()));
                break;
            }

            if ($serverData['status'] != self::STATUS_COMPLETE) {
                break;
            }

            $requestPendingPartial->addResultData($serverData['data']);

            if (!empty($serverData['next_part'])) {
                continue;
            }

            $this->completeRequest($requestPendingPartial, $serverData['messages']);
            break;
        }
    }

    private function getServerData(Ess_M2ePro_Model_Request_Pending_Partial $requestPendingPartial)
    {
        $dispatcher = Mage::getModel('M2ePro/'.ucfirst($requestPendingPartial->getComponent()).'_Connector_Dispatcher');
        $connector = $dispatcher->getVirtualConnector(
            'processing','get','results',
            array(
                'processing_id' => $requestPendingPartial->getServerHash(),
                'necessary_parts' => array(
                    $requestPendingPartial->getServerHash() => $requestPendingPartial->getNextPart(),
                ),
            ),
            'results', NULL, NULL
        );

        $dispatcher->process($connector);
        $result = $connector->getResponseData();

        return $result[$requestPendingPartial->getServerHash()];
    }

    //####################################

    private function getFailedMessage()
    {
        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            'Request wait timeout exceeded.',
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
        );

        return $message;
    }

    private function completeRequest(Ess_M2ePro_Model_Request_Pending_Partial $requestPendingPartial,
                                     array $messages = array())
    {
        $requestPendingPartial->setSettings('result_messages', $messages);
        $requestPendingPartial->setData('next_part', null);
        $requestPendingPartial->setData('is_completed', 1);

        $requestPendingPartial->save();
    }

    //####################################
}