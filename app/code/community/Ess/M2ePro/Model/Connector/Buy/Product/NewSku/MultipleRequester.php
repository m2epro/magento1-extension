<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Buy_Product_NewSku_MultipleRequester
    extends Ess_M2ePro_Model_Connector_Buy_Product_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('product','add','newSku');
    }

    //########################################

    protected function getActionType()
    {
        return 'new_sku';
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_NEW_SKU_PRODUCT_ON_COMPONENT;
    }

    //########################################
}