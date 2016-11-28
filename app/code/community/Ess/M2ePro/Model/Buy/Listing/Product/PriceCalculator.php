<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Buy_Listing getComponentListing()
 * @method Ess_M2ePro_Model_Buy_Template_SellingFormat getComponentSellingFormatTemplate()
 * @method Ess_M2ePro_Model_Buy_Listing_Product getComponentProduct()
 */
class Ess_M2ePro_Model_Buy_Listing_Product_PriceCalculator
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
                            == Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_VARIATION_MODE_PARENT;
    }

    protected function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode()
                            == Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_VARIATION_MODE_CHILDREN;
    }

    //########################################

    /**
     * @param bool $value
     * @return Ess_M2ePro_Model_Buy_Listing_Product_PriceCalculator
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