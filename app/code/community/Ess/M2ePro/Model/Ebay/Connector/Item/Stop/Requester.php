<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Item_Stop_Requester
    extends Ess_M2ePro_Model_Ebay_Connector_Item_Requester
{
    //########################################

    protected function getCommand()
    {
        return array('item','update','end');
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_STOP;
    }

    protected function getLogsAction()
    {
        if (!empty($this->params['remove'])) {
            return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT;
        }

        return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;
    }

    //########################################

    public function getRequestTimeout()
    {
        return self::DEFAULT_REQUEST_TIMEOUT;
    }

    //########################################

    public function initializeVariations() {}

    //########################################
}