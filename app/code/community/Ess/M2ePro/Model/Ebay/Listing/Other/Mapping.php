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
    protected $account = NULL;

    protected $mappingSettings = NULL;

    //########################################

    public function initialize(Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->account = $account;
        $this->mappingSettings = NULL;
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

        if (count($otherListingsFiltered) <= 0) {
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

            $magentoProductId = NULL;

            if ($type == 'sku') {
                $magentoProductId = $this->getSkuMappedMagentoProductId($otherListing);
            }

            if ($type == 'title') {
                $magentoProductId = $this->getTitleMappedMagentoProductId($otherListing);
            }

            if ($type == 'item_id') {
                $magentoProductId = $this->getItemIdMappedMagentoProductId($otherListing);
            }

            if (is_null($magentoProductId)) {
                continue;
            }

            $otherListing->mapProduct($magentoProductId, Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

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
        if (!is_null($this->mappingSettings)) {
            return $this->mappingSettings;
        }

        $this->mappingSettings = array();

        foreach ($this->getAccount()->getChildObject()->getOtherListingsMappingSettings() as $key=>$value) {
            if ((int)$value['mode'] == 0) {
                continue;
            }
            for ($i=0;$i<10;$i++) {
                if (!isset($this->mappingSettings[(int)$value['priority']+$i])) {
                    $this->mappingSettings[(int)$value['priority']+$i] = (string)$key;
                    break;
                }
            }
        }

        ksort($this->mappingSettings);

        return $this->mappingSettings;
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
            return NULL;
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeProductId()) {

            $productId = trim($otherListing->getChildObject()->getSku());

            if (!ctype_digit($productId) || (int)$productId <= 0) {
                return NULL;
            }

            $product = Mage::getModel('catalog/product')->load($productId);

            if ($product->getId()) {
                return $product->getId();
            }

            return NULL;
        }

        $attributeCode = NULL;

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeDefault()) {
            $attributeCode = 'sku';
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingSkuModeCustomAttribute()) {
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingSkuAttribute();
        }

        if (is_null($attributeCode)) {
            return NULL;
        }

        $storeId = $otherListing->getChildObject()->getRelatedStoreId();
        $attributeValue = trim($otherListing->getChildObject()->getSku());

        $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if ($productObj && $productObj->getId()) {
            return $productObj->getId();
        }

        return NULL;
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
            return NULL;
        }

        $attributeCode = NULL;

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingTitleModeDefault()) {
            $attributeCode = 'name';
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingTitleModeCustomAttribute()) {
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingTitleAttribute();
        }

        if (is_null($attributeCode)) {
            return NULL;
        }

        $storeId = $otherListing->getChildObject()->getRelatedStoreId();
        $attributeValue = trim($otherListing->getChildObject()->getTitle());

        $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if ($productObj && $productObj->getId()) {
            return $productObj->getId();
        }

        return NULL;
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
            return NULL;
        }

        $attributeCode = NULL;

        if ($this->getAccount()->getChildObject()->isOtherListingsMappingItemIdModeCustomAttribute()) {
            $attributeCode = $this->getAccount()->getChildObject()->getOtherListingsMappingItemIdAttribute();
        }

        if (is_null($attributeCode)) {
            return NULL;
        }

        $storeId = $ebayListingOther->getRelatedStoreId();
        $attributeValue = $ebayListingOther->getItemId();

        $productObj = Mage::getModel('catalog/product')->setStoreId($storeId);
        $productObj = $productObj->loadByAttribute($attributeCode, $attributeValue);

        if ($productObj && $productObj->getId()) {
            return $productObj->getId();
        }

        return NULL;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->account;
    }

    // ---------------------------------------

    protected function setAccountByOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if (!is_null($this->account) && $this->account->getId() == $otherListing->getAccountId()) {
            return;
        }

        $this->account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Account',$otherListing->getAccountId()
        );

        $this->mappingSettings = NULL;
    }

    //########################################
}