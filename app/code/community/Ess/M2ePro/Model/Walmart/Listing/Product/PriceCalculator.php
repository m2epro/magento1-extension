<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Walmart_Listing getComponentListing()
 * @method Ess_M2ePro_Model_Walmart_Template_SellingFormat getComponentSellingFormatTemplate()
 * @method Ess_M2ePro_Model_Walmart_Listing_Product getComponentProduct()
 */
class Ess_M2ePro_Model_Walmart_Listing_Product_PriceCalculator
    extends Ess_M2ePro_Model_Listing_Product_PriceCalculator
{
    //########################################

    protected function isPriceVariationModeParent()
    {
        return $this->getPriceVariationMode()
                            == Ess_M2ePro_Model_Walmart_Template_SellingFormat::PRICE_VARIATION_MODE_PARENT;
    }

    protected function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode()
                            == Ess_M2ePro_Model_Walmart_Template_SellingFormat::PRICE_VARIATION_MODE_CHILDREN;
    }

    //########################################

    protected function getCurrencyForPriceConvert()
    {
        return $this->getComponentListing()->getWalmartMarketplace()->getDefaultCurrency();
    }

    //########################################
}
