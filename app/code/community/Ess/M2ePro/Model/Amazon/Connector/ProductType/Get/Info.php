<?php

class Ess_M2ePro_Model_Amazon_Connector_ProductType_Get_Info
    extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    protected function getCommand()
    {
        return array('productType', 'get', 'info');
    }

    public function getRequestData()
    {
        return array(
            'product_type_nick' => $this->_params['product_type_nick'],
            'marketplace' => $this->_params['marketplace_id'],
        );
    }

    protected function validateResponse()
    {
        $response = $this->getResponse()->getData();

        return isset(
            $response['nick'],
            $response['title'],
            $response['attributes'],
            $response['attributes_groups'],
            $response['variation_themes'],
            $response['last_update']
        );
    }
}