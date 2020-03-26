<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Ebay_Listing getComponentListing()
 * @method Ess_M2ePro_Model_Ebay_Template_SellingFormat getComponentSellingFormatTemplate()
 * @method Ess_M2ePro_Model_Ebay_Listing_Product getComponentProduct()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product_PriceCalculator
    extends Ess_M2ePro_Model_Listing_Product_PriceCalculator
{
    //########################################

    protected function isPriceVariationModeParent()
    {
        return $this->getPriceVariationMode()
                            == Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_VARIATION_MODE_PARENT;
    }

    protected function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode()
                            == Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_VARIATION_MODE_CHILDREN;
    }

    //########################################

    public function getVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($variation->getChildObject()->isDelete()) {
            return $variation->getChildObject()->getOnlinePrice();
        }

        return parent::getVariationValue($variation);
    }

    //########################################

    protected function prepareOptionTitles($optionTitles)
    {
        foreach ($optionTitles as &$optionTitle) {
            $optionTitle = trim(
                Mage::helper('M2ePro')->reduceWordsInString(
                    $optionTitle, Ess_M2ePro_Helper_Component_Ebay::VARIATION_OPTION_LABEL_MAX_LENGTH
                )
            );
        }

        return $optionTitles;
    }

    protected function prepareAttributeTitles($attributeTitles)
    {
        foreach ($attributeTitles as &$attributeTitle) {
            $attributeTitle = trim($attributeTitle);
        }

        return $attributeTitles;
    }

    //########################################

    protected function getCurrencyForPriceConvert()
    {
        return $this->getComponentListing()->getEbayMarketplace()->getCurrency();
    }

    //########################################
}
