<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Command_RealTime extends Ess_M2ePro_Model_Connector_Command_Abstract
{
    // ########################################

    protected $responseData = NULL;

    // ########################################

    public function process()
    {
        $this->getConnection()->process();

        if (!$this->validateResponse()) {
            throw new Ess_M2ePro_Model_Exception('Validation Failed. The Server response data is not valid.');
        }

        $this->prepareResponseData();
    }

    // ########################################

    protected function validateResponse()
    {
        return true;
    }

    protected function prepareResponseData()
    {
        $this->responseData = $this->getResponse()->getData();
    }

    // ########################################

    public function getResponseData()
    {
        return $this->responseData;
    }

    public function getResponseMessages()
    {
        return $this->getResponse()->getMessages()->getEntitiesAsArrays();
    }

    // ########################################
}