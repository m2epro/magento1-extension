<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Command_Pending_Requester
    extends Ess_M2ePro_Model_Connector_Command_Abstract
{
    /** @var Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single_Runner $processingRunner */
    protected $processingRunner = NULL;

    protected $processingServerHash = NULL;

    /** @var Ess_M2ePro_Model_Connector_Command_Pending_Responser $responser */
    protected $responser = NULL;

    protected $preparedResponseData = NULL;

    // ########################################

    protected function getProcessingRunner()
    {
        if (!is_null($this->processingRunner)) {
            return $this->processingRunner;
        }

        $this->processingRunner = Mage::getModel('M2ePro/'.$this->getProcessingRunnerModelName());

        $this->processingRunner->setParams($this->getProcessingParams());

        $this->processingRunner->setResponserModelName($this->getResponserModelName());
        $this->processingRunner->setResponserParams($this->getResponserParams());

        return $this->processingRunner;
    }

    protected function getResponser()
    {
        if (!is_null($this->responser)) {
            return $this->responser;
        }

        $modelClassName = Mage::getConfig()->getModelClassName($this->getResponserModelName());
        return $this->responser = new $modelClassName($this->getResponserParams(), $this->getResponse());
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
            $this->processingServerHash = $responseData['processing_id'];
            $this->getProcessingRunner()->start();

            return;
        }

        $this->processResponser();

        $this->preparedResponseData = $this->getResponser()->getPreparedResponseData();
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
        return $this->preparedResponseData;
    }

    // ########################################

    public function eventBeforeExecuting() {}

    // ########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Connector_Command_Pending_Processing_Single_Runner';
    }

    protected function getProcessingParams()
    {
        return array(
            'component'   => $this->getProtocol()->getComponent(),
            'server_hash' => $this->processingServerHash,
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
        return $this->params;
    }

    // ########################################
}