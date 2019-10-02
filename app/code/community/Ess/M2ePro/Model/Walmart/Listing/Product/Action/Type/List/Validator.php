<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Validator
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Validator
{
    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        if (!$this->validateMagentoProductType()) {
            return false;
        }

        $sku = $this->getSku();
        if (empty($sku)) {
            // M2ePro_TRANSLATIONS
            // SKU is not provided. Please, check Listing Settings.
            $this->addMessage('SKU is not provided. Please, check Listing Settings.');
            return false;
        }

        if (strlen($sku) > Ess_M2ePro_Helper_Component_Walmart::SKU_MAX_LENGTH) {
            // M2ePro_TRANSLATIONS
            // The length of SKU must be less than 50 characters.
            $this->addMessage('The length of SKU must be less than 50 characters.');
            return false;
        }

        if (!$this->validateCategory()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProductFlags()) {
            return false;
        }

        if (!$this->getListingProduct()->isNotListed() || !$this->getListingProduct()->isListable()) {
            // M2ePro_TRANSLATIONS
            // Item is already on Walmart, or not available.
            $this->addMessage('Item is already on Walmart, or not available.');

            return false;
        }

        if ($this->getVariationManager()->isLogicalUnit()) {
            return true;
        }

        if (!$this->validateProductIds()) {
            return false;
        }

        if (!$this->validateStartEndDates()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        if ($this->getVariationManager()->isPhysicalUnit() && !$this->validatePhysicalUnitMatching()) {
            return false;
        }

        return true;
    }

    //########################################

    protected function getSku()
    {
        if (isset($this->_data['sku'])) {
            return $this->_data['sku'];
        }

        $params = $this->getParams();
        if (!isset($params['sku'])) {
            return NULL;
        }

        return $params['sku'];
    }

    //########################################

    protected function getGtin()
    {
        $gtin = parent::getGtin();
        if ($gtin !== null) {
            return $gtin;
        }

        $helper = Mage::helper('M2ePro/Component_Walmart_Configuration');

        if ($helper->isGtinModeNotSet()) {
            return NULL;
        }

        return $this->getWalmartListingProduct()->getActualMagentoProduct()->getAttributeValue(
            $helper->getGtinCustomAttribute()
        );
    }

    protected function getUpc()
    {
        $upc = parent::getUpc();
        if ($upc !== null) {
            return $upc;
        }

        $helper = Mage::helper('M2ePro/Component_Walmart_Configuration');

        if ($helper->isUpcModeNotSet()) {
            return NULL;
        }

        return $this->getWalmartListingProduct()->getActualMagentoProduct()->getAttributeValue(
            $helper->getUpcCustomAttribute()
        );
    }

    protected function getEan()
    {
        $ean = parent::getEan();
        if ($ean !== null) {
            return $ean;
        }

        $helper = Mage::helper('M2ePro/Component_Walmart_Configuration');

        if ($helper->isEanModeNotSet()) {
            return NULL;
        }

        return $this->getWalmartListingProduct()->getActualMagentoProduct()->getAttributeValue(
            $helper->getEanCustomAttribute()
        );
    }

    protected function getIsbn()
    {
        $isbn = parent::getIsbn();
        if ($isbn !== null) {
            return $isbn;
        }

        $helper = Mage::helper('M2ePro/Component_Walmart_Configuration');

        if ($helper->isIsbnModeNotSet()) {
            return NULL;
        }

        return $this->getWalmartListingProduct()->getActualMagentoProduct()->getAttributeValue(
            $helper->getIsbnCustomAttribute()
        );
    }

    //########################################
}
