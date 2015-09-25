<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Buy_Listing getComponentListing()
 * @method Ess_M2ePro_Model_Buy_Template_SellingFormat getComponentSellingFormatTemplate()
 * @method Ess_M2ePro_Model_Buy_Listing_Product getComponentProduct()
 */
class Ess_M2ePro_Model_Buy_Listing_Product_QtyCalculator
    extends Ess_M2ePro_Model_Listing_Product_QtyCalculator
{
    // ########################################

    /**
     * @var bool
     */
    private $isMagentoMode = false;

    // ########################################

    /**
     * @param bool $value
     * @return Ess_M2ePro_Model_Buy_Listing_Product_QtyCalculator
     */
    public function setIsMagentoMode($value)
    {
        $this->isMagentoMode = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    protected function getIsMagentoMode()
    {
        return $this->isMagentoMode;
    }

    // ########################################

    public function getProductValue()
    {
        if ($this->getIsMagentoMode()) {
            return (int)$this->getMagentoProduct()->getQty(true);
        }

        return parent::getProductValue();
    }

    protected function getOptionBaseValue(Ess_M2ePro_Model_Listing_Product_Variation_Option $option)
    {
        if ($this->getIsMagentoMode() ||
            $this->getSource('mode') == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {

            if (!$this->getMagentoProduct()->isStatusEnabled() ||
                !$this->getMagentoProduct()->isStockAvailability()) {
                return 0;
            }
        }

        if ($this->getIsMagentoMode()) {
            return (int)$option->getMagentoProduct()->getQty(true);
        }

        return parent::getOptionBaseValue($option);
    }

    // ########################################

    protected function applySellingFormatTemplateModifications($value)
    {
        if ($this->getIsMagentoMode()) {
            return $value;
        }

        return parent::applySellingFormatTemplateModifications($value);
    }

    // ########################################
}