<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Account_Update_EntityRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    //########################################

    public function getRequestData()
    {
        return $this->_params;
    }

    protected function getCommand()
    {
        return array('account','update','entity');
    }

    protected function validateResponse()
    {
        if (!parent::validateResponse()) {
            return false;
        }

        $responseData = $this->getResponse()->getData();
        if (!isset($responseData['info']) && !$this->getResponse()->getMessages()->hasErrorEntities()) {
            throw new Exception('Validation Failed. The Server response data is not valid.');
        }

        return true;
    }

    protected function prepareResponseData()
    {
        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError()) {
                continue;
            }

            throw new Exception($message->getText());
        }

        $this->_responseData = $this->getResponse()->getData();
    }

    //########################################
}
