<?php

class Ess_M2ePro_Model_Walmart_Connector_Account_GetGrantAccessUrl_Command
    extends Ess_M2ePro_Model_Walmart_Connector_Command_RealTime
{
    const PARAM_KEY_BACK_URL = 'back_url';

    protected function getCommand()
    {
        return array('account', 'get', 'grantAccessUrl');
    }

    public function getRequestData()
    {
        return array(
            'back_url' => $this->_params[self::PARAM_KEY_BACK_URL]
        );
    }

    public function prepareResponseData()
    {
        $response = $this->getResponse()->getData();
        $this->_responseData = new Ess_M2ePro_Model_Walmart_Connector_Account_GetGrantAccessUrl_Response($response['url']);

        return $this->_responseData;
    }

}