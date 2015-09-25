<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/** @method Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Stop_Response getResponseObject */

class Ess_M2ePro_Model_Connector_Ebay_OtherItem_Stop_Single
    extends Ess_M2ePro_Model_Connector_Ebay_OtherItem_Abstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','end');
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Other_Log::ACTION_STOP_PRODUCT;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_STOP;
    }

    // ########################################

    protected function filterManualListingOther()
    {
        if (!$this->otherListing->isStoppable()) {

            $message = array(
                // M2ePro_TRANSLATIONS
                // Item is not Listed or not available
                parent::MESSAGE_TEXT_KEY => 'Item is not Listed or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingOtherMessage($this->otherListing,$message,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        $data = $this->getRequestObject()->getData();
        $this->logRequestMessages();

        return $this->buildRequestDataObject($data)->getData();
    }

    //----------------------------------------

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR) {
            return $response;
        }

        if ($response['already_stop']) {

            $this->getResponseObject()->processAlreadyStopped($response);

            $message = array(
                // M2ePro_TRANSLATIONS
                // Item was already Stopped on eBay
                parent::MESSAGE_TEXT_KEY => 'Item was already Stopped on eBay',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

        } else {

            $this->getResponseObject()->processSuccess($response);

            $message = array(
                // M2ePro_TRANSLATIONS
                // Item was successfully Stopped
                parent::MESSAGE_TEXT_KEY => 'Item was successfully Stopped',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );
        }

        $this->getLogger()->logListingOtherMessage($this->otherListing, $message,
                                                   Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        return $response;
    }

    // ########################################
}