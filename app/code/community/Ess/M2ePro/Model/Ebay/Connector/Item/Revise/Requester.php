<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Item_Revise_Requester
    extends Ess_M2ePro_Model_Ebay_Connector_Item_Requester
{
    //########################################

    protected function getCommand()
    {
        return array('item','update','reviseManager');
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT;
    }

    //########################################
}