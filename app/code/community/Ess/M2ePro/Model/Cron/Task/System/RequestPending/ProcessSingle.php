<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_System_RequestPending_ProcessSingle extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'system/request_pending/process_single';

    const STATUS_NOT_FOUND  = 'not_found';
    const STATUS_COMPLETE   = 'completed';
    const STATUS_PROCESSING = 'processing';

    const MAX_HASHES_PER_REQUEST = 100;

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
        $requestPendingSingleCollection = Mage::getResourceModel('M2ePro/Request_Pending_Single_Collection');
        $requestPendingSingleCollection->setOnlyOutdatedItemsFilter();
        $requestPendingSingleCollection->addFieldToFilter('is_completed', 1);

        /** @var Ess_M2ePro_Model_Request_Pending_Single[] $requestsPendingSingleObjects */
        $requestsPendingSingleObjects = $requestPendingSingleCollection->getItems();

        foreach ($requestsPendingSingleObjects as $requestsPendingSingleObject) {
            $requestsPendingSingleObject->deleteInstance();
        }
    }

    private function completeExpired()
    {
        $requestPendingSingleCollection = Mage::getResourceModel('M2ePro/Request_Pending_Single_Collection');
        $requestPendingSingleCollection->setOnlyExpiredItemsFilter();
        $requestPendingSingleCollection->addFieldToFilter('is_completed', 0);

        /** @var Ess_M2ePro_Model_Request_Pending_Single[] $expiredRequestPendingSingleObjects */
        $expiredRequestPendingSingleObjects = $requestPendingSingleCollection->getItems();

        foreach ($expiredRequestPendingSingleObjects as $requestPendingSingle) {
            $this->completeRequest($requestPendingSingle, array(), array($this->getFailedMessage()->asArray()));
        }
    }

    private function executeInProgress()
    {
        $componentsInProgress = Mage::getResourceModel('M2ePro/Request_Pending_Single')->getComponentsInProgress();

        foreach ($componentsInProgress as $component) {
            $requestPendingSingleCollection = Mage::getResourceModel('M2ePro/Request_Pending_Single_Collection');
            $requestPendingSingleCollection->addFieldToFilter('component', $component);
            $requestPendingSingleCollection->addFieldToFilter('is_completed', 0);

            $serverHashes = $requestPendingSingleCollection->getColumnValues('server_hash');
            $serverHashesPacks = array_chunk($serverHashes, self::MAX_HASHES_PER_REQUEST);

            foreach ($serverHashesPacks as $serverHashesPack) {

                $results = $this->getResultsFromServer($component, $serverHashesPack);

                foreach ($serverHashesPack as $serverHash) {
                    /** @var Ess_M2ePro_Model_Request_Pending_Single $requestPendingSingle */
                    $requestPendingSingle = $requestPendingSingleCollection->getItemByColumnValue(
                        'server_hash', $serverHash
                    );

                    if (!isset($results[$serverHash]['status']) ||
                        $results[$serverHash]['status'] == self::STATUS_NOT_FOUND
                    ) {
                        $this->completeRequest(
                            $requestPendingSingle, array(), array($this->getFailedMessage()->asArray())
                        );
                        continue;
                    }

                    if ($results[$serverHash]['status'] != self::STATUS_COMPLETE) {
                        continue;
                    }

                    $data = array();
                    if (isset($results[$serverHash]['data'])) {
                        $data = $results[$serverHash]['data'];
                    }

                    $messages = array();
                    if (isset($results[$serverHash]['messages'])) {
                        $messages = $results[$serverHash]['messages'];
                    }

                    $this->completeRequest($requestPendingSingle, $data, $messages);
                }
            }
        }
    }

    //####################################

    private function getResultsFromServer($component, array $serverHashes)
    {
        $dispatcher = Mage::getModel('M2ePro/'.ucfirst($component).'_Connector_Dispatcher');
        $connector = $dispatcher->getVirtualConnector(
            'processing','get','results',
            array('processing_ids' => $serverHashes),
            'results', NULL, NULL
        );

        $dispatcher->process($connector);

        return $connector->getResponseData();
    }

    private function getFailedMessage()
    {
        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            'Request wait timeout exceeded.',
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
        );

        return $message;
    }

    private function completeRequest(Ess_M2ePro_Model_Request_Pending_Single $requestPendingSingle,
                                     array $data, array $messages)
    {
        $requestPendingSingle->setSettings('result_data', $data);
        $requestPendingSingle->setSettings('result_messages', $messages);

        $requestPendingSingle->setData('is_completed', 1);

        $requestPendingSingle->save();
    }

    //####################################
}