<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
    /**
     * @var bool
     */
    private $isIncreaseByVatPercent = false;

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

    /**
     * @param bool $value
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_PriceCalculator
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

    public function getVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($variation->getChildObject()->isDelete()) {
            return 0;
        }

        return parent::getVariationValue($variation);
    }

    //########################################

    protected function prepareFinalValue($value)
    {
        if ($this->getIsIncreaseByVatPercent() &&
            $this->getComponentSellingFormatTemplate()->isPriceIncreaseVatPercentEnabled()) {

            $value = $this->increaseValueByVatPercent($value);
        }

        return parent::prepareFinalValue($value);
    }

    protected function increaseValueByVatPercent($value)
    {
        $vatPercent = $this->getComponentSellingFormatTemplate()->getVatPercent();
        return $value + (($vatPercent*$value) / 100);
    }

    //########################################

    protected function prepareOptionTitles($optionTitles)
    {
        foreach ($optionTitles as &$optionTitle) {
            $optionTitle = Mage::helper('M2ePro')->reduceWordsInString(
                $optionTitle, Ess_M2ePro_Helper_Component_Ebay::MAX_LENGTH_FOR_OPTION_VALUE
            );
        }

        return $optionTitles;
    }

    //########################################
}