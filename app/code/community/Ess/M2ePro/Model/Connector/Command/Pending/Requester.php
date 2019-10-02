<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Command_Pending_Requester
    extends Ess_M2ePro_Model_Connector_Command_Abstract
{
    /** @var Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single_Runner $_processingRunner */
    protected $_processingRunner = null;

    protected $_processingServerHash = null;

    /** @var Ess_M2ePro_Model_Connector_Command_Pending_Responser $_responser */
    protected $_responser;

    protected $_preparedResponseData;

    // ########################################

    protected function getProcessingRunner()
    {
        if ($this->_processingRunner !== null) {
            return $this->_processingRunner;
        }

        $this->_processingRunner = Mage::getModel('M2ePro/' . $this->getProcessingRunnerModelName());

        $this->_processingRunner->setParams($this->getProcessingParams());

        $this->_processingRunner->setResponserModelName($this->getResponserModelName());
        $this->_processingRunner->setResponserParams($this->getResponserParams());

        return $this->_processingRunner;
    }

    protected function getResponser()
    {
        if ($this->_responser !== null) {
            return $this->_responser;
        }

        $modelClassName = Mage::getConfig()->getModelClassName($this->getResponserModelName());
        return $this->_responser = new $modelClassName($this->getResponserParams(), $this->getResponse());
    }

    // ########################################

    public function process()
    {
        try {
            $this->getConnection()->process();
        } catch (Exception $exception) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromException($exception);

            if ($this->getConnection()->getResponse() === null) {
                $response = Mage::getModel('M2ePro/Connector_Connection_Response');
                $response->initFromPreparedResponse(array(), array());

                $this->getConnection()->setResponse($response);
            }

            $this->getConnection()->getResponse()->getMessages()->addEntity($message);
        }

        $this->eventBeforeExecuting();

        $responseData = $this->getResponse()->getData();
        if (isset($responseData['processing_id'])) {
            $this->_processingServerHash = $responseData['processing_id'];
            $this->getProcessingRunner()->start();

            return;
        }

        $this->processResponser();

        $this->_preparedResponseData = $this->getResponser()->getPreparedResponseData();
    }

    // -----------------------------------------

    protected function processResponser()
    {
        try {
            $this->getResponser()->process();
            $this->getResponser()->eventAfterExecuting();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            $this->getResponser()->failDetected($exception->getMessage());
        }
    }

    // ########################################

    public function getPreparedResponseData()
    {
        return $this->_preparedResponseData;
    }

    // ########################################

    public function eventBeforeExecuting()
    {
        return null;
    }

    // ########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Connector_Command_Pending_Processing_Single_Runner';
    }

    protected function getProcessingParams()
    {
        return array(
            'component'   => $this->getProtocol()->getComponent(),
            'server_hash' => $this->_processingServerHash,
        );
    }

    // -----------------------------------------

    protected function getResponserModelName()
    {
        $responserClassName = preg_replace('/Requester$/', '', get_class($this)).'Responser';
        $responserModelName = preg_replace('/^Ess_M2ePro_Model_/', 'M2ePro/', $responserClassName);

        return $responserModelName;
    }

    protected function getResponserParams()
    {
        return $this->_params;
    }

    // ########################################
}
