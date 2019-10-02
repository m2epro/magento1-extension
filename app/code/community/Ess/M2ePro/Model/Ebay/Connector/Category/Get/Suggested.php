<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Category_Get_Suggested
    extends Ess_M2ePro_Model_Ebay_Connector_Command_RealTime
{
    // ########################################

    protected function getCommand()
    {
        return array('category', 'get', 'suggested');
    }

    public function getRequestData()
    {
        return array(
            'query' => $this->_params['query']
        );
    }

    protected function validateResponse()
    {
        return true;
    }

    protected function prepareResponseData()
    {
        if ($this->getResponse()->isResultError()) {
            return;
        }

        $this->_responseData = $this->getResponse()->getData();
    }

    // ########################################

    protected function buildConnectionInstance()
    {
        $connection = parent::buildConnectionInstance();
        $connection->setTimeout(30);

        return $connection;
    }

    // ########################################
}
