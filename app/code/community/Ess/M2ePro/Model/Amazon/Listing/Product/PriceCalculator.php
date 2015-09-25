<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Amazon_Listing getComponentListing()
 * @method Ess_M2ePro_Model_Amazon_Template_SellingFormat getComponentSellingFormatTemplate()
 * @method Ess_M2ePro_Model_Amazon_Listing_Product getComponentProduct()
 */
class Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator
    extends Ess_M2ePro_Model_Listing_Product_PriceCalculator
{
    /**
     * @var bool
     */
    private $isSalePrice = false;

    // ########################################

    /**
     * @param bool $value
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator
     */
    public function setIsSalePrice($value)
    {
        $this->isSalePrice = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    protected function getIsSalePrice()
    {
        return $this->isSalePrice;
    }

    // ########################################

    protected function applyAdditionalOptionValuesModifications(
        Ess_M2ePro_Model_Listing_Product_Variation $variation, $value)
    {
        if ($this->getIsSalePrice() && $value <= 0 &&
            $this->getSource('mode') == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL) {
            return 0;
        }

        return parent::applyAdditionalOptionValuesModifications($variation, $value);
    }

    // ########################################

    protected function getExistedProductSpecialValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        if ($this->getIsSalePrice() && !$product->isSpecialPriceActual()) {
            return 0;
        }

        return parent::getExistedProductSpecialValue($product);
    }

    protected function getBundleProductDynamicSpecialValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        if ($this->getIsSalePrice() && !$product->isSpecialPriceActual()) {
            return 0;
        }

        return parent::getBundleProductDynamicSpecialValue($product);
    }

    // ########################################
}