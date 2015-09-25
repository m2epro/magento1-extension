<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Amazon_Listing getResource()
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

    const SEARCH_BY_MAGENTO_TITLE_MODE_NONE = 0;
    const SEARCH_BY_MAGENTO_TITLE_MODE_YES  = 1;

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

    const CONDITION_NOTE_MODE_NONE             = 3;
    const CONDITION_NOTE_MODE_CUSTOM_VALUE     = 1;

    const IMAGE_MAIN_MODE_NONE           = 0;
    const IMAGE_MAIN_MODE_PRODUCT        = 1;
    const IMAGE_MAIN_MODE_ATTRIBUTE      = 2;

    const GALLERY_IMAGES_MODE_NONE       = 0;
    const GALLERY_IMAGES_MODE_PRODUCT    = 1;
    const GALLERY_IMAGES_MODE_ATTRIBUTE  = 2;

    const GALLERY_IMAGES_COUNT_MAX       = 5;

    const HANDLING_TIME_MODE_NONE             = 3;
    const HANDLING_TIME_MODE_RECOMMENDED      = 1;
    const HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE = 2;

    const RESTOCK_DATE_MODE_NONE              = 1;
    const RESTOCK_DATE_MODE_CUSTOM_VALUE      = 2;
    const RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE  = 3;

    const GIFT_WRAP_MODE_NO = 0;
    const GIFT_WRAP_MODE_YES = 1;
    const GIFT_WRAP_MODE_ATTRIBUTE = 2;

    const GIFT_MESSAGE_MODE_NO = 0;
    const GIFT_MESSAGE_MODE_YES = 1;
    const GIFT_MESSAGE_MODE_ATTRIBUTE = 2;

    const ADDING_MODE_ADD_AND_CREATE_NEW_ASIN_NO  = 0;
    const ADDING_MODE_ADD_AND_CREATE_NEW_ASIN_YES = 1;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    private $synchronizationTemplateModel = NULL;

    /** @var Ess_M2ePro_Model_Amazon_Listing_Source[] */
    private $listingSourceModels = array();

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->sellingFormatTemplateModel = NULL;
        $temp && $this->synchronizationTemplateModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Amazon_Listing_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->listingSourceModels[$productId])) {
            return $this->listingSourceModels[$productId];
        }

        $this->listingSourceModels[$productId] = Mage::getModel('M2ePro/Amazon_Listing_Source');
        $this->listingSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->listingSourceModels[$productId]->setListing($this->getParentObject());

        return $this->listingSourceModels[$productId];
    }

    // ########################################

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

    //-----------------------------------------

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

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplateModel)) {
            $this->sellingFormatTemplateModel = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Template_SellingFormat',$this->getData('template_selling_format_id'),NULL,array('template')
            );
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

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        if (is_null($this->synchronizationTemplateModel)) {
            $this->synchronizationTemplateModel = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Template_Synchronization', $this->getData('template_synchronization_id'),NULL,array('template')
            );
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

    //-----------------------------------------

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

    // ########################################

    public function getProducts($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getProducts($asObjects,$filters);
    }

    public function getCategories($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getCategories($asObjects,$filters);
    }

    // ########################################

    public function getAutoGlobalAddingDescriptionTemplateId()
    {
        return (int)$this->getData('auto_global_adding_description_template_id');
    }

    public function getAutoWebsiteAddingDescriptionTemplateId()
    {
        return (int)$this->getData('auto_website_adding_description_template_id');
    }

    // ########################################

    public function getSkuMode()
    {
        return (int)$this->getData('sku_mode');
    }

    public function isSkuProductIdMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_PRODUCT_ID;
    }

    public function isSkuDefaultMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_DEFAULT;
    }

    public function isSkuAttributeMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getSkuSource()
    {
        return array(
            'mode'      => $this->getSkuMode(),
            'attribute' => $this->getData('sku_custom_attribute')
        );
    }

    //-------------------------

    public function getSkuModificationMode()
    {
        return (int)$this->getData('sku_modification_mode');
    }

    public function isSkuModificationModeNone()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_NONE;
    }

    public function isSkuModificationModePrefix()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_PREFIX;
    }

    public function isSkuModificationModePostfix()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_POSTFIX;
    }

    public function isSkuModificationModeTemplate()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_TEMPLATE;
    }

    public function getSkuModificationSource()
    {
        return array(
            'mode'  => $this->getSkuModificationMode(),
            'value' => $this->getData('sku_modification_custom_value')
        );
    }

    //-------------------------

    public function isGenerateSkuModeNo()
    {
        return (int)$this->getData('generate_sku_mode') == self::GENERATE_SKU_MODE_NO;
    }

    public function isGenerateSkuModeYes()
    {
        return (int)$this->getData('generate_sku_mode') == self::GENERATE_SKU_MODE_YES;
    }

    //-------------------------

    public function getGeneralIdMode()
    {
        return (int)$this->getData('general_id_mode');
    }

    public function isGeneralIdNotSetMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_NOT_SET;
    }

    public function isGeneralIdAttributeMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getGeneralIdSource()
    {
        return array(
            'mode'      => $this->getGeneralIdMode(),
            'attribute' => $this->getData('general_id_custom_attribute')
        );
    }

    //-------------------------

    public function getWorldwideIdMode()
    {
        return (int)$this->getData('worldwide_id_mode');
    }

    public function isWorldwideIdNotSetMode()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_NOT_SET;
    }

    public function isWorldwideIdAttributeMode()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getWorldwideIdSource()
    {
        return array(
            'mode'      => $this->getWorldwideIdMode(),
            'attribute' => $this->getData('worldwide_id_custom_attribute')
        );
    }

    //-------------------------

    public function getSearchByMagentoTitleMode()
    {
        return (int)$this->getData('search_by_magento_title_mode');
    }

    public function isSearchByMagentoTitleModeEnabled()
    {
        return $this->getSearchByMagentoTitleMode() == self::SEARCH_BY_MAGENTO_TITLE_MODE_YES;
    }

    //-------------------------

    public function getConditionMode()
    {
        return (int)$this->getData('condition_mode');
    }

    public function isConditionDefaultMode()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_DEFAULT;
    }

    public function isConditionAttributeMode()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_CUSTOM_ATTRIBUTE;
    }

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

        $reflectionClass = new ReflectionClass (__CLASS__);
        $tempConstants = $reflectionClass->getConstants();

        $values = array();
        foreach ($tempConstants as $key => $value) {
            $prefixKey = strtolower(substr($key,0,14));
            if (substr($prefixKey,0,10) != 'condition_' ||
                in_array($prefixKey,array('condition_mode','condition_note'))) {
                continue;
            }
            $values[] = $value;
        }

        $this->setData('cache_condition_values',$values);

        return $values;
    }

    //-------------------------

    public function getConditionNoteMode()
    {
        return (int)$this->getData('condition_note_mode');
    }

    public function isConditionNoteNoneMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_NONE;
    }

    public function isConditionNoteValueMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_CUSTOM_VALUE;
    }

    public function getConditionNoteSource()
    {
        return array(
            'mode'      => $this->getConditionNoteMode(),
            'value'     => $this->getData('condition_note_value')
        );
    }

    public function getConditionNoteAttributes()
    {
        $attributes = array();
        $src = $this->getConditionNoteSource();

        if ($src['mode'] == self::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['value'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //----------------------------------------

    public function getImageMainMode()
    {
        return (int)$this->getData('image_main_mode');
    }

    public function isImageMainModeNone()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_NONE;
    }

    public function isImageMainModeProduct()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_PRODUCT;
    }

    public function isImageMainModeAttribute()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_ATTRIBUTE;
    }

    public function getImageMainSource()
    {
        return array(
            'mode'     => $this->getImageMainMode(),
            'attribute' => $this->getData('image_main_attribute')
        );
    }

    public function getImageMainAttributes()
    {
        $attributes = array();
        $src = $this->getImageMainSource();

        if ($src['mode'] == self::IMAGE_MAIN_MODE_PRODUCT) {
            $attributes[] = 'image';
        } else if ($src['mode'] == self::IMAGE_MAIN_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //----------------------------------------

    public function getGalleryImagesMode()
    {
        return (int)$this->getData('gallery_images_mode');
    }

    public function isGalleryImagesModeNone()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_NONE;
    }

    public function isGalleryImagesModeProduct()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_PRODUCT;
    }

    public function isGalleryImagesModeAttribute()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_ATTRIBUTE;
    }

    public function getGalleryImagesSource()
    {
        return array(
            'mode'      => $this->getGalleryImagesMode(),
            'limit'     => $this->getData('gallery_images_limit'),
            'attribute' => $this->getData('gallery_images_attribute')
        );
    }

    public function getGalleryImagesAttributes()
    {
        $attributes = array();
        $src = $this->getGalleryImagesSource();

        if ($src['mode'] == self::GALLERY_IMAGES_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //----------------------------------------

    public function getHandlingTimeMode()
    {
        return (int)$this->getData('handling_time_mode');
    }

    public function isHandlingTimeNoneMode()
    {
        return $this->getHandlingTimeMode() == self::HANDLING_TIME_MODE_NONE;
    }

    public function isHandlingTimeRecommendedMode()
    {
        return $this->getHandlingTimeMode() == self::HANDLING_TIME_MODE_RECOMMENDED;
    }

    public function isHandlingTimeAttributeMode()
    {
        return $this->getHandlingTimeMode() == self::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getHandlingTimeSource()
    {
        return array(
            'mode'      => $this->getHandlingTimeMode(),
            'value'     => (int)$this->getData('handling_time_value'),
            'attribute' => $this->getData('handling_time_custom_attribute')
        );
    }

    //----------------------------------------

    public function getRestockDateMode()
    {
        return (int)$this->getData('restock_date_mode');
    }

    public function isRestockDateNoneMode()
    {
        return $this->getRestockDateMode() == self::RESTOCK_DATE_MODE_NONE;
    }

    public function isRestockDateValueMode()
    {
        return $this->getRestockDateMode() == self::RESTOCK_DATE_MODE_CUSTOM_VALUE;
    }

    public function isRestockDateAttributeMode()
    {
        return $this->getRestockDateMode() == self::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getRestockDateSource()
    {
        return array(
            'mode'      => $this->getRestockDateMode(),
            'value'     => $this->getData('restock_date_value'),
            'attribute' => $this->getData('restock_date_custom_attribute')
        );
    }

    //----------------------------------------

    public function getGiftWrapMode()
    {
        return $this->getData('gift_wrap_mode');
    }

    public function isGiftWrapModeYes()
    {
        return $this->getGiftWrapMode() == self::GIFT_WRAP_MODE_YES;
    }

    public function isGiftWrapModeNo()
    {
        return $this->getGiftWrapMode() == self::GIFT_WRAP_MODE_NO;
    }

    public function isGiftWrapModeAttribute()
    {
        return $this->getGiftWrapMode() == self::GIFT_WRAP_MODE_ATTRIBUTE;
    }

    public function getGiftWrapSource()
    {
        return array(
            'mode' => $this->getGiftWrapMode(),
            'attribute' => $this->getData('gift_wrap_attribute')
        );
    }

    public function getGiftWrapAttributes()
    {
        $attributes = array();
        $src = $this->getGiftWrapSource();

        if ($src['mode'] == self::GIFT_WRAP_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //----------------------------------------

    public function getGiftMessageMode()
    {
        return $this->getData('gift_message_mode');
    }

    public function isGiftMessageModeYes()
    {
        return $this->getGiftMessageMode() == self::GIFT_MESSAGE_MODE_YES;
    }

    public function isGiftMessageModeNo()
    {
        return $this->getGiftMessageMode() == self::GIFT_MESSAGE_MODE_NO;
    }

    public function isGiftMessageModeAttribute()
    {
        return $this->getGiftMessageMode() == self::GIFT_MESSAGE_MODE_ATTRIBUTE;
    }

    public function getGiftMessageSource()
    {
        return array(
            'mode' => $this->getGiftMessageMode(),
            'attribute' => $this->getData('gift_message_attribute')
        );
    }

    public function getGiftMessageAttributes()
    {
        $attributes = array();
        $src = $this->getGiftMessageSource();

        if ($src['mode'] == self::GIFT_MESSAGE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ########################################

    public function convertPriceFromStoreToMarketplace($price)
    {
        return Mage::getSingleton('M2ePro/Currency')->convertPrice(
            $price,
            $this->getAmazonMarketplace()->getDefaultCurrency(),
            $this->getParentObject()->getStoreId()
        );
    }

    public function addProductFromOther(Ess_M2ePro_Model_Listing_Other $listingOtherProduct,
                                        $checkingMode = false,
                                        $checkHasProduct = true)
    {
        if (!$listingOtherProduct->getProductId()) {
            return false;
        }

        $productId = $listingOtherProduct->getProductId();
        $result = $this->getParentObject()->addProduct($productId, $checkingMode, $checkHasProduct);

        if ($checkingMode) {
            return $result;
        }

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

        $amazonListingProduct->getAmazonItem()
            ->setData('store_id', $this->getParentObject()->getStoreId())
            ->save();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Other $amazonListingOther */
        $amazonListingOther = $listingOtherProduct->getChildObject();

        $dataForUpdate = array(
            'general_id'         => $amazonListingOther->getGeneralId(),
            'sku'                => $amazonListingOther->getSku(),
            'online_price'       => $amazonListingOther->getOnlinePrice(),
            'online_qty'         => $amazonListingOther->getOnlineQty(),
            'is_afn_channel'     => (int)$amazonListingOther->isAfnChannel(),
            'is_isbn_general_id' => (int)$amazonListingOther->isIsbnGeneralId(),
            'status'             => $listingOtherProduct->getStatus(),
            'status_changer'     => $listingOtherProduct->getStatusChanger()
        );

        $listingProduct->addData($dataForUpdate)->save();

        return $listingProduct;
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return array_unique(array_merge(
            $this->getConditionNoteAttributes(),
            $this->getImageMainAttributes(),
            $this->getGalleryImagesAttributes(),
            $this->getGiftWrapAttributes(),
            $this->getGiftMessageAttributes(),
            $this->getSellingFormatTemplate()->getTrackingAttributes()
        ));
    }

    // ########################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @param bool $onlyPhysicalUnits
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*', $onlyPhysicalUnits = false)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('listing_id', $this->getId());

        if ($onlyPhysicalUnits) {
            $listingProductCollection->addFieldToFilter('is_variation_parent', 0);
        }

        if (is_array($columns) && !empty($columns)) {
            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$listingProductCollection->getData() : (array)$listingProductCollection->getItems();
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(
            true, array('id', 'synch_status', 'synch_reasons'), true
        );
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    // ########################################

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

    // ########################################
}