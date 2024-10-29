<?php

/**
 * @method Ess_M2ePro_Model_Walmart_Connector_ProductType_GetInfo_Response getResponseData()
 */
class Ess_M2ePro_Model_Walmart_Connector_ProductType_GetInfo_Command
    extends Ess_M2ePro_Model_Walmart_Connector_Command_RealTime
{
    const PARAM_KEY_MARKETPLACE_ID = 'marketplace_id';
    const PARAM_KEY_PRODUCT_TYPE_NICK = 'product_type_nick';

    protected function getCommand()
    {
        return array('productType', 'get', 'info');
    }

    public function getRequestData()
    {
        return array(
            'marketplace' => $this->_params[self::PARAM_KEY_MARKETPLACE_ID],
            'product_type_nick' => $this->_params[self::PARAM_KEY_PRODUCT_TYPE_NICK],
        );
    }

    protected function prepareResponseData()
    {
        $response = $this->getResponse()->getData();

        $this->_responseData = new Ess_M2ePro_Model_Walmart_Connector_ProductType_GetInfo_Response(
            $response['title'],
            $response['nick'],
            $response['variation_attributes'],
            $response['attributes']
        );
    }

    protected function validateResponse()
    {
        $response = $this->getResponse()->getData();

        return isset($response['title'])
            && isset($response['nick'])
            && isset($response['variation_attributes'])
            && isset($response['attributes']);
    }
}
