<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Account_Check_EntityRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    public function getRequestData()
    {
        return array(
            'account' => $this->_params['account_server_hash'],
        );
    }

    protected function getCommand()
    {
        return array('account', 'check', 'entity');
    }

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();

        return isset($responseData['status']) && array_key_exists('explanation', $responseData);
    }

    protected function prepareResponseData()
    {
        $response = $this->getResponse()->getData();

        $this->_responseData = array(
            'status'      => $response['status'],
            'explanation' => $response['explanation'] === null ? '' : $response['explanation']
        );
    }
}
