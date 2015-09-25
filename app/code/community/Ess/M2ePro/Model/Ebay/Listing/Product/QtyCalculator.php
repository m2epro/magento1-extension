<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Ebay_Listing getComponentListing()
 * @method Ess_M2ePro_Model_Ebay_Template_SellingFormat getComponentSellingFormatTemplate()
 * @method Ess_M2ePro_Model_Ebay_Listing_Product getComponentProduct()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product_QtyCalculator
    extends Ess_M2ePro_Model_Listing_Product_QtyCalculator
{
    // ########################################

    public function getVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($variation->getChildObject()->isDelete()) {
            return 0;
        }

        return parent::getVariationValue($variation);
    }

    // ########################################

    protected function getOptionBaseValue(Ess_M2ePro_Model_Listing_Product_Variation_Option $option)
    {
        if (!$option->getMagentoProduct()->isStatusEnabled() ||
            !$option->getMagentoProduct()->isStockAvailability()) {
            return 0;
        }

        if ($this->getSource('mode') == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {

            if (!$this->getMagentoProduct()->isStatusEnabled() ||
                !$this->getMagentoProduct()->isStockAvailability()) {
                return 0;
            }
        }

        return parent::getOptionBaseValue($option);
    }

    // ########################################
}