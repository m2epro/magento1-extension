<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Description_Renderer
{
    const MODE_FULL    = 1;
    const MODE_PREVIEW = 2;

    protected $_renderMode = self::MODE_FULL;

    //########################################

    /**
     * @return int
     */
    public function getRenderMode()
    {
        return $this->_renderMode;
    }

    /**
     * @param int $renderMode
     */
    public function setRenderMode($renderMode)
    {
        $this->_renderMode = $renderMode;
    }

    //########################################

    /** @var Ess_M2ePro_Model_Ebay_Listing_Product */
    protected $listingProduct = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product $listingProduct
     * @return $this
     */
    public function setListingProduct(Ess_M2ePro_Model_Ebay_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;

        return $this;
    }

    //########################################

    public function parseTemplate($text)
    {
        preg_match_all("/#value\[(.+?)\]#/", $text, $matches);

        if (!count($matches[0])) {
            return $text;
        }

        $replaces = array();
        foreach ($matches[1] as $i => $attributeCode) {
            $method = 'get' . implode(array_map('ucfirst', explode('_', $attributeCode)));

            $arg = null;
            if (preg_match('/(?<=\[)(\d+?)(?=\])/', $method, $tempMatch)) {
                $arg = $tempMatch[0];
                $method = str_replace('[' . $arg . ']', '', $method);
            }

            $value = '';
            method_exists($this, $method) && $value = $this->$method($arg);

            if (in_array($attributeCode, array('fixed_price', 'start_price', 'reserve_price', 'buyitnow_price'))) {
                $value = round($value, 2);
                $storeId = $this->listingProduct->getMagentoProduct()->getStoreId();
                $store = \Mage::app()->getStore($storeId);
                $value = $store->formatPrice($value, false);
            }

            ($value !== '') && $replaces[$matches[0][$i]] = $value;
        }

        $text = str_replace(array_keys($replaces), array_values($replaces), $text);

        return $text;
    }

    //########################################

    /**
     * @return int
     */
    protected function getQty()
    {
        return (int)$this->listingProduct->getQty();
    }

    // ---------------------------------------

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
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

            $price = !empty($pricesList) ? min($pricesList) : 0;
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

    //########################################

    protected function getTitle()
    {
        return $this->listingProduct->getDescriptionTemplateSource()->getTitle();
    }

    protected function getSubtitle()
    {
        return $this->listingProduct->getDescriptionTemplateSource()->getSubTitle();
    }

    // ---------------------------------------

    protected function getListingType()
    {
        $helper = Mage::helper('M2ePro');

        $types = array(
            Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED   => $helper->__('Fixed Price'),
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
        $handlingTime = $this->listingProduct->getShippingTemplateSource()->getDispatchTime();

        $result = Mage::helper('M2ePro')->__('Business Day');

        if ($handlingTime > 1) {
            $result = Mage::helper('M2ePro')->__('Business Days');
        }

        if ($handlingTime) {
            $result = $handlingTime . ' ' . $result;
        } else {
            $result = Mage::helper('M2ePro')->__('Same') . ' ' . $result;
        }

        return $result;
    }

    // ---------------------------------------

    protected function getCondition()
    {
        $conditions = array_combine(
            array(
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NEW,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NEW_OTHER,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NEW_WITH_DEFECT,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_CERTIFIED_REFURBISHED,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_EXCELLENT_REFURBISHED,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_VERY_GOOD_REFURBISHED,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_GOOD_REFURBISHED,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_SELLER_REFURBISHED,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_LIKE_NEW,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_PRE_OWNED_EXCELLENT,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_USED_EXCELLENT,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_PRE_OWNED_FAIR,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_VERY_GOOD,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_GOOD,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_ACCEPTABLE,
                Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NOT_WORKING,
            ),
            array(
                Mage::helper('M2ePro')->__('New'),
                Mage::helper('M2ePro')->__('New Other'),
                Mage::helper('M2ePro')->__('New With Defects'),
                Mage::helper('M2ePro')->__('Certified Refurbished'),
                Mage::helper('M2ePro')->__('Excellent (Refurbished)'),
                Mage::helper('M2ePro')->__('Very Good (Refurbished)'),
                Mage::helper('M2ePro')->__('Good (Refurbished)'),
                Mage::helper('M2ePro')->__('Seller Refurbished'),
                Mage::helper('M2ePro')->__('Like New'),
                Mage::helper('M2ePro')->__('Excellent (Pre-owned)'),
                Mage::helper('M2ePro')->__('Good (Pre-owned)'),
                Mage::helper('M2ePro')->__('Fair (Pre-owned)'),
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

    //########################################

    protected function getPrimaryCategoryId()
    {
        $source = $this->listingProduct->getCategoryTemplateSource();

        return $source ? $source->getCategoryId() : 'N/A';
    }

    protected function getSecondaryCategoryId()
    {
        $source = $this->listingProduct->getCategorySecondaryTemplateSource();

        return $source ? $source->getCategoryId() : 'N/A';
    }

    protected function getStorePrimaryCategoryId()
    {
        $source = $this->listingProduct->getStoreCategoryTemplateSource();

        return $source ? $source->getCategoryId() : 'N/A';
    }

    protected function getStoreSecondaryCategoryId()
    {
        $source = $this->listingProduct->getStoreCategorySecondaryTemplateSource();

        return $source ? $source->getCategoryId() : 'N/A';
    }

    // ---------------------------------------

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

    //########################################

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

        $tableDictShipping = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_shipping');

        // table m2epro_ebay_dictionary_marketplace
        $dbSelect = $connRead
            ->select()
            ->from($tableDictShipping, 'title')
            ->where('`ebay_id` = ?', $service->getShippingValue())
            ->where('`marketplace_id` = ?', (int)$this->listingProduct->getMarketplace()->getId());

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

    // ---------------------------------------

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

        // ---------------------------------------
        $tableDictShipping = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_shipping');
        // ---------------------------------------

        // table m2epro_ebay_dictionary_marketplace
        // ---------------------------------------
        $dbSelect = $connRead
            ->select()
            ->from($tableDictShipping, 'title')
            ->where('`ebay_id` = ?', $service->getShippingValue())
            ->where('`marketplace_id` = ?', (int)$this->listingProduct->getMarketplace()->getId());

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

    //########################################

    protected function getMainImage()
    {
        if ($this->_renderMode === self::MODE_FULL) {
            $mainImage = $this->listingProduct->getDescriptionTemplateSource()->getMainImage();
        } else {
            $mainImage = $this->listingProduct->getMagentoProduct()->getImage('image');
        }

        return !empty($mainImage) ? $mainImage->getUrl() : '';
    }

    protected function getGalleryImage($index)
    {
        if ($this->_renderMode === self::MODE_FULL) {
            $images = array_values($this->listingProduct->getDescriptionTemplateSource()->getGalleryImages());
        } else {
            $images = array_values($this->listingProduct->getMagentoProduct()->getGalleryImages(11));

            if ($index <= 0) {
                return '';
            }

            $index--;
        }

        if (!empty($images[$index]) && $images[$index]->getUrl()) {
            return $images[$index]->getUrl();
        }

        return '';
    }

    //########################################
}
