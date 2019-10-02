<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Command_Pending_Processing_Runner extends Ess_M2ePro_Model_Processing_Runner
{
    const PENDING_REQUEST_MAX_LIFE_TIME = 43200;

    protected $_responserModelName = null;

    protected $_responserParams = array();

    /** @var Ess_M2ePro_Model_Connector_Command_Pending_Responser $_responser */
    protected $_responser = null;

    /** @var Ess_M2ePro_Model_Connector_Connection_Response $_response */
    protected $_response = null;

    // ##################################

    public function setProcessingObject(Ess_M2ePro_Model_Processing $processingObjectObject)
    {
        $result = parent::setProcessingObject($processingObjectObject);

        $this->setResponserModelName($this->_params['responser_model_name']);
        $this->setResponserParams($this->_params['responser_params']);

        return $result;
    }

    // ----------------------------------

    public function getParams()
    {
        $params = parent::getParams();

        $params['responser_model_name'] = $this->getResponserModelName();
        $params['responser_params']     = $this->getResponserParams();

        return $params;
    }

    // ----------------------------------

    public function setResponserModelName($modelName)
    {
        $this->_responserModelName = $modelName;
        return $this;
    }

    public function getResponserModelName()
    {
        return $this->_responserModelName;
    }

    // ----------------------------------

    public function setResponserParams(array $params)
    {
        $this->_responserParams = $params;
        return $this;
    }

    public function getResponserParams()
    {
        return $this->_responserParams;
    }

    // ##################################

    protected function eventAfter()
    {
        parent::eventAfter();

        try {
            $this->getResponser()->eventAfterExecuting();
        } catch (Exception $exception) {
            $this->getResponser()->failDetected($exception->getMessage());
        }
    }

    // ##################################

    protected function getResponser($returnNewObject = false)
    {
        if ($this->_responser !== null && !$returnNewObject) {
            return $this->_responser;
        }

        $modelClassName = Mage::getConfig()->getModelClassName($this->getResponserModelName());

        if (!class_exists($modelClassName)) {
            throw new Ess_M2ePro_Model_Exception('Responser class does not exist.');
        }

        return $this->_responser = new $modelClassName($this->getResponserParams(), $this->getResponse());
    }

    protected function getResponse()
    {
        if ($this->_response !== null) {
            return $this->_response;
        }

        $this->_response = Mage::getModel('M2ePro/Connector_Connection_Response');
        $this->_response->initFromPreparedResponse(
            $this->getProcessingObject()->getResultData(), $this->getProcessingObject()->getResultMessages()
        );

        $params = $this->getParams();
        if (!empty($params['request_time'])) {
            $this->_response->setRequestTime($params['request_time']);
        }

        return $this->_response;
    }

    // ##################################
}