<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Selling
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Request_Abstract
{
    // ########################################

    public function getData()
    {
        $data = array();

        if ($this->getConfigurator()->isQtyAllowed()) {
            if (!isset($this->validatorsData['qty'])) {
                $this->validatorsData['qty'] = $this->getBuyListingProduct()->getQty();
            }

            $data['qty'] = $this->validatorsData['qty'];

            $this->checkQtyWarnings();
        }

        if ($this->getConfigurator()->isPriceAllowed()) {
            if (!isset($this->validatorsData['price'])) {
                $this->validatorsData['price'] = $this->getBuyListingProduct()->getPrice();
            }

            $data['price'] = $this->validatorsData['price'];
        }

        return $data;
    }

    // ########################################

    public function checkQtyWarnings()
    {
        $qtyMode = $this->getBuyListing()->getBuySellingFormatTemplate()->getQtyMode();
        if ($qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {

            $listingProductId = $this->getListingProduct()->getId();
            $productId = $this->getBuyListingProduct()->getActualMagentoProduct()->getProductId();
            $storeId = $this->getListing()->getStoreId();

            if(!empty(Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'])) {

                $qtys = Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'];
                foreach ($qtys as $type => $override) {
                    $this->addQtyWarnings($type);
                }
            }
        }
    }

    public function addQtyWarnings($type)
    {
        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
            // M2ePro_TRANSLATIONS
            // During the Quantity Calculation the Settings in the "Manage Stock No" field were taken into consideration.
            $this->addWarningMessage('During the Quantity Calculation the Settings in the "Manage Stock No" '.
                'field were takken into consideration.');
        }

        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_BACKORDERS) {
            // M2ePro_TRANSLATIONS
            // During the Quantity Calculation the Settings in the "Backorders" field were taken into consideration.
            $this->addWarningMessage('During the Quantity Calculation the Settings in the "Backorders" '.
                'field were taken into consideration.');
        }
    }

    // ########################################
}