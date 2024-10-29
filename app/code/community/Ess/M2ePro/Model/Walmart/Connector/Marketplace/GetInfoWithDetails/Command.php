<?php

/**
 * @method Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetInfoWithDetails_Response getResponseData()
 */
class Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetInfoWithDetails_Command
    extends Ess_M2ePro_Model_Walmart_Connector_Command_RealTime
{
    const PARAM_KEY_MARKETPLACE_ID = 'marketplace_id';

    protected function getCommand()
    {
        return array('marketplace', 'get', 'info');
    }

    public function getRequestData()
    {
        return array(
            'include_details' => true,
            'marketplace' => $this->_params[self::PARAM_KEY_MARKETPLACE_ID],
        );
    }

    protected function prepareResponseData()
    {
        $productTypes = array();
        $productTypesNicks = array();

        $response = $this->getResponse()->getData();
        foreach ($response['info']['details']['product_types'] as $productType) {
            $productTypes[] = array(
                'nick' => $productType['nick'],
                'title' => $productType['title'],
            );
            $productTypesNicks[] = $productType['nick'];
        }

        $this->_responseData = new Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetInfoWithDetails_Response(
            $productTypes,
            $productTypesNicks,
            Mage::helper('M2ePro')->createGmtDateTime($response['info']['last_update'])
        );
    }

    protected function validateResponse()
    {
        $response = $this->getResponse()->getData();

        return isset($response['info']['details']['product_types'])
            && isset($response['info']['last_update']);
    }
}
