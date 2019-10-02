<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Response getResponseObject()
 */

class Ess_M2ePro_Model_Walmart_Connector_Product_List_Responser
    extends Ess_M2ePro_Model_Walmart_Connector_Product_Responser
{
    // ########################################

    protected function processSuccess(array $params = array())
    {
        $this->getResponseObject()->processSuccess($params);
        $this->_isSuccess = true;
    }

    protected function getSuccessfulMessage()
    {
        return NULL;
    }

    // ########################################
}
