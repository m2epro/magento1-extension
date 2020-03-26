<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Mapping
{
    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $_account = null;

    protected $_mappingSettings = null;

    //########################################

    public function initialize(Ess_M2ePro_Model_Account $account = null)
    {
        $this->_account = $account;
        $this->_mappingSettings = null;
    }

    //########################################

    /**
     * @param array $otherListings
     * @return bool
     */
    public function autoMapOtherListingsProducts(array $otherListings)
    {
        $otherListingsFiltered = array();

        foreach ($otherListings as $otherListing) {
            if (!($otherListing instanceof Ess_M2ePro_Model_Listing_Other)) {
                continue;
            }

            /** @var $otherListing Ess_M2ePro_Model_Listing_Other */

            if ($otherListing->getProductId()) {
                continue;
            }

            $otherListingsFiltered[] = $otherListing;
        }

        if (empty($otherListingsFiltered)) {
            return false;
        }

        $accounts = array();

        foreach ($otherListingsFiltered as $otherListing) {

            /** @var $otherListing Ess_M2ePro_Model_Listing_Other */

            $identifier = $otherListing->getAccountId();

            if (!isset($accounts[$identifier])) {
                $accounts[$identifier] = array();
            }

            $accounts[$identifier][] = $otherListing;
        }

        $result = true;

        foreach ($accounts as $otherListings) {
            foreach ($otherListings as $otherListing) {
                /** @var $otherListing Ess_M2ePro_Model_Listing_Other */
                $temp = $this->autoMapOtherListingProduct($otherListing);
                $temp === false && $result = false;
            }
        }

        return $result;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function autoMapOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if ($otherListing->getProductId()) {
            return false;
        }

        $this->setAccountByOtherListingProduct($otherListing);

        if (!$this->getAccount()->getChildObject()->isOtherListingsMappingEnabled()) {
            return false;
        }

        $mappingSettings = $this->getMappingRulesByPriority();

        foreach ($mappingSettings as $type) {
            $magentoProductId = null;

            if ($type == 'sku') {
                $magentoProductId = $this->getSkuMappedMagentoProductId($otherListing);
            }

            if ($type == 'title') {
                $magentoProductId = $this->getTitleMappedMagentoProductId($otherListing);
            }

            if ($type == 'item_id') {
                $magentoProductId = $this->getItemIdMappedMagentoProductId($otherListing);
            }

            if ($magentoProductId === null) {
                continue;
            }

            $otherListing->mapProduct($magentoProductId);

            return true;
        }

        return false;
    }

    //########################################

    /**
     * @return array|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getMappingRulesByPriority()
    {
        if ($this->_mappingSettings !== null) {
            return $this->_mappingSettings;
        }

        $this->_mappingSettings = array();

        foreach ($this->getAccount()->getChildObject()->getOtherListingsMappingSettings() as $key=>$value) {
            if ((int)$value['mode'] == 0) {
                continue;
            }

            for ($i=0;$i<10;$i++) {
                if (!isset($this->_mappingSettings[(int)$value['priority']+$i])) {
                    $this->_mappingSettings[(int)$value['priority']+$i] = (string)$key;
                    break;
                }
            }
        }

        ksort($this->_mappingSettings);

        return $this->_mappingSettings;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return int|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getSkuMappedMagentoProductId(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $temp = $otherListing->getChildObject()->getSku();

        if (empty($temp)) {
            return null;
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeProductId()) {
            $productId = trim($otherListing->getChildObject()->getSku());

            if (!ctype_digit($productId) || (int)$productId <= 0) {
                return null;
            }

            $product = Mage::getModel('catalog/product')->load($productId);

            if ($product->getId() && $this->isMagentoProductTypeAllowed($product->getTypeId())) {
                return $product->getId();
            }

            return null;
        }

        $attributeCode = null;

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeDefault()) {
            $attributeCode = 'sku';
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeCustomAttribute()) {
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingSkuAttribute();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $otherListing->getChildObject()->getRelatedStoreId();
        $attributeValue = trim($otherListing->getChildObject()->getSku());

        $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if ($productObj && $productObj->getId() &&
            $this->isMagentoProductTypeAllowed($productObj->getTypeId())) {
            return $productObj->getId();
        }

        return null;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return int|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getTitleMappedMagentoProductId(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $temp = $otherListing->getChildObject()->getTitle();

        if (empty($temp)) {
            return null;
        }

        $attributeCode = null;

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingTitleModeDefault()) {
            $attributeCode = 'name';
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingTitleModeCustomAttribute()) {
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingTitleAttribute();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $otherListing->getChildObject()->getRelatedStoreId();
        $attributeValue = trim($otherListing->getChildObject()->getTitle());

        $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if ($productObj && $productObj->getId() &&
            $this->isMagentoProductTypeAllowed($productObj->getTypeId())) {
            return $productObj->getId();
        }

        return null;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return int|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getItemIdMappedMagentoProductId(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
        $ebayListingOther = $otherListing->getChildObject();

        $temp = $ebayListingOther->getItemId();

        if (empty($temp)) {
            return null;
        }

        $attributeCode = null;

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingItemIdModeCustomAttribute()) {
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingItemIdAttribute();
        }

        if ($attributeCode === null) {
            return null;
        }

        $storeId = $ebayListingOther->getRelatedStoreId();
        $attributeValue = $ebayListingOther->getItemId();

        $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if ($productObj && $productObj->getId() &&
            $this->isMagentoProductTypeAllowed($productObj->getTypeId())) {
            return $productObj->getId();
        }

        return null;
    }

    //########################################

    protected function isMagentoProductTypeAllowed($type)
    {
        $knownTypes = Mage::helper('M2ePro/Magento_Product')->getOriginKnownTypes();
        return in_array($type, $knownTypes);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->_account;
    }

    // ---------------------------------------

    protected function setAccountByOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if ($this->_account !== null && $this->_account->getId() == $otherListing->getAccountId()) {
            return;
        }

        $this->_account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Account', $otherListing->getAccountId()
        );

        $this->_mappingSettings = null;
    }

    //########################################
}