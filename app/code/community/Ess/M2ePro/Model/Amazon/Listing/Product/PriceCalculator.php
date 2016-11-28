<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
    private $isSalePrice = false;

    /**
     * @var bool
     */
    private $isIncreaseByVatPercent = false;

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

    //########################################

    /**
     * @param bool $value
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator
     */
    public function setIsIncreaseByVatPercent($value)
    {
        $this->isIncreaseByVatPercent = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    protected function getIsIncreaseByVatPercent()
    {
        return $this->isIncreaseByVatPercent;
    }

    //########################################

    protected function applyAdditionalOptionValuesModifications(
        Ess_M2ePro_Model_Listing_Product_Variation $variation, $value)
    {
        if ($this->getIsSalePrice() && $value <= 0 &&
            $this->getSource('mode') == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL) {
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

    protected function prepareFinalValue($value)
    {
        if ($this->getIsIncreaseByVatPercent() &&
            $this->getComponentSellingFormatTemplate()->getPriceVatPercent() > 0) {

            $value = $this->increaseValueByVatPercent($value);
        }

        return parent::prepareFinalValue($value);
    }

    protected function increaseValueByVatPercent($value)
    {
        $vatPercent = $this->getComponentSellingFormatTemplate()->getPriceVatPercent();
        return $value + (($vatPercent*$value) / 100);
    }

    //########################################
}