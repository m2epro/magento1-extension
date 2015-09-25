<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Processing_Dispatcher
{
    const MAX_MEMORY_LIMIT = 512;

    const MAX_REQUESTS_PER_ONE_TIME = 3;
    const MAX_PROCESSING_IDS_PER_REQUEST = 100;

    private $lockItem = NULL;
    private $operationHistory = NULL;

    private $parentLockItem = NULL;
    private $parentOperationHistory = NULL;

    private $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    // ########################################

    public function process()
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit(self::MAX_MEMORY_LIMIT);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();

        if ($this->getLockItem()->isExist()) {
            return false;
        }

        $lockItemParentId = $this->getParentLockItem() ? $this->getParentLockItem()->getRealId() : NULL;
        $this->getLockItem()->create($lockItemParentId);
        $this->getLockItem()->makeShutdownFunction();

        $this->getOperationHistory()->cleanOldData();

        $operationHistoryParentId = $this->getParentOperationHistory() ?
                $this->getParentOperationHistory()->getObject()->getId() : NULL;
        $this->getOperationHistory()->start('processing',
                                            $operationHistoryParentId,
                                            $this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();

        $result = true;

        try {

            $this->clearOldProcessingRequests();
            $this->processProcessingRequests();

        } catch (Exception $exception) {

            $result = false;

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getOperationHistory()->setContentData('exception', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ));
        }

        $this->getOperationHistory()->stop();
        $this->getLockItem()->remove();

        return $result;
    }

    // ########################################

    public function setInitiator($value)
    {
        $this->initiator = (int)$value;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    // -----------------------------------

    public function setParentLockItem(Ess_M2ePro_Model_LockItem $object)
    {
        $this->parentLockItem = $object;
    }

    /**
     * @return Ess_M2ePro_Model_LockItem
     */
    public function getParentLockItem()
    {
        return $this->parentLockItem;
    }

    // -----------------------------------

    public function setParentOperationHistory(Ess_M2ePro_Model_OperationHistory $object)
    {
        $this->parentOperationHistory = $object;
    }

    /**
     * @return Ess_M2ePro_Model_OperationHistory
     */
    public function getParentOperationHistory()
    {
        return $this->parentOperationHistory;
    }

    // ########################################

    protected function getLockItem()
    {
        if (is_null($this->lockItem)) {
            $this->lockItem = Mage::getModel('M2ePro/LockItem');
            $this->lockItem->setNick('processing');
        }
        return $this->lockItem;
    }

    protected function getOperationHistory()
    {
        if (is_null($this->operationHistory)) {
            $this->operationHistory = Mage::getModel('M2ePro/OperationHistory');
        }
        return $this->operationHistory;
    }

    // ########################################

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

    // ########################################

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

    //----------------------------------------

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

    // ########################################

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

    // ########################################
}