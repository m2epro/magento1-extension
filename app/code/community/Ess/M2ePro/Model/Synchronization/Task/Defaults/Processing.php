<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults_Processing
    extends Ess_M2ePro_Model_Synchronization_Task_Defaults_Abstract
{
    const MAX_REQUESTS_PER_ONE_TIME      = 3;
    const MAX_PROCESSING_IDS_PER_REQUEST = 100;

    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/processing/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Processing';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 30;
    }

    //########################################

    protected function performActions()
    {
        $this->clearOldProcessingRequests();
        $this->processProcessingRequests();

        return true;
    }

    //########################################

    private function clearOldProcessingRequests()
    {
        $currentDateTime = Mage::helper('M2ePro')->getCurrentGmtDate();

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Processing_Request')->getCollection();
        $collection->getSelect()->where("expiration_date < '{$currentDateTime}'");

        $this->executeFailedProcessingRequests($collection->getItems());
    }

    private function processProcessingRequests()
    {
        $collection = Mage::getModel('M2ePro/Processing_Request')->getCollection();
        $collection->addFieldToSelect('component');
        $collection->getSelect()->distinct();
        $collection->load();

        foreach ($collection->getItems() as $component) {

            $component = $component->getData('component');

            /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
            $collection = Mage::getModel('M2ePro/Processing_Request')->getCollection();
            $collection->addFieldToFilter('component',$component);
            $processingRequests = $collection->getItems();

            $processingSingleObjects = array();
            $processingPartialObjects = array();

            foreach ($processingRequests as $processingRequest) {
                /** @var $processingRequest Ess_M2ePro_Model_Processing_Request */
                if ($processingRequest->isPerformTypeSingle()) {
                    $processingSingleObjects[] = $processingRequest;
                } else {
                    $processingPartialObjects[] = $processingRequest;
                }
            }

            $processingIds = array();
            $processingObjects = array();

            foreach ($processingSingleObjects as $processingRequest) {
                /** @var $processingRequest Ess_M2ePro_Model_Processing_Request */
                $processingIds[] = $processingRequest->getProcessingHash();
                if (!isset($processingObjects[$processingRequest->getProcessingHash()])) {
                    $processingObjects[$processingRequest->getProcessingHash()] = array();
                }
                $processingObjects[$processingRequest->getProcessingHash()][] = $processingRequest;
            }

            $this->processSingleProcessingRequests($component,array_unique($processingIds),$processingObjects);

            $processingIds = array();
            $processingObjects = array();

            foreach ($processingPartialObjects as $processingRequest) {
                /** @var $processingRequest Ess_M2ePro_Model_Processing_Request */
                $processingIds[] = $processingRequest->getProcessingHash();
                if (!isset($processingObjects[$processingRequest->getProcessingHash()])) {
                    $processingObjects[$processingRequest->getProcessingHash()] = array();
                }
                $processingObjects[$processingRequest->getProcessingHash()][] = $processingRequest;
            }

            $this->processPartialProcessingRequests($component,array_unique($processingIds),$processingObjects);
        }
    }

    //########################################

    private function processSingleProcessingRequests($component, array $processingIds, array $processingObjects)
    {
        if (count($processingIds) <= 0) {
            return;
        }

        $processingIdsParts = array_chunk($processingIds,self::MAX_PROCESSING_IDS_PER_REQUEST);

        foreach ($processingIdsParts as $processingIds) {

            if (count($processingIds) <= 0) {
                continue;
            }

            // send parts to the server
            $dispatcherObject = Mage::getModel('M2ePro/Connector_'.ucfirst($component).'_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('processing','get','results',
                array('processing_ids'=>$processingIds),
                'results', NULL, NULL);

            $results = $dispatcherObject->process($connectorObj);

            if (empty($results)) {
                continue;
            }

            // process results
            foreach ($processingIds as $processingId) {

                if (!isset($results[$processingId]) || !isset($results[$processingId]['status']) ||
                    $results[$processingId]['status'] == Ess_M2ePro_Model_Processing_Request::STATUS_NOT_FOUND) {
                    $this->executeFailedProcessingRequests($processingObjects[$processingId]);
                    continue;
                }

                if ($results[$processingId]['status'] != Ess_M2ePro_Model_Processing_Request::STATUS_COMPLETE) {
                    continue;
                }

                !isset($results[$processingId]['data']) && $results[$processingId]['data'] = array();
                !isset($results[$processingId]['messages']) && $results[$processingId]['messages'] = array();

                $this->executeCompletedProcessingRequests($processingObjects[$processingId],
                    (array)$results[$processingId]['data'],
                    (array)$results[$processingId]['messages']);
            }
        }

        $this->getLockItem()->activate();
    }

    // ---------------------------------------

    private function processPartialProcessingRequests($component, array $processingIds, array $processingObjects)
    {
        if (count($processingIds) <= 0) {
            return;
        }

        foreach ($processingIds as $processingId) {

            $nextPart = NULL;

            foreach ($processingObjects[$processingId] as $key => $processingRequest) {

                /** @var $processingRequest Ess_M2ePro_Model_Processing_Request */

                if (is_null($processingRequest->getNextPart())) {
                    unset($processingObjects[$processingId][$key]);
                    continue;
                }

                $tempNextPart = (int)$processingRequest->getNextPart();

                if (is_null($nextPart) || $tempNextPart < $nextPart) {
                    $nextPart = $tempNextPart;
                }
            }

            if (empty($processingObjects[$processingId])) {
                continue;
            }

            $this->processPartialProcessingRequestsNextPart(
                $component, $processingId, array_values($processingObjects[$processingId]), $nextPart, 1
            );
        }
    }

    private function processPartialProcessingRequestsNextPart($component, $processingId, array $processingRequests,
                                                              $necessaryPart, $countCycles = 1)
    {
        $params = array(
            'processing_id' => $processingId,
            'necessary_parts' => array(
                $processingId => (int)$necessaryPart
            )
        );

        // send parts to the server
        $dispatcherObject = Mage::getModel('M2ePro/Connector_'.ucfirst($component).'_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('processing','get','results',
            $params, 'results', NULL, NULL);

        $results = $dispatcherObject->process($connectorObj);

        if (empty($results)) {
            return;
        }

        if (!isset($results[$processingId]) || !isset($results[$processingId]['status']) ||
            $results[$processingId]['status'] == Ess_M2ePro_Model_Processing_Request::STATUS_NOT_FOUND) {
            $this->executeFailedProcessingRequests($processingRequests);
            return;
        }

        if ($results[$processingId]['status'] != Ess_M2ePro_Model_Processing_Request::STATUS_COMPLETE) {
            return;
        }

        !isset($results[$processingId]['data']) && $results[$processingId]['data'] = array();
        !isset($results[$processingId]['messages']) && $results[$processingId]['messages'] = array();

        $nextPart = NULL;
        if (isset($results[$processingId]['next_part']) && (int)$results[$processingId]['next_part'] >= 2) {
            $nextPart = (int)$results[$processingId]['next_part'];
        }

        $nextProcessingRequests = array();
        foreach ($processingRequests as $processingRequest) {

            /** @var $processingRequest Ess_M2ePro_Model_Processing_Request */

            $responserRunner = $processingRequest->getResponserRunner();

            if ((int)$processingRequest->getNextPart() == $necessaryPart) {

                $results[$processingId]['data']['next_part'] = $nextPart;

                $processResult = $responserRunner->process(
                    (array)$results[$processingId]['data'], (array)$results[$processingId]['messages']
                );

                if (!$processResult) {
                    continue;
                }

                $processingRequest->setData('next_part', $nextPart)->save();
            }

            $this->getLockItem()->activate();

            if (is_null($nextPart)) {
                $responserRunner->complete();
            } else {
                $nextProcessingRequests[] = $processingRequest;
            }
        }

        if (is_null($nextPart) || empty($nextProcessingRequests)) {
            return;
        }

        if ($countCycles >= self::MAX_REQUESTS_PER_ONE_TIME) {
            return;
        }

        unset($results, $dispatcherObject, $processingRequests);

        $this->processPartialProcessingRequestsNextPart(
            $component, $processingId,
            $nextProcessingRequests,
            $nextPart, $countCycles + 1
        );
    }

    //########################################

    private function executeCompletedProcessingRequests($processingRequests, array $data, array $messages = array())
    {
        if (!is_array($processingRequests)) {
            $processingRequests = array($processingRequests);
        }

        foreach ($processingRequests as $processingRequest) {

            if (!($processingRequest instanceof Ess_M2ePro_Model_Processing_Request)) {
                continue;
            }

            $responserRunner = $processingRequest->getResponserRunner();

            $responserRunner->process($data, $messages) && $responserRunner->complete();
        }
    }

    private function executeFailedProcessingRequests($processingRequests)
    {
        if (!is_array($processingRequests)) {
            $processingRequests = array($processingRequests);
        }

        $message = 'Request wait timeout exceeded.';

        foreach ($processingRequests as $processingRequest) {

            if (!($processingRequest instanceof Ess_M2ePro_Model_Processing_Request)) {
                continue;
            }

            $processingRequest->getResponserRunner()->complete($message);
        }
    }

    //########################################
}