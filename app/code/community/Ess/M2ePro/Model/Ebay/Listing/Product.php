<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 * @method Ess_M2ePro_Model_Resource_Ebay_Listing_Product getResource()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED = 'channel_status_changed';
    const INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED    = 'channel_qty_changed';
    const INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED  = 'channel_price_changed';

    /**
     * @var Ess_M2ePro_Model_Ebay_Item
     */
    protected $_ebayItemModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    protected $_categoryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    protected $_categorySecondaryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    protected $_storeCategoryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    protected $_storeCategorySecondaryTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Manager[]
     */
    protected $_templateManagers = array();

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    protected $_sellingFormatTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    protected $_synchronizationTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Template_Description
     */
    protected $_descriptionTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Payment
     */
    protected $_paymentTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_ReturnPolicy
     */
    protected $_returnTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    protected $_shippingTemplateModel = null;

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

        $this->_ebayItemModel                       = null;
        $this->_categoryTemplateModel               = null;
        $this->_categorySecondaryTemplateModel      = null;
        $this->_storeCategoryTemplateModel          = null;
        $this->_storeCategorySecondaryTemplateModel = null;
        $this->_templateManagers                    = array();
        $this->_sellingFormatTemplateModel          = null;
        $this->_synchronizationTemplateModel        = null;
        $this->_descriptionTemplateModel            = null;
        $this->_paymentTemplateModel                = null;
        $this->_returnTemplateModel                 = null;
        $this->_shippingTemplateModel               = null;

        $this->delete();

        return true;
    }

    //########################################

    public function afterSaveNewEntity()
    {
        return null;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Item
     */
    public function getEbayItem()
    {
        if ($this->_ebayItemModel === null) {
            $this->_ebayItemModel = Mage::getModel('M2ePro/Ebay_Item')->loadInstance($this->getData('ebay_item_id'));
        }

        return $this->_ebayItemModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Item $instance
     */
    public function setEbayItem(Ess_M2ePro_Model_Ebay_Item $instance)
    {
        $this->_ebayItemModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategoryTemplate()
    {
        if ($this->_categoryTemplateModel === null && $this->isSetCategoryTemplate()) {
            $this->_categoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Category',
                (int)$this->getTemplateCategoryId(),
                null,
                array('template')
            );
        }

        return $this->_categoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
        $this->_categoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategorySecondaryTemplate()
    {
        if ($this->_categorySecondaryTemplateModel === null && $this->isSetCategorySecondaryTemplate()) {
            $this->_categorySecondaryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Category',
                (int)$this->getTemplateCategorySecondaryId(),
                null,
                array('template')
            );
        }

        return $this->_categorySecondaryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setCategorySecondaryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
        $this->_categorySecondaryTemplateModel = $instance;
    }

    //----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    public function getStoreCategoryTemplate()
    {
        if ($this->_storeCategoryTemplateModel === null && $this->isSetStoreCategoryTemplate()) {
            $this->_storeCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_StoreCategory',
                (int)$this->getTemplateStoreCategoryId(),
                null,
                array('template')
            );
        }

        return $this->_storeCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_StoreCategory $instance
     */
    public function setStoreCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_StoreCategory $instance)
    {
        $this->_storeCategoryTemplateModel = $instance;
    }

    //----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory
     */
    public function getStoreCategorySecondaryTemplate()
    {
        if ($this->_storeCategorySecondaryTemplateModel === null && $this->isSetStoreCategorySecondaryTemplate()) {
            $this->_storeCategorySecondaryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_StoreCategory',
                (int)$this->getTemplateStoreCategorySecondaryId(),
                null,
                array('template')
            );
        }

        return $this->_storeCategorySecondaryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_StoreCategory $instance
     */
    public function setStoreCategorySecondaryTemplate(Ess_M2ePro_Model_Ebay_Template_StoreCategory $instance)
    {
        $this->_storeCategorySecondaryTemplateModel = $instance;
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
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getVariationSpecificsReplacements()
    {
        $specificsReplacements = $this->getParentObject()->getSetting(
            'additional_data',
            'variations_specifics_replacements',
            array()
        );

        $replacements = array();
        foreach ($specificsReplacements as $findIt => $replaceBy) {
            $replacements[trim($findIt)] = trim($replaceBy);
        }

        return $replacements;
    }

    //########################################

    /**
     * @param $template
     * @return Ess_M2ePro_Model_Ebay_Template_Manager
     */
    public function getTemplateManager($template)
    {
        if (!isset($this->_templateManagers[$template])) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Manager $manager */
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setOwnerObject($this);
            $this->_templateManagers[$template] = $manager->setTemplate($template);
        }

        return $this->_templateManagers[$template];
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if ($this->_sellingFormatTemplateModel === null) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT;
            $this->_sellingFormatTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
        $this->_sellingFormatTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        if ($this->_synchronizationTemplateModel === null) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;
            $this->_synchronizationTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_synchronizationTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Synchronization $instance
     */
    public function setSynchronizationTemplate(Ess_M2ePro_Model_Template_Synchronization $instance)
    {
        $this->_synchronizationTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        if ($this->_descriptionTemplateModel === null) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION;
            $this->_descriptionTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_descriptionTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Description $instance
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
        $this->_descriptionTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    public function getPaymentTemplate()
    {
        if ($this->_paymentTemplateModel === null) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;
            $this->_paymentTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_paymentTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Payment $instance
     */
    public function setPaymentTemplate(Ess_M2ePro_Model_Ebay_Template_Payment $instance)
    {
        $this->_paymentTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_ReturnPolicy
     */
    public function getReturnTemplate()
    {
        if ($this->_returnTemplateModel === null) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY;
            $this->_returnTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_returnTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_ReturnPolicy $instance
     */
    public function setReturnTemplate(Ess_M2ePro_Model_Ebay_Template_ReturnPolicy $instance)
    {
        $this->_returnTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        if ($this->_shippingTemplateModel === null) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;
            $this->_shippingTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->_shippingTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping $instance
     */
    public function setShippingTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping $instance)
    {
        $this->_shippingTemplateModel = $instance;
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
            return null;
        }

        return $this->getCategoryTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Source
     */
    public function getCategorySecondaryTemplateSource()
    {
        if (!$this->isSetCategorySecondaryTemplate()) {
            return null;
        }

        return $this->getCategorySecondaryTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory_Source
     */
    public function getStoreCategoryTemplateSource()
    {
        if (!$this->isSetStoreCategoryTemplate()) {
            return null;
        }

        return $this->getStoreCategoryTemplate()->getSource($this->getMagentoProduct());
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory_Source
     */
    public function getStoreCategorySecondaryTemplateSource()
    {
        if (!$this->isSetStoreCategorySecondaryTemplate()) {
            return null;
        }

        return $this->getStoreCategorySecondaryTemplate()->getSource($this->getMagentoProduct());
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
     * @return Ess_M2ePro_Model_Listing_Product_Variation[]|array
     */
    public function getVariations($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getVariations($asObjects, $filters);
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

    public function getItemUUID()
    {
        return $this->getData('item_uuid');
    }

    public function generateItemUUID()
    {
        $uuid = str_pad($this->getAccount()->getId(), 2, '0', STR_PAD_LEFT);
        $uuid .= str_pad($this->getListing()->getId(), 4, '0', STR_PAD_LEFT);
        $uuid .= str_pad($this->getId(), 10, '0', STR_PAD_LEFT);

        // max int value is 2147483647 = 0x7FFFFFFF
        $randomPart = dechex(call_user_func('mt_rand', 0x000000, 0x7FFFFFFF));
        $uuid .= str_pad($randomPart, 16, '0', STR_PAD_LEFT);

        return strtoupper($uuid);
    }

    // ---------------------------------------

    public function getTemplateCategoryId()
    {
        return $this->getData('template_category_id');
    }

    public function getTemplateCategorySecondaryId()
    {
        return $this->getData('template_category_secondary_id');
    }

    public function getTemplateStoreCategoryId()
    {
        return $this->getData('template_store_category_id');
    }

    public function getTemplateStoreCategorySecondaryId()
    {
        return $this->getData('template_store_category_secondary_id');
    }

    //----------------------------------------

    /**
     * @return bool
     */
    public function isSetCategoryTemplate()
    {
        return $this->getTemplateCategoryId() !== null;
    }

    /**
     * @return bool
     */
    public function isSetCategorySecondaryTemplate()
    {
        return $this->getTemplateCategorySecondaryId() !== null;
    }

    /**
     * @return bool
     */
    public function isSetStoreCategoryTemplate()
    {
        return $this->getTemplateStoreCategoryId() !== null;
    }

    /**
     * @return bool
     */
    public function isSetStoreCategorySecondaryTemplate()
    {
        return $this->getTemplateStoreCategorySecondaryId() !== null;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOnlineVariation()
    {
        return (bool)$this->getData("online_is_variation");
    }

    /**
     * @return bool
     */
    public function isOnlineAuctionType()
    {
        return (bool)$this->getData("online_is_auction_type");
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

    public function getOnlineSubTitle()
    {
        return $this->getData('online_sub_title');
    }

    public function getOnlineDescription()
    {
        return $this->getData('online_description');
    }

    public function getOnlineImages()
    {
        return $this->getData('online_images');
    }

    public function getOnlineDuration()
    {
        return $this->getData('online_duration');
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

    public function getOnlineMainCategory()
    {
        return $this->getData('online_main_category');
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getOnlineCategoriesData()
    {
        return $this->getSettings('online_categories_data');
    }

    /**
     * @return array
     */
    public function getOnlineShippingData()
    {
        return $this->getData('online_shipping_data');
    }

    /**
     * @return array
     */
    public function getOnlinePaymentData()
    {
        return $this->getData('online_payment_data');
    }

    /**
     * @return array
     */
    public function getOnlineReturnData()
    {
        return $this->getData('online_return_data');
    }

    /**
     * @return array
     */
    public function getOnlineOtherData()
    {
        return $this->getData('online_other_data');
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
        return $this->getMagentoProduct()->getSku();
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

        if (!$this->isSetCategoryTemplate() || $this->getParentObject()->isGroupedProductModeSet()) {
            $this->setData(__METHOD__, false);

            return false;
        }

        $isVariationEnabled = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
            ->isVariationEnabled(
                (int)$this->getCategoryTemplateSource()->getCategoryId(),
                $this->getMarketplace()->getId()
            );

        if ($isVariationEnabled === null) {
            $isVariationEnabled = true;
        }

        $result = $this->getEbayMarketplace()->isMultivariationEnabled() &&
            !$this->getEbaySellingFormatTemplate()->isIgnoreVariationsEnabled() &&
            $isVariationEnabled &&
            $this->isListingTypeFixed() &&
            $this->getMagentoProduct()->isProductWithVariations();

        $this->setData(__METHOD__, $result);

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

        $variations = $this->getVariations();
        $result = $this->isVariationMode() && !empty($variations);

        $this->setData(__METHOD__, $result);

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

        $vatPercent = null;
        if ($this->getEbaySellingFormatTemplate()->isVatModeOnTopOfPrice()) {
            $vatPercent = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        return $this->getCalculatedPrice(
            $src,
            $vatPercent,
            $this->getEbaySellingFormatTemplate()->getFixedPriceCoefficient()
        );
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

        $vatPercent = null;
        if ($this->getEbaySellingFormatTemplate()->isVatModeOnTopOfPrice()) {
            $vatPercent = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        return $this->getCalculatedPrice(
            $src,
            $vatPercent,
            $this->getEbaySellingFormatTemplate()->getStartPriceCoefficient()
        );
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

        $vatPercent = null;
        if ($this->getEbaySellingFormatTemplate()->isVatModeOnTopOfPrice()) {
            $vatPercent = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        return $this->getCalculatedPrice(
            $src,
            $vatPercent,
            $this->getEbaySellingFormatTemplate()->getReservePriceCoefficient()
        );
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

        $vatPercent = null;
        if ($this->getEbaySellingFormatTemplate()->isVatModeOnTopOfPrice()) {
            $vatPercent = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        return $this->getCalculatedPrice(
            $src,
            $vatPercent,
            $this->getEbaySellingFormatTemplate()->getBuyItNowPriceCoefficient()
        );
    }

    // ---------------------------------------

    /**
     * @return float|int
     */
    public function getPriceDiscountStp()
    {
        $src = $this->getEbaySellingFormatTemplate()->getPriceDiscountStpSource();

        $vatPercent = null;
        if ($this->getEbaySellingFormatTemplate()->isVatModeOnTopOfPrice()) {
            $vatPercent = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        return $this->getCalculatedPrice($src, $vatPercent);
    }

    /**
     * @return float|int
     */
    public function getPriceDiscountMap()
    {
        $src = $this->getEbaySellingFormatTemplate()->getPriceDiscountMapSource();

        $vatPercent = null;
        if ($this->getEbaySellingFormatTemplate()->isVatModeOnTopOfPrice()) {
            $vatPercent = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        return $this->getCalculatedPrice($src, $vatPercent);
    }

    // ---------------------------------------

    protected function getCalculatedPrice($src, $vatPercent = null, $coefficient = null)
    {
        /** @var $calculator Ess_M2ePro_Model_Ebay_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Ebay_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getParentObject());
        $calculator->setVatPercent($vatPercent);
        $calculator->setCoefficient($coefficient);

        return $calculator->getProductValue();
    }

    //########################################

    /**
     * @param false $magentoMode
     * @return int|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getQty($magentoMode = false)
    {
        if ($this->isListingTypeAuction()) {
            return 1;
        }

        if ($this->isVariationsReady()) {
            $qty = 0;

            foreach ($this->getVariations(true) as $variation) {
                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                $qty += $variation->getChildObject()->getQty($magentoMode);
            }

            return $qty;
        }

        /** @var $calculator Ess_M2ePro_Model_Ebay_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Ebay_Listing_Product_QtyCalculator');
        $calculator->setProduct($this->getParentObject());
        $calculator->setIsMagentoMode($magentoMode);

        return $calculator->getProductValue();
    }

    //########################################

    public function isOutOfStockControlEnabled()
    {
        if ($this->getOnlineDuration() && !$this->isOnlineDurationGtc()) {
            return false;
        }

        if ($this->getEbayAccount()->getOutOfStockControl()) {
            return true;
        }

        return false;
    }

    //########################################

    public function isOnlineDurationGtc()
    {
        return $this->getOnlineDuration() == Ess_M2ePro_Helper_Component_Ebay::LISTING_DURATION_GTC;
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
                $price = (float)Mage::helper('M2ePro/Magento_Attribute')
                    ->convertAttributeTypePriceFromStoreToMarketplace(
                        $this->getMagentoProduct(),
                        $src['attribute'],
                        $this->getEbayListing()->getEbayMarketplace()->getCurrency(),
                        $this->getListing()->getStoreId()
                    );
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
                $price = (float)Mage::helper('M2ePro/Magento_Attribute')
                    ->convertAttributeTypePriceFromStoreToMarketplace(
                        $this->getMagentoProduct(),
                        $src['attribute'],
                        $this->getEbayListing()->getEbayMarketplace()->getCurrency(),
                        $this->getListing()->getStoreId()
                    );
                break;
        }

        return round($price, 2);
    }

    //########################################

    public function assignTemplatesToProducts(
        $productsIds,
        $categoryTemplateId = null,
        $categorySecondaryTemplateId = null,
        $storeCategoryTemplateId = null,
        $storeCategorySecondaryTemplateId = null
    ) {
        $this->getResource()->assignTemplatesToProducts(
            $productsIds,
            $categoryTemplateId,
            $categorySecondaryTemplateId,
            $storeCategoryTemplateId,
            $storeCategorySecondaryTemplateId
        );
    }

    //########################################

    public function mapChannelItemProduct()
    {
        $this->getResource()->mapChannelItemProduct($this);
    }

    //########################################
}
