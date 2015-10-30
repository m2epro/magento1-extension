<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Buy_Product_Relist_MultipleRequester
    extends Ess_M2ePro_Model_Connector_Buy_Product_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('product','update','entities');
    }

    //########################################

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT;
    }

    //########################################
}