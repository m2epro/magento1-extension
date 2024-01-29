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
class Ess_M2ePro_Model_Ebay_Listing_Product_QtyCalculator
    extends Ess_M2ePro_Model_Listing_Product_QtyCalculator
{
    /**
     * @var bool
     */
    protected $_isMagentoMode = false;

    //########################################

    /**
     * @param $value
     * @return $this
     */
    public function setIsMagentoMode($value)
    {
        $this->_isMagentoMode = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    protected function getIsMagentoMode()
    {
        return $this->_isMagentoMode;
    }

    //########################################

    public function getProductValue()
    {
        if ($this->getIsMagentoMode()) {
            return (int)$this->getMagentoProduct()->getQty(true);
        }

        return parent::getProductValue();
    }

    //########################################

    public function getVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($variation->getChildObject()->isDelete()) {
            return 0;
        }

        $qty = parent::getVariationValue($variation);
        $ebaySynchronizationTemplate = $variation->getListingProduct()
            ->getChildObject()
            ->getEbaySynchronizationTemplate();

        if ($ebaySynchronizationTemplate->isStopWhenQtyCalculatedHasValue()) {
            $minQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyCalculatedHasValue();

            if ($qty <= $minQty || $this->isVariationHasStopAdvancedRules($variation)) {
                return 0;
            }
        }

        return $qty;
    }

    //########################################

    protected function getOptionBaseValue(Ess_M2ePro_Model_Listing_Product_Variation_Option $option)
    {
        if (!$option->getMagentoProduct()->isStatusEnabled() ||
            !$option->getMagentoProduct()->isStockAvailability()) {
            return 0;
        }

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

    //########################################

    protected function applySellingFormatTemplateModifications($value)
    {
        if ($this->getIsMagentoMode()) {
            return $value;
        }

        return parent::applySellingFormatTemplateModifications($value);
    }

    private function isVariationHasStopAdvancedRules(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        $ebaySynchronizationTemplate = $variation->getListingProduct()
            ->getChildObject()
            ->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isStopAdvancedRulesEnabled()) {
            return false;
        }

        /** @var \Ess_M2ePro_Model_Magento_Product_Rule $ruleModel */
        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
            array(
                'store_id' => $variation->getListingProduct()->getListing()->getStoreId(),
                'prefix'   => Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_ADVANCED_RULES_PREFIX
            )
        );
        $ruleModel->loadFromSerialized($ebaySynchronizationTemplate->getStopAdvancedRulesFilters());

        $conditions = $ruleModel->getConditions()->getConditions();

        if (empty($conditions)) {
            return false;
        }

        $productIdVariation = $variation->getChildObject()->getVariationProductId();

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($productIdVariation);

        if ($ruleModel->validate($magentoProduct->getProduct())) {
            return true;
        }

        return false;
    }
}
