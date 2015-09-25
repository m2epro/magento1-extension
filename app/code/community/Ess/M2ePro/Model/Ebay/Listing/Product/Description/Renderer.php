<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Description_Renderer
{
    /* @var Ess_M2ePro_Model_Ebay_Listing_Product */
    protected $listingProduct = NULL;

    // ########################################

    public function setListingProduct(Ess_M2ePro_Model_Ebay_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
        return $this;
    }

    // ########################################

    public function parseTemplate($text)
    {
        $text = $this->insertValues($text);
        return $text;
    }

    // ########################################

    protected function insertValues($text)
    {
        preg_match_all("/#value\[(.+?)\]#/", $text, $matches);

        if (!count($matches[0])) {
            return $text;
        }

        $replaces = array();
        foreach ($matches[1] as $i => $attributeCode) {
            $method = 'get'.implode(array_map('ucfirst',explode('_', $attributeCode)));

            $arg = NULL;
            if (preg_match('/(?<=\[)(\d+?)(?=\])/',$method,$tempMatch)) {
                $arg = $tempMatch[0];
                $method = str_replace('['.$arg.']','',$method);
            }

            method_exists($this,$method) && $replaces[$matches[0][$i]] = $this->$method($arg);
        }

        $text = str_replace(array_keys($replaces), array_values($replaces), $text);

        return $text;
    }

    // ########################################

    protected function getQty()
    {
        return (int)$this->listingProduct->getQty();
    }

    // ----------------------------------------

    protected function getFixedPrice()
    {
        if (!$this->listingProduct->isListingTypeFixed()) {
            return 'N/A';
        }

        if ($this->listingProduct->isVariationsReady()) {

            $pricesList = array();

            foreach ($this->listingProduct->getVariations(true) as $variation) {
                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                $pricesList[] = $variation->getChildObject()->getPrice();
            }

            $price = count($pricesList) > 0 ? min($pricesList) : 0;

        } else {
            $price = $this->listingProduct->getFixedPrice();
        }

        if (empty($price)) {
            return 'N/A';
        }

        return sprintf('%01.2f', $price);
    }

    protected function getStartPrice()
    {
        if (!$this->listingProduct->isListingTypeAuction()) {
            return 'N/A';
        }

        $price = $this->listingProduct->getStartPrice();

        if (empty($price)) {
            return 'N/A';
        }

        return sprintf('%01.2f', $price);
    }

    protected function getReservePrice()
    {
        if (!$this->listingProduct->isListingTypeAuction()) {
            return 'N/A';
        }

        $price = $this->listingProduct->getReservePrice();

        if (empty($price)) {
            return 'N/A';
        }

        return sprintf('%01.2f', $price);
    }

    protected function getBuyItNowPrice()
    {
        if (!$this->listingProduct->isListingTypeAuction()) {
            return 'N/A';
        }

        $price = $this->listingProduct->getBuyItNowPrice();

        if (empty($price)) {
            return 'N/A';
        }

        return sprintf('%01.2f', $price);
    }

    // ########################################

    protected function getTitle()
    {
        return $this->listingProduct->getDescriptionTemplateSource()->getTitle();
    }

    protected function getSubtitle()
    {
        return $this->listingProduct->getDescriptionTemplateSource()->getSubTitle();
    }

    // ----------------------------------------

    protected function getListingType()
    {
        $helper = Mage::helper('M2ePro');

        $types = array(
            Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED => $helper->__('Fixed Price'),
            Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION => $helper->__('Auction'),
        );

        $type = $this->listingProduct->getSellingFormatTemplateSource()->getListingType();

        if (isset($types[$type])) {
            return $types[$type];
        }

        return 'N/A';
    }

    protected function getListingDuration()
    {
        $durations = Mage::helper('M2ePro/Component_Ebay')->getAvailableDurations();

        $duration = $this->listingProduct->getSellingFormatTemplateSource()->getDuration();

        if (isset($durations[$duration])) {
            return $durations[$duration];
        }

        return 'N/A';
    }

    protected function getHandlingTime()
    {
        $handlingTime = $this->listingProduct->getShippingTemplate()->getDispatchTime();

        $result = Mage::helper('M2ePro')->__('Business Day');

        if ($handlingTime > 1) {
            $result = Mage::helper('M2ePro')->__('Business Days');
        }

        if ($handlingTime) {
            $result = $handlingTime.' '.$result;
        } else {
            $result = Mage::helper('M2ePro')->__('Same').' '.$result;
        }

        return $result;
    }

    // ----------------------------------------

    protected function getCondition()
    {
        $conditions = array_combine(
            array(
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NEW,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NEW_OTHER,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NEW_WITH_DEFECT,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_MANUFACTURER_REFURBISHED,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_SELLER_REFURBISHED,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_USED,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_VERY_GOOD,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_GOOD,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_ACCEPTABLE,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NOT_WORKING,
            ),
            array(
                Mage::helper('M2ePro')->__('New'),
                Mage::helper('M2ePro')->__('New Other'),
                Mage::helper('M2ePro')->__('New With Defects'),
                Mage::helper('M2ePro')->__('Manufacturer Refurbished'),
                Mage::helper('M2ePro')->__('Seller Refurbished'),
                Mage::helper('M2ePro')->__('Used'),
                Mage::helper('M2ePro')->__('Very Good'),
                Mage::helper('M2ePro')->__('Good'),
                Mage::helper('M2ePro')->__('Acceptable'),
                Mage::helper('M2ePro')->__('For Parts or Not Working'),
            )
        );

        $condition = $this->listingProduct->getDescriptionTemplateSource()->getCondition();

        if (isset($conditions[$condition])) {
            return $conditions[$condition];
        }

        return Mage::helper('M2ePro')->__('N/A');
    }

    protected function getConditionDescription()
    {
        return $this->listingProduct->getDescriptionTemplateSource()->getConditionNote();
    }

    // ########################################

    protected function getPrimaryCategoryId()
    {
        if (!$this->listingProduct->isSetCategoryTemplate()) {
            return 'N/A';
        }

        $category = $this->listingProduct->getCategoryTemplateSource()->getMainCategory();
        return $category ? $category : 'N/A';
    }

    protected function getSecondaryCategoryId()
    {
        if (!$this->listingProduct->isSetOtherCategoryTemplate()) {
            return 'N/A';
        }

        $category = $this->listingProduct->getOtherCategoryTemplateSource()->getSecondaryCategory();
        return $category ? $category : 'N/A';
    }

    protected function getStorePrimaryCategoryId()
    {
        $category = $this->listingProduct->getOtherCategoryTemplateSource()->getStoreCategoryMain();
        return $category ? $category : 'N/A';
    }

    protected function getStoreSecondaryCategoryId()
    {
        $category = $this->listingProduct->getOtherCategoryTemplateSource()->getStoreCategorySecondary();
        return $category ? $category : 'N/A';
    }

    // ----------------------------------------

    protected function getPrimaryCategoryName()
    {
        $category = $this->listingProduct->getEbayMarketplace()->getCategory($this->getPrimaryCategoryId());

        if ($category) {
            return $category['title'];
        }

        return 'N/A';
    }

    protected function getSecondaryCategoryName()
    {
        $category = $this->listingProduct->getEbayMarketplace()->getCategory($this->getSecondaryCategoryId());

        if ($category) {
            return $category['title'];
        }

        return 'N/A';
    }

    protected function getStorePrimaryCategoryName()
    {
        $category = $this->listingProduct->getEbayAccount()->getEbayStoreCategory($this->getStorePrimaryCategoryId());

        if ($category) {
            return $category['title'];
        }

        return 'N/A';
    }

    protected function getStoreSecondaryCategoryName()
    {
        $category = $this->listingProduct->getEbayAccount()->getEbayStoreCategory($this->getStoreSecondaryCategoryId());

        if ($category) {
            return $category['title'];
        }

        return 'N/A';
    }

    // ########################################

    protected function getDomesticShippingMethod($i)
    {
        $services = array_values($this->listingProduct->getShippingTemplate()->getLocalShippingServices());

        --$i;
        if (!isset($services[$i])) {
            return 'N/A';
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_Shipping_Service $service */
        $service = $services[$i];

        $coreResource = Mage::getSingleton('core/resource');
        $connRead = $coreResource->getConnection('core_read');

        //------------------------------
        $tableDictShipping = $coreResource->getTableName('m2epro_ebay_dictionary_shipping');
        //------------------------------

        // table m2epro_ebay_dictionary_marketplace
        //------------------------------
        $dbSelect = $connRead
            ->select()
            ->from($tableDictShipping,'title')
            ->where('`ebay_id` = ?',$service->getShippingValue())
            ->where('`marketplace_id` = ?',(int)$this->listingProduct->getMarketplace()->getId());

        $shippingMethod = $dbSelect->query()->fetchColumn();

        return $shippingMethod ? $shippingMethod : 'N/A';
    }

    protected function getDomesticShippingCost($i)
    {
        $services = ($this->listingProduct->getShippingTemplate()->getLocalShippingServices());

        --$i;
        if (!isset($services[$i])) {
            return 'N/A';
        }

        $cost = $services[$i]->getSource($this->listingProduct->getMagentoProduct())
                             ->getCost();

        if (empty($cost)) {
            return Mage::helper('M2ePro')->__('Free');
        }

        return sprintf('%01.2f', $cost);
    }

    protected function getDomesticShippingAdditionalCost($i)
    {
        $services = ($this->listingProduct->getShippingTemplate()->getLocalShippingServices());

        --$i;
        if (!isset($services[$i])) {
            return 'N/A';
        }

        $cost = $services[$i]->getSource($this->listingProduct->getMagentoProduct())
                             ->getCostAdditional();

        if (empty($cost)) {
            return Mage::helper('M2ePro')->__('Free');
        }

        return sprintf('%01.2f', $cost);
    }

    // ----------------------------------------

    protected function getInternationalShippingMethod($i)
    {
        $services = array_values($this->listingProduct->getShippingTemplate()->getInternationalShippingServices());

        --$i;
        if (!isset($services[$i])) {
            return 'N/A';
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_Shipping_Service $service */
        $service = $services[$i];

        $coreResource = Mage::getSingleton('core/resource');
        $connRead = $coreResource->getConnection('core_read');

        //------------------------------
        $tableDictShipping = $coreResource->getTableName('m2epro_ebay_dictionary_shipping');
        //------------------------------

        // table m2epro_ebay_dictionary_marketplace
        //------------------------------
        $dbSelect = $connRead
            ->select()
            ->from($tableDictShipping,'title')
            ->where('`ebay_id` = ?',$service->getShippingValue())
            ->where('`marketplace_id` = ?',(int)$this->listingProduct->getMarketplace()->getId());

        $shippingMethod = $dbSelect->query()->fetchColumn();

        return $shippingMethod ? $shippingMethod : 'N/A';
    }

    protected function getInternationalShippingCost($i)
    {
        $services = ($this->listingProduct->getShippingTemplate()->getInternationalShippingServices());

        --$i;
        if (!isset($services[$i]) || !$services[$i]->getShippingValue()) {
            return 'N/A';
        }

        $cost = $services[$i]->getSource($this->listingProduct->getMagentoProduct())
                             ->getCost();

        if (empty($cost)) {
            return Mage::helper('M2ePro')->__('Free');
        }

        return sprintf('%01.2f', $cost);
    }

    protected function getInternationalShippingAdditionalCost($i)
    {
        $services = ($this->listingProduct->getShippingTemplate()->getInternationalShippingServices());

        --$i;
        if (!isset($services[$i]) || !$services[$i]->getShippingValue()) {
            return 'N/A';
        }

        $cost = $services[$i]->getSource($this->listingProduct->getMagentoProduct())
                             ->getCostAdditional();

        if (empty($cost)) {
            return Mage::helper('M2ePro')->__('Free');
        }

        return sprintf('%01.2f', $cost);
    }

    // ########################################
}