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
            $this->addMessage('SKU is not provided. Please, check Listing Settings.');
            return false;
        }

        if (strlen($sku) > Ess_M2ePro_Helper_Component_Walmart::SKU_MAX_LENGTH) {
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
            return null;
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
            return null;
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
            return null;
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
            return null;
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
            return null;
        }

        return $this->getWalmartListingProduct()->getActualMagentoProduct()->getAttributeValue(
            $helper->getIsbnCustomAttribute()
        );
    }

    //########################################
}
