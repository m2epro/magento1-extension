<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing getParentObject()
 * @method Ess_M2ePro_Model_Resource_Amazon_Listing getResource()
 */
class Ess_M2ePro_Model_Amazon_Listing extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const SKU_MODE_PRODUCT_ID       = 3;
    const SKU_MODE_DEFAULT          = 1;
    const SKU_MODE_CUSTOM_ATTRIBUTE = 2;

    const SKU_MODIFICATION_MODE_NONE     = 0;
    const SKU_MODIFICATION_MODE_PREFIX   = 1;
    const SKU_MODIFICATION_MODE_POSTFIX  = 2;
    const SKU_MODIFICATION_MODE_TEMPLATE = 3;

    const GENERATE_SKU_MODE_NO  = 0;
    const GENERATE_SKU_MODE_YES = 1;

    const GENERAL_ID_MODE_NOT_SET          = 0;
    const GENERAL_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    const WORLDWIDE_ID_MODE_NOT_SET          = 0;
    const WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    const CONDITION_MODE_DEFAULT          = 1;
    const CONDITION_MODE_CUSTOM_ATTRIBUTE = 2;

    const CONDITION_NEW                    = 'New';
    const CONDITION_USED_LIKE_NEW          = 'UsedLikeNew';
    const CONDITION_USED_VERY_GOOD         = 'UsedVeryGood';
    const CONDITION_USED_GOOD              = 'UsedGood';
    const CONDITION_USED_ACCEPTABLE        = 'UsedAcceptable';
    const CONDITION_COLLECTIBLE_LIKE_NEW   = 'CollectibleLikeNew';
    const CONDITION_COLLECTIBLE_VERY_GOOD  = 'CollectibleVeryGood';
    const CONDITION_COLLECTIBLE_GOOD       = 'CollectibleGood';
    const CONDITION_COLLECTIBLE_ACCEPTABLE = 'CollectibleAcceptable';
    const CONDITION_REFURBISHED            = 'Refurbished';
    const CONDITION_CLUB                   = 'Club';

    const CONDITION_NOTE_MODE_NONE         = 3;
    const CONDITION_NOTE_MODE_CUSTOM_VALUE = 1;

    const IMAGE_MAIN_MODE_NONE      = 0;
    const IMAGE_MAIN_MODE_PRODUCT   = 1;
    const IMAGE_MAIN_MODE_ATTRIBUTE = 2;

    const GALLERY_IMAGES_MODE_NONE      = 0;
    const GALLERY_IMAGES_MODE_PRODUCT   = 1;
    const GALLERY_IMAGES_MODE_ATTRIBUTE = 2;

    const GALLERY_IMAGES_COUNT_MAX = 5;

    const HANDLING_TIME_MODE_NONE             = 3;
    const HANDLING_TIME_MODE_RECOMMENDED      = 1;
    const HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE = 2;

    const RESTOCK_DATE_MODE_NONE             = 1;
    const RESTOCK_DATE_MODE_CUSTOM_VALUE     = 2;
    const RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE = 3;

    const GIFT_WRAP_MODE_NO        = 0;
    const GIFT_WRAP_MODE_YES       = 1;
    const GIFT_WRAP_MODE_ATTRIBUTE = 2;

    const GIFT_MESSAGE_MODE_NO        = 0;
    const GIFT_MESSAGE_MODE_YES       = 1;
    const GIFT_MESSAGE_MODE_ATTRIBUTE = 2;

    const ADDING_MODE_ADD_AND_CREATE_NEW_ASIN_NO  = 0;
    const ADDING_MODE_ADD_AND_CREATE_NEW_ASIN_YES = 1;

    const CREATE_LISTING_SESSION_DATA = 'amazon_listing_create';

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    protected $_sellingFormatTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    protected $_synchronizationTemplateModel = null;

    /** @var Ess_M2ePro_Model_Amazon_Listing_Source[] */
    protected $_listingSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_sellingFormatTemplateModel = null;
        $temp && $this->_synchronizationTemplateModel = null;

        return $temp;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Amazon_Listing_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->_listingSourceModels[$productId])) {
            return $this->_listingSourceModels[$productId];
        }

        $this->_listingSourceModels[$productId] = Mage::getModel('M2ePro/Amazon_Listing_Source');
        $this->_listingSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->_listingSourceModels[$productId]->setListing($this->getParentObject());

        return $this->_listingSourceModels[$productId];
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account
     */
    public function getAmazonAccount()
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
     * @return Ess_M2ePro_Model_Amazon_Marketplace
     */
    public function getAmazonMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if ($this->_sellingFormatTemplateModel === null) {
            $this->_sellingFormatTemplateModel = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Template_SellingFormat',
                $this->getData('template_selling_format_id'),
                null,
                array('template')
            );
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
            $this->_synchronizationTemplateModel = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Template_Synchronization',
                $this->getData('template_synchronization_id'),
                null,
                array('template')
            );
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
     * @return Ess_M2ePro_Model_Amazon_Template_SellingFormat
     */
    public function getAmazonSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Synchronization
     */
    public function getAmazonSynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getTemplateShippingId()
    {
        return (int)($this->getData('template_shipping_id'));
    }

    /**
     * @return bool
     */
    public function isExistShippingTemplate()
    {
        return $this->getTemplateShippingId() > 0;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Shipping | null
     */
    public function getShippingTemplate()
    {
        if (!$this->isExistShippingTemplate()) {
            return null;
        }

        return Mage::helper('M2ePro')->getCachedObject(
            'Amazon_Template_Shipping',
            $this->getTemplateShippingId(),
            null,
            array('template')
        );
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Amazon_Template_Shipping_Source
     */
    public function getShippingTemplateSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        if (!$this->isExistShippingTemplate()) {
            return null;
        }

        return $this->getShippingTemplate()->getSource($magentoProduct);
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getProducts($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getProducts($asObjects, $filters);
    }

    //########################################

    /**
     * @return int
     */
    public function getAutoGlobalAddingDescriptionTemplateId()
    {
        return (int)$this->getData('auto_global_adding_description_template_id');
    }

    /**
     * @return int
     */
    public function getAutoWebsiteAddingDescriptionTemplateId()
    {
        return (int)$this->getData('auto_website_adding_description_template_id');
    }

    //########################################

    /**
     * @return int
     */
    public function getSkuMode()
    {
        return (int)$this->getData('sku_mode');
    }

    /**
     * @return bool
     */
    public function isSkuProductIdMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_PRODUCT_ID;
    }

    /**
     * @return bool
     */
    public function isSkuDefaultMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_DEFAULT;
    }

    /**
     * @return bool
     */
    public function isSkuAttributeMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getSkuSource()
    {
        return array(
            'mode'      => $this->getSkuMode(),
            'attribute' => $this->getData('sku_custom_attribute')
        );
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getSkuModificationMode()
    {
        return (int)$this->getData('sku_modification_mode');
    }

    /**
     * @return bool
     */
    public function isSkuModificationModeNone()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isSkuModificationModePrefix()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_PREFIX;
    }

    /**
     * @return bool
     */
    public function isSkuModificationModePostfix()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_POSTFIX;
    }

    /**
     * @return bool
     */
    public function isSkuModificationModeTemplate()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_TEMPLATE;
    }

    /**
     * @return array
     */
    public function getSkuModificationSource()
    {
        return array(
            'mode'  => $this->getSkuModificationMode(),
            'value' => $this->getData('sku_modification_custom_value')
        );
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getGenerateSkuMode()
    {
        return (int)$this->getData('generate_sku_mode');
    }

    /**
     * @return bool
     */
    public function isGenerateSkuModeNo()
    {
        return $this->getGenerateSkuMode() == self::GENERATE_SKU_MODE_NO;
    }

    /**
     * @return bool
     */
    public function isGenerateSkuModeYes()
    {
        return $this->getGenerateSkuMode() == self::GENERATE_SKU_MODE_YES;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getGeneralIdMode()
    {
        return (int)$this->getData('general_id_mode');
    }

    /**
     * @return bool
     */
    public function isGeneralIdNotSetMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_NOT_SET;
    }

    /**
     * @return bool
     */
    public function isGeneralIdAttributeMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getGeneralIdSource()
    {
        return array(
            'mode'      => $this->getGeneralIdMode(),
            'attribute' => $this->getData('general_id_custom_attribute')
        );
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getWorldwideIdMode()
    {
        return (int)$this->getData('worldwide_id_mode');
    }

    /**
     * @return bool
     */
    public function isWorldwideIdNotSetMode()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_NOT_SET;
    }

    /**
     * @return bool
     */
    public function isWorldwideIdAttributeMode()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getWorldwideIdSource()
    {
        return array(
            'mode'      => $this->getWorldwideIdMode(),
            'attribute' => $this->getData('worldwide_id_custom_attribute')
        );
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getConditionMode()
    {
        return (int)$this->getData('condition_mode');
    }

    /**
     * @return bool
     */
    public function isConditionDefaultMode()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_DEFAULT;
    }

    /**
     * @return bool
     */
    public function isConditionAttributeMode()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getConditionSource()
    {
        return array(
            'mode'      => $this->getConditionMode(),
            'value'     => $this->getData('condition_value'),
            'attribute' => $this->getData('condition_custom_attribute')
        );
    }

    public function getConditionValues()
    {
        $temp = $this->getData('cache_condition_values');

        if (!empty($temp)) {
            return $temp;
        }

        $reflectionClass = new ReflectionClass(__CLASS__);
        $tempConstants = $reflectionClass->getConstants();

        $values = array();
        foreach ($tempConstants as $key => $value) {
            $prefixKey = strtolower(substr($key, 0, 14));
            if (substr($prefixKey, 0, 10) != 'condition_' ||
                in_array($prefixKey, array('condition_mode', 'condition_note'))) {
                continue;
            }

            $values[] = $value;
        }

        $this->setData('cache_condition_values', $values);

        return $values;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getConditionNoteMode()
    {
        return (int)$this->getData('condition_note_mode');
    }

    /**
     * @return bool
     */
    public function isConditionNoteNoneMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isConditionNoteValueMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_CUSTOM_VALUE;
    }

    /**
     * @return array
     */
    public function getConditionNoteSource()
    {
        return array(
            'mode'  => $this->getConditionNoteMode(),
            'value' => $this->getData('condition_note_value')
        );
    }

    /**
     * @return array
     */
    public function getConditionNoteAttributes()
    {
        $attributes = array();
        $src = $this->getConditionNoteSource();

        if ($src['mode'] == self::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $match = array();
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $src['value'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getImageMainMode()
    {
        return (int)$this->getData('image_main_mode');
    }

    /**
     * @return bool
     */
    public function isImageMainModeNone()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isImageMainModeProduct()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isImageMainModeAttribute()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getImageMainSource()
    {
        return array(
            'mode'      => $this->getImageMainMode(),
            'attribute' => $this->getData('image_main_attribute')
        );
    }

    /**
     * @return array
     */
    public function getImageMainAttributes()
    {
        $attributes = array();
        $src = $this->getImageMainSource();

        if ($src['mode'] == self::IMAGE_MAIN_MODE_PRODUCT) {
            $attributes[] = 'image';
        } else {
            if ($src['mode'] == self::IMAGE_MAIN_MODE_ATTRIBUTE) {
                $attributes[] = $src['attribute'];
            }
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getGalleryImagesMode()
    {
        return (int)$this->getData('gallery_images_mode');
    }

    /**
     * @return bool
     */
    public function isGalleryImagesModeNone()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isGalleryImagesModeProduct()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isGalleryImagesModeAttribute()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getGalleryImagesSource()
    {
        return array(
            'mode'      => $this->getGalleryImagesMode(),
            'limit'     => $this->getData('gallery_images_limit'),
            'attribute' => $this->getData('gallery_images_attribute')
        );
    }

    /**
     * @return array
     */
    public function getGalleryImagesAttributes()
    {
        $attributes = array();
        $src = $this->getGalleryImagesSource();

        if ($src['mode'] == self::GALLERY_IMAGES_MODE_PRODUCT) {
            $attributes[] = 'media_gallery';
        } else {
            if ($src['mode'] == self::GALLERY_IMAGES_MODE_ATTRIBUTE) {
                $attributes[] = $src['attribute'];
            }
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getHandlingTimeMode()
    {
        return (int)$this->getData('handling_time_mode');
    }

    /**
     * @return bool
     */
    public function isHandlingTimeNoneMode()
    {
        return $this->getHandlingTimeMode() == self::HANDLING_TIME_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isHandlingTimeRecommendedMode()
    {
        return $this->getHandlingTimeMode() == self::HANDLING_TIME_MODE_RECOMMENDED;
    }

    /**
     * @return bool
     */
    public function isHandlingTimeAttributeMode()
    {
        return $this->getHandlingTimeMode() == self::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getHandlingTimeSource()
    {
        return array(
            'mode'      => $this->getHandlingTimeMode(),
            'value'     => (int)$this->getData('handling_time_value'),
            'attribute' => $this->getData('handling_time_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getHandlingTimeAttributes()
    {
        $attributes = array();
        $src = $this->getHandlingTimeSource();

        if ($src['mode'] == self::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getRestockDateMode()
    {
        return (int)$this->getData('restock_date_mode');
    }

    /**
     * @return bool
     */
    public function isRestockDateNoneMode()
    {
        return $this->getRestockDateMode() == self::RESTOCK_DATE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isRestockDateValueMode()
    {
        return $this->getRestockDateMode() == self::RESTOCK_DATE_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isRestockDateAttributeMode()
    {
        return $this->getRestockDateMode() == self::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getRestockDateSource()
    {
        return array(
            'mode'      => $this->getRestockDateMode(),
            'value'     => $this->getData('restock_date_value'),
            'attribute' => $this->getData('restock_date_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getRestockDateAttributes()
    {
        $attributes = array();
        $src = $this->getRestockDateSource();

        if ($src['mode'] == self::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getGiftWrapMode()
    {
        return $this->getData('gift_wrap_mode');
    }

    /**
     * @return bool
     */
    public function isGiftWrapModeYes()
    {
        return $this->getGiftWrapMode() == self::GIFT_WRAP_MODE_YES;
    }

    /**
     * @return bool
     */
    public function isGiftWrapModeNo()
    {
        return $this->getGiftWrapMode() == self::GIFT_WRAP_MODE_NO;
    }

    /**
     * @return bool
     */
    public function isGiftWrapModeAttribute()
    {
        return $this->getGiftWrapMode() == self::GIFT_WRAP_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getGiftWrapSource()
    {
        return array(
            'mode'      => $this->getGiftWrapMode(),
            'attribute' => $this->getData('gift_wrap_attribute')
        );
    }

    /**
     * @return array
     */
    public function getGiftWrapAttributes()
    {
        $attributes = array();
        $src = $this->getGiftWrapSource();

        if ($src['mode'] == self::GIFT_WRAP_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getGiftMessageMode()
    {
        return $this->getData('gift_message_mode');
    }

    /**
     * @return bool
     */
    public function isGiftMessageModeYes()
    {
        return $this->getGiftMessageMode() == self::GIFT_MESSAGE_MODE_YES;
    }

    /**
     * @return bool
     */
    public function isGiftMessageModeNo()
    {
        return $this->getGiftMessageMode() == self::GIFT_MESSAGE_MODE_NO;
    }

    /**
     * @return bool
     */
    public function isGiftMessageModeAttribute()
    {
        return $this->getGiftMessageMode() == self::GIFT_MESSAGE_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getGiftMessageSource()
    {
        return array(
            'mode'      => $this->getGiftMessageMode(),
            'attribute' => $this->getData('gift_message_attribute')
        );
    }

    /**
     * @return array
     */
    public function getGiftMessageAttributes()
    {
        $attributes = array();
        $src = $this->getGiftMessageSource();

        if ($src['mode'] == self::GIFT_MESSAGE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getAddedListingProductsIds()
    {
        $ids = $this->getData('product_add_ids');
        $ids = array_filter((array)Mage::helper('M2ePro')->jsonDecode($ids));

        return array_values(array_unique($ids));
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Other $listingOtherProduct
     * @param int $initiator
     * @return bool|Ess_M2ePro_Model_Listing_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function addProductFromOther(
        Ess_M2ePro_Model_Listing_Other $listingOtherProduct,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN
    ) {
        if (!$listingOtherProduct->getProductId()) {
            return false;
        }

        $productId = $listingOtherProduct->getProductId();
        $result = $this->getParentObject()->addProduct($productId, $initiator, false, true);

        if (!($result instanceof Ess_M2ePro_Model_Listing_Product)) {
            return false;
        }

        $listingProduct = $result;

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        if ($variationManager->isRelationParentType()) {
            $variationManager->switchModeToAnother();
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Other $amazonListingOther */
        $amazonListingOther = $listingOtherProduct->getChildObject();

        $dataForUpdate = array(
            'general_id'           => $amazonListingOther->getGeneralId(),
            'sku'                  => $amazonListingOther->getSku(),
            'online_regular_price' => $amazonListingOther->getOnlinePrice(),
            'online_qty'           => $amazonListingOther->getOnlineQty(),
            'is_repricing'         => (int)$amazonListingOther->isRepricing(),
            'is_afn_channel'       => (int)$amazonListingOther->isAfnChannel(),
            'is_isbn_general_id'   => (int)$amazonListingOther->isIsbnGeneralId(),
            'status'               => $listingOtherProduct->getStatus(),
            'status_changer'       => $listingOtherProduct->getStatusChanger()
        );

        $listingProduct->addData($dataForUpdate);
        $listingProduct->setSetting(
            'additional_data',
            $listingProduct::MOVING_LISTING_OTHER_SOURCE_KEY,
            $listingOtherProduct->getId()
        );

        if ($listingProduct->getMagentoProduct()->isGroupedType() &&
            Mage::helper('M2ePro/Module_Configuration')->isGroupedProductModeSet()
        ) {
            $listingProduct->setSetting('additional_data', 'grouped_product_mode', 1);
        }

        $listingProduct->save();

        $listingOtherProduct->setSetting(
            'additional_data',
            $listingOtherProduct::MOVING_LISTING_PRODUCT_DESTINATION_KEY,
            $listingProduct->getId()
        );
        $listingOtherProduct->save();

        $amazonItem = $amazonListingProduct->getAmazonItem();
        if ($listingProduct->getMagentoProduct()->isGroupedType() &&
            Mage::helper('M2ePro/Module_Configuration')->isGroupedProductModeSet()
        ) {
            $amazonItem->setAdditionalData(json_encode(array('grouped_product_mode' => 1)));
        }

        $amazonItem->setData('store_id', $this->getParentObject()->getStoreId())
            ->save();

        if ($amazonListingOther->isRepricing()) {
            $listingProductRepricing = Mage::getModel('M2ePro/Amazon_Listing_Product_Repricing');
            $listingProductRepricing->setData(
                array(
                    'listing_product_id' => $listingProduct->getId(),
                    'is_online_disabled' => $amazonListingOther->isRepricingDisabled(),
                    'is_online_inactive' => $amazonListingOther->isRepricingInactive(),
                    'update_date'        => Mage::helper('M2ePro')->getCurrentGmtDate(),
                    'create_date'        => Mage::helper('M2ePro')->getCurrentGmtDate(),
                )
            );
            $listingProductRepricing->save();
        }

        $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
        $instruction->setData(
            array(
                'listing_product_id' => $listingProduct->getId(),
                'component'          => Ess_M2ePro_Helper_Component_Amazon::NICK,
                'type'               => Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
                'initiator'          => Ess_M2ePro_Model_Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER,
                'priority'           => 20,
            )
        );
        $instruction->save();

        return $listingProduct;
    }

    public function addProductFromAnotherAmazonSite(
        Ess_M2ePro_Model_Listing_Product $sourceListingProduct,
        Ess_M2ePro_Model_Listing $sourceListing
    ) {
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = $this->getParentObject()->addProduct(
            $sourceListingProduct->getProductId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER
        );

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode($this->getComponentMode());

        $logMessage = Mage::helper('M2ePro')->__(
            'Product was copied from %previous_listing_name% (%previous_marketplace%) 
            Listing to %current_listing_name% (%current_marketplace%) Listing.',
            $sourceListing->getTitle(),
            $sourceListing->getMarketplace()->getCode(),
            $this->getParentObject()->getTitle(),
            $this->getMarketplace()->getCode()
        );

        if ($listingProduct instanceof Ess_M2ePro_Model_Listing_Product) {
            $logModel->addProductMessage(
                $sourceListing->getId(),
                $sourceListingProduct->getProductId(),
                $sourceListingProduct->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                $logModel->getResource()->getNextActionId(),
                Ess_M2ePro_Model_Listing_Log::ACTION_SELL_ON_ANOTHER_SITE,
                $logMessage,
                Ess_M2ePro_Model_Log_Abstract::TYPE_INFO
            );

            if ($sourceListing->getMarketplaceId() == $this->getParentObject()->getMarketplaceId()) {
                $listingProduct->getChildObject()->setData(
                    'template_description_id',
                    $sourceListingProduct->getChildObject()->getTemplateDescriptionId()
                );
                $listingProduct->getChildObject()->setData(
                    'template_shipping_id',
                    $sourceListingProduct->getChildObject()->getTemplateShippingId()
                );
                $listingProduct->getChildObject()->setData(
                    'template_product_tax_code_id',
                    $sourceListingProduct->getChildObject()->getTemplateProductTaxCodeId()
                );
            }

            // @codingStandardsIgnoreLine
            $listingProduct->getChildObject()->save();

            return $listingProduct;
        }

        $logMessage = Mage::helper('M2ePro')->__(
            'Product already exists in the %listing_name% Listing.',
            $this->getParentObject()->getTitle()
        );

        $logModel->addProductMessage(
            $sourceListing->getId(),
            $sourceListingProduct->getProductId(),
            $sourceListingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            $logModel->getResource()->getNextActionId(),
            Ess_M2ePro_Model_Listing_Log::ACTION_SELL_ON_ANOTHER_SITE,
            $logMessage,
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
        );

        return false;
    }

    public function addProductFromListing(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        Ess_M2ePro_Model_Listing $sourceListing
    ) {
        if (!$this->getParentObject()->addProductFromListing($listingProduct, $sourceListing, false)) {
            return false;
        }

        if ($this->getParentObject()->getStoreId() != $sourceListing->getStoreId()) {
            if (!$listingProduct->isNotListed()) {
                if ($item = $listingProduct->getChildObject()->getAmazonItem()) {
                    $item->setData('store_id', $this->getParentObject()->getStoreId());
                    $item->save();
                }
            }
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        if ($variationManager->isRelationParentType()) {
            Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->moveChildrenToListing($listingProduct);
        }

        return true;
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('listing');

        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('listing');

        return parent::delete();
    }

    //########################################
}
