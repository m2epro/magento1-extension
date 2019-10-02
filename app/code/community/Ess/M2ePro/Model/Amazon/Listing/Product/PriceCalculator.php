<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
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
    protected $_isSalePrice = false;

    //########################################

    protected function isPriceVariationModeParent()
    {
        return $this->getPriceVariationMode()
                            == Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_VARIATION_MODE_PARENT;
    }

    protected function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode()
                            == Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_VARIATION_MODE_CHILDREN;
    }

    //########################################

    /**
     * @param bool $value
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator
     */
    public function setIsSalePrice($value)
    {
        $this->_isSalePrice = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    protected function getIsSalePrice()
    {
        return $this->_isSalePrice;
    }

    //########################################

    protected function applyAdditionalOptionValuesModifications(
        Ess_M2ePro_Model_Listing_Product_Variation $variation,
        $value
    ) {
        if ($this->getIsSalePrice() && $value <= 0 && $this->isSourceModeSpecial()) {
            return 0;
        }

        return parent::applyAdditionalOptionValuesModifications($variation, $value);
    }

    //########################################

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

    //########################################

    protected function getCurrencyForPriceConvert()
    {
        return $this->getComponentListing()->getAmazonMarketplace()->getDefaultCurrency();
    }

    //########################################
}
