<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_Revise_Single
    extends Ess_M2ePro_Model_Connector_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','revise');
    }

    protected function getLogsAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
    }

    // ########################################

    protected function filterManualListingProduct()
    {
        if (!$this->listingProduct->isRevisable()) {

            $message = array(
                // M2ePro_TRANSLATIONS
                // Item is not Listed or not available
                parent::MESSAGE_TEXT_KEY => 'Item is not Listed or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage(
                $this->listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        if(!$this->listingProduct->getChildObject()->isSetCategoryTemplate()) {

            $message = array(
                // M2ePro_TRANSLATIONS
                // Categories Settings are not set
                parent::MESSAGE_TEXT_KEY => 'Categories Settings are not set',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage(
                $this->listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

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

        $params = array(
            'is_images_upload_error' => $this->isImagesUploadFailed($this->messages)
        );

        if ($response['already_stop']) {

            $this->getResponseObject()->processAlreadyStopped($response, $params);

            $message = array(
                // M2ePro_TRANSLATIONS
                // Item was already Stopped on eBay
                parent::MESSAGE_TEXT_KEY => 'Item was already Stopped on eBay',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

        } else {

            $this->getResponseObject()->processSuccess($response, $params);

            $message = array(
                // M2ePro_TRANSLATIONS
                // Item was successfully Revised
                parent::MESSAGE_TEXT_KEY => $this->getResponseObject()->getSuccessfulMessage(),
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );
        }

        $this->getLogger()->logListingProductMessage(
            $this->listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );

        return $response;
    }

    // ########################################
}