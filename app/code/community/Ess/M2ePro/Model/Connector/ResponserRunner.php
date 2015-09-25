<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_ResponserRunner
{
    /** @var Ess_M2ePro_Model_Processing_Request $processingRequest */
    private $processingRequest = null;

    /** @var Ess_M2ePro_Model_Connector_Responser $responserObject */
    private $responserObject = null;

    private $responserModelName = null;

    private $responserParams = array();

    // ##################################

    public function setResponserModelName($modelName)
    {
        $this->responserModelName = $modelName;
        return $this;
    }

    public function setResponserParams(array $responserParams)
    {
        $this->responserParams = $responserParams;
        return $this;
    }

    public function setProcessingRequest(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        $this->responserModelName = $processingRequest->getResponserModel();
        $this->responserParams    = $processingRequest->getDecodedResponserParams();

        $this->processingRequest  = $processingRequest;

        return $this;
    }

    // ##################################

    public function start(Ess_M2ePro_Model_Connector_Requester $requester)
    {
        $requester->eventBeforeExecuting();

        if (!is_null($this->processingRequest)) {
            $requester->eventBeforeProcessing();
            $requester->setProcessingLocks($this->processingRequest);
        }
    }

    public function process(array $response = array(), array $messages = array())
    {
        try {
            $this->getResponserObject()->process($response, $messages);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            $this->complete($exception->getMessage());
            return false;
        }

        return true;
    }

    public function getParsedResponseData()
    {
        return $this->getResponserObject()->getParsedResponseData();
    }

    public function complete($errorMessage = null)
    {
        try {
            if (!is_null($this->processingRequest)) {
                $this->makeShutdownFunction();

                $this->getResponserObject()->unsetProcessingLocks($this->processingRequest);
                $this->getResponserObject()->eventAfterProcessing();
            }

            if (!is_null($errorMessage)) {
                $this->getResponserObject()->eventFailedExecuting($errorMessage);
            }

            $this->getResponserObject()->eventAfterExecuting();

            if ($this->processingRequest) {
                $this->processingRequest->deleteInstance();
            }
        } catch (Exception $exception) {
            $this->forceRemoveLockedObjectsAndProcessingRequest();
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    // ##################################

    /**
     * @return Ess_M2ePro_Model_Connector_Responser
     */
    private function getResponserObject()
    {
        if (!is_null($this->responserObject)) {
            return $this->responserObject;
        }

        $modelClassName = Mage::getConfig()->getModelClassName($this->responserModelName);
        return $this->responserObject = new $modelClassName($this->responserParams);
    }

    // ##################################

    private function forceRemoveLockedObjectsAndProcessingRequest()
    {
        if (is_null($this->processingRequest)) {
            return;
        }

        $table = Mage::getResourceModel('M2ePro/LockedObject')->getMainTable();
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($table,array('`related_hash` = ?'=>(string)$this->processingRequest->getHash()));

        $table = Mage::getResourceModel('M2ePro/Processing_Request')->getMainTable();
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($table,array('`id` = ?'=>(int)$this->processingRequest->getId()));
    }

    private function makeShutdownFunction()
    {
        if (is_null($this->processingRequest)) {
            return;
        }

        $hash = (string)$this->processingRequest->getHash();
        $processingRequestId = (int)$this->processingRequest->getId();

        $table = Mage::getResourceModel('M2ePro/LockedObject')->getMainTable();

        $functionCode = "Mage::getSingleton('core/resource')->getConnection('core_write')
                            ->delete('".$table."',array('`related_hash` = ?'=>'".$hash."'));
                         Mage::getSingleton('core/resource')->getConnection('core_write')
                            ->delete('".$table."',array('`id` = ?'=>".$processingRequestId."));";

        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);
    }

    // ##################################
}