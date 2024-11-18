<?php

class Ess_M2ePro_Model_Amazon_Connector_Template_Get_EntityRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    public function getRequestData()
    {
        return array();
    }

    protected function getCommand()
    {
        return array('account', 'get', 'shippingTemplatesInfo');
    }

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        if (
            !isset($responseData['templates'])
            || !is_array($responseData['templates'])
        ) {
            return false;
        }

        return true;
    }

    protected function prepareResponseData()
    {
        $preparedData = array();

        $response = $this->getResponse()->getData();

        foreach ($response['templates'] as $template) {
            $preparedData['templates'][] = array(
                'account_id' => $this->_account->getId(),
                'template_id' => $template['id'],
                'title' => $template['name'],
            );
        }

        $this->_responseData = $preparedData;
    }
}