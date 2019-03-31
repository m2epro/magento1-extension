<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Command_Pending_Processing_Runner extends Ess_M2ePro_Model_Processing_Runner
{
    const PENDING_REQUEST_MAX_LIFE_TIME = 43200;

    private $responserModelName = NULL;

    private $responserParams = array();

    /** @var Ess_M2ePro_Model_Connector_Command_Pending_Responser $responser */
    protected $responser = NULL;

    /** @var Ess_M2ePro_Model_Connector_Connection_Response $response */
    protected $response = NULL;

    // ##################################

    public function setProcessingObject(Ess_M2ePro_Model_Processing $processingObjectObject)
    {
        $result = parent::setProcessingObject($processingObjectObject);

        $this->setResponserModelName($this->params['responser_model_name']);
        $this->setResponserParams($this->params['responser_params']);

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
        $this->responserModelName = $modelName;
        return $this;
    }

    public function getResponserModelName()
    {
        return $this->responserModelName;
    }

    // ----------------------------------

    public function setResponserParams(array $params)
    {
        $this->responserParams = $params;
        return $this;
    }

    public function getResponserParams()
    {
        return $this->responserParams;
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
        if (!is_null($this->responser) && !$returnNewObject) {
            return $this->responser;
        }

        $modelClassName = Mage::getConfig()->getModelClassName($this->getResponserModelName());

        if (!class_exists($modelClassName)) {
            throw new Ess_M2ePro_Model_Exception('Responser class does not exist.');
        }

        return $this->responser = new $modelClassName($this->getResponserParams(), $this->getResponse());
    }

    protected function getResponse()
    {
        if (!is_null($this->response)) {
            return $this->response;
        }

        $this->response = Mage::getModel('M2ePro/Connector_Connection_Response');
        $this->response->initFromPreparedResponse(
            $this->getProcessingObject()->getResultData(), $this->getProcessingObject()->getResultMessages()
        );

        $params = $this->getParams();
        if (!empty($params['request_time'])) {
            $this->response->setRequestTime($params['request_time']);
        }

        return $this->response;
    }

    // ##################################
}