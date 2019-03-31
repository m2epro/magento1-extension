<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Item_Verify_Requester
    extends Ess_M2ePro_Model_Ebay_Connector_Item_Requester
{
    protected $isRealTime = true;

    //########################################

    protected function getCommand()
    {
        return array('item','add','single');
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_LIST;
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;
    }

    //########################################

    protected function isListingProductLocked()
    {
        return false;
    }

    protected function lockListingProduct() {}

    protected function unlockListingProduct() {}

    //----------------------------------------

    protected function getValidatorObject()
    {
        /** @var $obj Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_List_Validator */
        $obj = parent::getValidatorObject();
        $obj->setIsVerifyCall(true);

        return $obj;
    }

    protected function makeRequestObject()
    {
        /** @var $obj Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_List_Request */
        $obj = parent::makeRequestObject();
        $obj->setIsVerifyCall(true);

        return $obj;
    }

    public function getLogger()
    {
        $obj = parent::getLogger();
        $obj->setStoreMode(true);

        return $obj;
    }

    //########################################
}