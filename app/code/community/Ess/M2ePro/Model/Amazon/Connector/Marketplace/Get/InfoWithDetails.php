<?php

class Ess_M2ePro_Model_Amazon_Connector_Marketplace_Get_InfoWithDetails
    extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    protected function getCommand()
    {
        return array('marketplace', 'get', 'info');
    }

    public function getRequestData()
    {
        return array(
            'include_details' => true,
            'marketplace' => $this->_params['marketplace_id'],
        );
    }

    protected function validateResponse()
    {
        $response = $this->getResponse()->getData();;

        return isset($response['info']['details']['product_type']);
    }
}