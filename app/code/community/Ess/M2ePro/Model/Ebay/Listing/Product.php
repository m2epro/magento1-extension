<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const TRANSLATION_STATUS_NONE                     = 0;
    const TRANSLATION_STATUS_PENDING                  = 1;
    const TRANSLATION_STATUS_PENDING_PAYMENT_REQUIRED = 2;
    const TRANSLATION_STATUS_IN_PROGRESS              = 3;
    const TRANSLATION_STATUS_TRANSLATED               = 4;

    //########################################

    /**
     * @var Ess_M2ePro_Model_Ebay_Item
     */
    protected $ebayItemModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $categoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private $otherCategoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Manager[]
     */
    private $templateManagers = array();

    // ---------------------------------------

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    private $synchronizationTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Description
     */
    private $descriptionTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Payment
     */
    private $paymentTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Return
     */
    private $returnTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    private $shippingTemplateModel = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Product');
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->ebayItemModel = NULL;
        $this->categoryTemplateModel = NULL;
        $this->otherCategoryTemplateModel = NULL;
        $this->templateManagers = array();
        $this->sellingFormatTemplateModel = NULL;
        $this->synchronizationTemplateModel = NULL;
        $this->descriptionTemplateModel = NULL;
        $this->paymentTemplateModel = NULL;
        $this->returnTemplateModel = NULL;
        $this->shippingTemplateModel = NULL;

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Item
     */
    public function getEbayItem()
    {
        if (is_null($this->ebayItemModel)) {
            $this->ebayItemModel = Mage::getModel('M2ePro/Ebay_Item')->loadInstance($this->getData('ebay_item_id'));
        }

        return $this->ebayItemModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Item $instance
     */
    public function setEbayItem(Ess_M2ePro_Model_Ebay_Item $instance)
    {
         $this->ebayItemModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategoryTemplate()
    {
        if (is_null($this->categoryTemplateModel) && $this->isSetCategoryTemplate()) {

            $this->categoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Category', (int)$this->getTemplateCategoryId(), NULL, array('template')
            );
        }

        return $this->categoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
         $this->categoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    public function getOtherCategoryTemplate()
    {
        if (is_null($this->otherCategoryTemplateModel) && $this->isSetOtherCategoryTemplate()) {

            $this->otherCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_OtherCategory', (int)$this->getTemplateOtherCategoryId(), NULL, array('template')
            );
        }

        return $this->otherCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance
     */
    public function setOtherCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance)
    {
         $this->otherCategoryTemplateModel = $instance;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing
     */
    public function getEbayListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    public function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //########################################

    /**
     * @param $template
     * @return Ess_M2ePro_Model_Ebay_Template_Manager
     */
    public function getTemplateManager($template)
    {
        if (!isset($this->templateManagers[$template])) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Manager $manager */
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setOwnerObject($this);
            $this->templateManagers[$template] = $manager->setTemplate($template);
        }

        return $this->templateManagers[$template];
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT;
            $this->sellingFormatTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
         $this->sellingFormatTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        if (is_null($this->synchronizationTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;
            $this->synchronizationTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->synchronizationTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Synchronization $instance
     */
    public function setSynchronizationTemplate(Ess_M2ePro_Model_Template_Synchronization $instance)
    {
         $this->synchronizationTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION;
            $this->descriptionTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->descriptionTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Description $instance
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
         $this->descriptionTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    public function getPaymentTemplate()
    {
        if (is_null($this->paymentTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;
            $this->paymentTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->paymentTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Payment $instance
     */
    public function setPaymentTemplate(Ess_M2ePro_Model_Ebay_Template_Payment $instance)
    {
         $this->paymentTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Return
     */
    public function getReturnTemplate()
    {
        if (is_null($this->returnTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN;
            $this->returnTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->returnTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Return $instance
     */
    public function setReturnTemplate(Ess_M2ePro_Model_Ebay_Template_Return $instance)
    {
         $this->returnTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        if (is_null($this->shippingTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;
            $this->shippingTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->shippingTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping $instance
     */
    public function setShippingTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping $instance)
    {
         $this->shippingTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    public function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Synchronization
     */
    public function getEbaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getEbayDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Source
     */
    public function getCategoryTemplateSource()
    {
        if (!$this->isSetCategoryTemplate()) {
            return NULL;
        }

        return $this->getCategoryTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory_Source
     */
    public function getOtherCategoryTemplateSource()
    {
        if (!$this->isSetOtherCategoryTemplate()) {
            return NULL;
        }

        return $this->getOtherCategoryTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat_Source
     */
    public function getSellingFormatTemplateSource()
    {
        return $this->getEbaySellingFormatTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description_Source
     */
    public function getDescriptionTemplateSource()
    {
        return $this->getEbayDescriptionTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Source
     */
    public function getShippingTemplateSource()
    {
        return $this->getShippingTemplate()->getSource($this->getMagentoProduct());
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getVariations($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getVariations($asObjects,$filters);
    }

    //########################################

    public function updateVariationsStatus()
    {
        foreach ($this->getVariations(true) as $variation) {
            $variation->getChildObject()->setStatus($this->getParentObject()->getStatus());
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Description_Renderer
    **/
    public function getDescriptionRenderer()
    {
        $renderer = Mage::getSingleton('M2ePro/Ebay_Listing_Product_Description_Renderer');
        $renderer->setListingProduct($this);

        return $renderer;
    }

    //########################################

    /**
     * @return float
     */
    public function getEbayItemIdReal()
    {
        return $this->getEbayItem()->getItemId();
    }

    //########################################

    /**
     * @return int
     */
    public function getEbayItemId()
    {
        return (int)$this->getData('ebay_item_id');
    }

    // ---------------------------------------

    public function getTemplateCategoryId()
    {
        return $this->getData('template_category_id');
    }

    public function getTemplateOtherCategoryId()
    {
        return $this->getData('template_other_category_id');
    }

    /**
     * @return bool
     */
    public function isSetCategoryTemplate()
    {
        return !is_null($this->getTemplateCategoryId());
    }

    /**
     * @return bool
     */
    public function isSetOtherCategoryTemplate()
    {
        return !is_null($this->getTemplateOtherCategoryId());
    }

    // ---------------------------------------

    public function getOnlineSku()
    {
        return $this->getData('online_sku');
    }

    public function getOnlineTitle()
    {
        return $this->getData('online_title');
    }

    // ---------------------------------------

    /**
     * @return float
     */
    public function getOnlineCurrentPrice()
    {
        return (float)$this->getData('online_current_price');
    }

    /**
     * @return float
     */
    public function getOnlineStartPrice()
    {
        return (float)$this->getData('online_start_price');
    }

    /**
     * @return float
     */
    public function getOnlineReservePrice()
    {
        return (float)$this->getData('online_reserve_price');
    }

    /**
     * @return float
     */
    public function getOnlineBuyItNowPrice()
    {
        return (float)$this->getData('online_buyitnow_price');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    /**
     * @return int
     */
    public function getOnlineQtySold()
    {
        return (int)$this->getData('online_qty_sold');
    }

    /**
     * @return int
     */
    public function getOnlineBids()
    {
        return (int)$this->getData('online_bids');
    }

    public function getOnlineCategory()
    {
        return $this->getData('online_category');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getTranslationStatus()
    {
        return (int)$this->getData('translation_status');
    }

    /**
     * @return bool
     */
    public function isTranslationStatusNone()
    {
        return $this->getTranslationStatus() == self::TRANSLATION_STATUS_NONE;
    }

    /**
     * @return bool
     */
    public function isTranslationStatusPending()
    {
        return $this->getTranslationStatus() == self::TRANSLATION_STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isTranslationStatusPendingPaymentRequired()
    {
        return $this->getTranslationStatus() == self::TRANSLATION_STATUS_PENDING_PAYMENT_REQUIRED;
    }

    /**
     * @return bool
     */
    public function isTranslationStatusInProgress()
    {
        return $this->getTranslationStatus() == self::TRANSLATION_STATUS_IN_PROGRESS;
    }

    /**
     * @return bool
     */
    public function isTranslationStatusTranslated()
    {
        return $this->getTranslationStatus() == self::TRANSLATION_STATUS_TRANSLATED;
    }

    /**
     * @return bool
     */
    public function isTranslatable()
    {
        return $this->isTranslationStatusPending() || $this->isTranslationStatusPendingPaymentRequired();
    }

    public function getTranslationService()
    {
        return $this->getData('translation_service');
    }

    public function getTranslatedDate()
    {
        return $this->getData('translated_date');
    }

    // ---------------------------------------

    public function getStartDate()
    {
        return $this->getData('start_date');
    }

    public function getEndDate()
    {
        return $this->getData('end_date');
    }

    //########################################

    public function getSku()
    {
        $sku = $this->getMagentoProduct()->getSku();

        if (strlen($sku) >= 50) {
            $sku = 'RANDOM_'.sha1($sku);
        }

        return $sku;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isListingTypeFixed()
    {
        return $this->getSellingFormatTemplateSource()->getListingType() ==
               Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED;
    }

    /**
     * @return bool
     */
    public function isListingTypeAuction()
    {
        return $this->getSellingFormatTemplateSource()->getListingType() ==
               Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_AUCTION;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isVariationMode()
    {
        if ($this->hasData(__METHOD__)) {
            return $this->getData(__METHOD__);
        }

        if (!$this->isSetCategoryTemplate()) {
            $this->setData(__METHOD__,false);
            return false;
        }

        $isVariationEnabled = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                                                ->isVariationEnabled(
                                                    (int)$this->getCategoryTemplateSource()->getMainCategory(),
                                                    $this->getMarketplace()->getId()
                                                );

        if (is_null($isVariationEnabled)) {
            $isVariationEnabled = true;
        }

        $result = $this->getEbayMarketplace()->isMultivariationEnabled() &&
                  !$this->getEbaySellingFormatTemplate()->isIgnoreVariationsEnabled() &&
                  $isVariationEnabled &&
                  $this->isListingTypeFixed() &&
                  $this->getMagentoProduct()->isProductWithVariations();

        $this->setData(__METHOD__,$result);

        return $result;
    }

    /**
     * @return bool
     */
    public function isVariationsReady()
    {
        if ($this->hasData(__METHOD__)) {
            return $this->getData(__METHOD__);
        }

        $result = $this->isVariationMode() && count($this->getVariations()) > 0;

        $this->setData(__METHOD__,$result);

        return $result;
    }

    //########################################

    /**
     * @return bool
     */
    public function isPriceDiscountStp()
    {
        return $this->getEbayMarketplace()->isStpEnabled() &&
               !$this->getEbaySellingFormatTemplate()->isPriceDiscountStpModeNone();
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMap()
    {
        return $this->getEbayMarketplace()->isMapEnabled() &&
               !$this->getEbaySellingFormatTemplate()->isPriceDiscountMapModeNone();
    }

    //########################################

    /**
     * @return float|int
     */
    public function getFixedPrice()
    {
        $src = $this->getEbaySellingFormatTemplate()->getFixedPriceSource();
        return $this->getCalculatedPrice($src, true, true);
    }

    // ---------------------------------------

    /**
     * @return float|int
     */
    public function getStartPrice()
    {
        $price = 0;

        if (!$this->isListingTypeAuction()) {
            return $price;
        }

        $src = $this->getEbaySellingFormatTemplate()->getStartPriceSource();
        return $this->getCalculatedPrice($src, true, true);
    }

    /**
     * @return float|int
     */
    public function getReservePrice()
    {
        $price = 0;

        if (!$this->isListingTypeAuction()) {
            return $price;
        }

        $src = $this->getEbaySellingFormatTemplate()->getReservePriceSource();
        return $this->getCalculatedPrice($src, true, true);
    }

    /**
     * @return float|int
     */
    public function getBuyItNowPrice()
    {
        $price = 0;

        if (!$this->isListingTypeAuction()) {
            return $price;
        }

        $src = $this->getEbaySellingFormatTemplate()->getBuyItNowPriceSource();
        return $this->getCalculatedPrice($src, true, true);
    }

    // ---------------------------------------

    /**
     * @return float|int
     */
    public function getPriceDiscountStp()
    {
        $src = $this->getEbaySellingFormatTemplate()->getPriceDiscountStpSource();
        return $this->getCalculatedPrice($src, true, false);
    }

    /**
     * @return float|int
     */
    public function getPriceDiscountMap()
    {
        $src = $this->getEbaySellingFormatTemplate()->getPriceDiscountMapSource();
        return $this->getCalculatedPrice($src, true, false);
    }

    // ---------------------------------------

    private function getCalculatedPrice($src, $increaseByVatPercent = false, $modifyByCoefficient = false)
    {
        /** @var $calculator Ess_M2ePro_Model_Ebay_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Ebay_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getParentObject());
        $calculator->setIsIncreaseByVatPercent($increaseByVatPercent);
        $calculator->setModifyByCoefficient($modifyByCoefficient);

        return $calculator->getProductValue();
    }

    //########################################

    /**
     * @return int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getQty()
    {
        if ($this->isListingTypeAuction()) {
            return 1;
        }

        if ($this->isVariationsReady()) {

            $qty = 0;

            foreach ($this->getVariations(true) as $variation) {
                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                $qty += $variation->getChildObject()->getQty();
            }

            return $qty;
        }

        /** @var $calculator Ess_M2ePro_Model_Ebay_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Ebay_Listing_Product_QtyCalculator');
        $calculator->setProduct($this->getParentObject());

        return $calculator->getProductValue();
    }

    //########################################

    /**
     * @return float|int
     */
    public function getBestOfferAcceptPrice()
    {
        if (!$this->isListingTypeFixed()) {
            return 0;
        }

        if (!$this->getEbaySellingFormatTemplate()->isBestOfferEnabled()) {
            return 0;
        }

        if ($this->getEbaySellingFormatTemplate()->isBestOfferAcceptModeNo()) {
            return 0;
        }

        $src = $this->getEbaySellingFormatTemplate()->getBestOfferAcceptSource();

        $price = 0;
        switch ($src['mode']) {
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_ACCEPT_MODE_PERCENTAGE:
                $price = $this->getFixedPrice() * (float)$src['value'] / 100;
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE:
                $price = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;
        }

        return round($price, 2);
    }

    /**
     * @return float|int
     */
    public function getBestOfferRejectPrice()
    {
        if (!$this->isListingTypeFixed()) {
            return 0;
        }

        if (!$this->getEbaySellingFormatTemplate()->isBestOfferEnabled()) {
            return 0;
        }

        if ($this->getEbaySellingFormatTemplate()->isBestOfferRejectModeNo()) {
            return 0;
        }

        $src = $this->getEbaySellingFormatTemplate()->getBestOfferRejectSource();

        $price = 0;
        switch ($src['mode']) {
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_REJECT_MODE_PERCENTAGE:
                $price = $this->getFixedPrice() * (float)$src['value'] / 100;
                break;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::BEST_OFFER_REJECT_MODE_ATTRIBUTE:
                $price = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;
        }

        return round($price, 2);
    }

    //########################################

    public function listAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_LIST, $params);
    }

    public function relistAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $params);
    }

    public function reviseAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $params);
    }

    public function stopAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_STOP, $params);
    }

    // ---------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        return Mage::getModel('M2ePro/Connector_Ebay_Item_Dispatcher')
            ->process($action, $this->getId(), $params);
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getTrackingAttributes()
    {
        $attributes = $this->getListing()->getTrackingAttributes();

        foreach (Mage::getModel('M2ePro/Ebay_Template_Manager')->getTrackingAttributesTemplates() as $template) {
            $templateManager = $this->getTemplateManager($template);
            $resultObjectTemp = $templateManager->getResultObject();
            if ($resultObjectTemp) {
                $attributes = array_merge($attributes,$resultObjectTemp->getTrackingAttributes());
            }
        }

        return array_unique($attributes);
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $templateManager = Mage::getSingleton('M2ePro/Ebay_Template_Manager');

        $newTemplates = $templateManager->getTemplatesFromData($newData);
        $oldTemplates = $templateManager->getTemplatesFromData($oldData);

        foreach ($templateManager->getAllTemplates() as $template) {

            $templateManager->setTemplate($template);

            $templateManager->getTemplateModel(true)->getResource()->setSynchStatusNeed(
                $newTemplates[$template]->getDataSnapshot(),
                $oldTemplates[$template]->getDataSnapshot(),
                array($this->getData())
            );
        }

        $this->getResource()->setSynchStatusNeedByCategoryTemplate($newData,$oldData,$this->getData());
        $this->getResource()->setSynchStatusNeedByOtherCategoryTemplate($newData,$oldData,$this->getData());
    }

    //########################################
}