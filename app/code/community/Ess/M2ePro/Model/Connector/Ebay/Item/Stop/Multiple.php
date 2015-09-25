<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_Stop_Multiple
    extends Ess_M2ePro_Model_Connector_Ebay_Item_MultipleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','ends');
    }

    protected function getLogsAction()
    {
        if (isset($this->params['remove']) && (bool)$this->params['remove']) {
            return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT;
        }
        return Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_STOP;
    }

    // ########################################

    protected function filterManualListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isStoppable()) {

                $this->removeAndUnlockListingProduct($listingProduct);

                if (!isset($this->params['remove']) || !(bool)$this->params['remove']) {

                    $message = array(
                        // M2ePro_TRANSLATIONS
                        // Item is not Listed or not available
                        parent::MESSAGE_TEXT_KEY => 'Item is not Listed or not available',
                        parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                    );

                    $this->getLogger()->logListingProductMessage(
                        $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );
                } else {
                    $listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
                    $listingProduct->deleteInstance();
                }

                continue;
            }
        }
    }

    protected function getRequestData()
    {
        $data = array(
            'items' => array()
        );

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $tempData = $this->getRequestObject($listingProduct)->getData();
            $this->logRequestMessages($listingProduct);

            $data['items'][$listingProduct->getId()] = $this->buildRequestDataObject(
                $listingProduct,$tempData
            )->getData();
        }

        return $data;
    }

    //----------------------------------------

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR || !isset($response['result'])) {
            $this->checkAndRemoveNeededItems();
            return $response;
        }

        foreach ($response['result'] as $listingProductId => $listingsProductResult) {

            if (!$this->isResultSuccess($listingsProductResult)) {
                continue;
            }

            $listingProduct = $this->getListingProduct($listingProductId);

            if ($listingsProductResult['already_stop']) {

                $message = array(
                    // M2ePro_TRANSLATIONS
                    // Item was already Stopped on eBay
                    parent::MESSAGE_TEXT_KEY => 'Item was already Stopped on eBay',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

            } else {

                $message = array(
                    // M2ePro_TRANSLATIONS
                    // Item was successfully Stopped
                    parent::MESSAGE_TEXT_KEY => 'Item was successfully Stopped',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                );
            }

            $this->getResponseObject($listingProduct)->processSuccess($response);
            $this->getLogger()->logListingProductMessage(
                $listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
        }

        $this->checkAndRemoveNeededItems();
        return $response;
    }

    // ########################################

    protected function checkAndRemoveNeededItems()
    {
        if (!isset($this->params['remove']) || !(bool)$this->params['remove']) {
            return;
        }

        foreach ($this->listingsProducts as $listingProduct) {
            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
            $listingProduct->deleteInstance();
        }
    }

    // ########################################
}