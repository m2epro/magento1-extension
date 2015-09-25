<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Buy_Listing getResource()
 */
class Ess_M2ePro_Model_Buy_Listing extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
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

    const GENERAL_ID_MODE_NOT_SET       = 0;
    const GENERAL_ID_MODE_GENERAL_ID    = 1;
    const GENERAL_ID_MODE_ISBN          = 2;
    const GENERAL_ID_MODE_WORLDWIDE     = 3;
    const GENERAL_ID_MODE_SELLER_SKU    = 4;

    const SEARCH_BY_MAGENTO_TITLE_MODE_NONE = 0;
    const SEARCH_BY_MAGENTO_TITLE_MODE_YES  = 1;

    const CONDITION_MODE_DEFAULT          = 1;
    const CONDITION_MODE_CUSTOM_ATTRIBUTE = 2;

    const CONDITION_NEW                    = 1;
    const CONDITION_USED_LIKE_NEW          = 2;
    const CONDITION_USED_VERY_GOOD         = 3;
    const CONDITION_USED_GOOD              = 4;
    const CONDITION_USED_ACCEPTABLE        = 5;
    const CONDITION_REFURBISHED            = 10;

    const CONDITION_NOTE_MODE_NONE             = 3;
    const CONDITION_NOTE_MODE_CUSTOM_VALUE     = 1;

    const SHIPPING_MODE_DISABLED           = 1;
    const SHIPPING_MODE_FREE               = 2;
    const SHIPPING_MODE_DEFAULT            = 3;
    const SHIPPING_MODE_VALUE              = 4;
    const SHIPPING_MODE_CUSTOM_ATTRIBUTE   = 5;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    private $synchronizationTemplateModel = NULL;

    /** @var Ess_M2ePro_Model_Buy_Listing_Source[] */
    private $listingSourceModels = array();

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Listing');
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
     * @return Ess_M2ePro_Model_Buy_Listing_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->listingSourceModels[$productId])) {
            return $this->listingSourceModels[$productId];
        }

        $this->listingSourceModels[$productId] = Mage::getModel('M2ePro/Buy_Listing_Source');
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
     * @return Ess_M2ePro_Model_Buy_Account
     */
    public function getBuyAccount()
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
     * @return Ess_M2ePro_Model_Buy_Marketplace
     */
    public function getBuyMarketplace()
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
            $this->sellingFormatTemplateModel = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
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
            $this->synchronizationTemplateModel = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
                'Template_Synchronization',$this->getData('template_synchronization_id'),NULL,array('template')
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
     * @return Ess_M2ePro_Model_Buy_Template_SellingFormat
     */
    public function getBuySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_Synchronization
     */
    public function getBuySynchronizationTemplate()
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

    public function isGeneralIdWorldwideMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_WORLDWIDE;
    }

    public function isGeneralIdGeneralIdMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_GENERAL_ID;
    }

    public function isGeneralIdSellerSkuMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_SELLER_SKU;
    }

    public function isGeneralIdIsbnMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_ISBN;
    }

    public function getGeneralIdSource()
    {
        return array(
            'mode'      => $this->getGeneralIdMode(),
            'attribute' => $this->getData('general_id_custom_attribute')
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
            'value'     => (int)$this->getData('condition_value'),
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

    // ########################################

    public function getShippingStandardMode()
    {
        return (int)$this->getData('shipping_standard_mode');
    }

    public function isShippingStandardFreeMode()
    {
        return $this->getShippingStandardMode() == self::SHIPPING_MODE_FREE;
    }

    public function isShippingStandardDefaultMode()
    {
        return $this->getShippingStandardMode() == self::SHIPPING_MODE_DEFAULT;
    }

    public function isShippingStandardValueMode()
    {
        return $this->getShippingStandardMode() == self::SHIPPING_MODE_VALUE;
    }

    public function isShippingStandardCustomAttributeMode()
    {
        return $this->getShippingStandardMode() == self::SHIPPING_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingStandardModeSource()
    {
        return array(
            'mode'      => $this->getShippingStandardMode(),
            'value'     => (float)$this->getData('shipping_standard_value'),
            'attribute' => $this->getData('shipping_standard_custom_attribute')
        );
    }

    //----------------------------------------

    public function getShippingExpeditedMode()
    {
        return (int)$this->getData('shipping_expedited_mode');
    }

    public function isShippingExpeditedDisabledMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_DISABLED;
    }

    public function isShippingExpeditedFreeMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_FREE;
    }

    public function isShippingExpeditedDefaultMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_DEFAULT;
    }

    public function isShippingExpeditedValueMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_VALUE;
    }

    public function isShippingExpeditedCustomAttributeMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingExpeditedModeSource()
    {
        return array(
            'mode'      => $this->getShippingExpeditedMode(),
            'value'     => (float)$this->getData('shipping_expedited_value'),
            'attribute' => $this->getData('shipping_expedited_custom_attribute')
        );
    }

    //----------------------------------------

    public function getShippingOneDayMode()
    {
        return (int)$this->getData('shipping_one_day_mode');
    }

    public function isShippingOneDayDisabledMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_DISABLED;
    }

    public function isShippingOneDayFreeMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_FREE;
    }

    public function isShippingOneDayDefaultMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_DEFAULT;
    }

    public function isShippingOneDayValueMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_VALUE;
    }

    public function isShippingOneDayCustomAttributeMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingOneDayModeSource()
    {
        return array(
            'mode'      => $this->getShippingOneDayMode(),
            'value'     => (float)$this->getData('shipping_one_day_value'),
            'attribute' => $this->getData('shipping_one_day_custom_attribute')
        );
    }

    //----------------------------------------

    public function getShippingTwoDayMode()
    {
        return (int)$this->getData('shipping_two_day_mode');
    }

    public function isShippingTwoDayDisabledMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_DISABLED;
    }

    public function isShippingTwoDayFreeMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_FREE;
    }

    public function isShippingTwoDayDefaultMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_DEFAULT;
    }

    public function isShippingTwoDayValueMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_VALUE;
    }

    public function isShippingTwoDayCustomAttributeMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingTwoDayModeSource()
    {
        return array(
            'mode'      => $this->getShippingTwoDayMode(),
            'value'     => (float)$this->getData('shipping_two_day_value'),
            'attribute' => $this->getData('shipping_two_day_custom_attribute')
        );
    }

    // ########################################

    public function convertPriceFromStoreToMarketplace($price)
    {
        return Mage::getSingleton('M2ePro/Currency')->convertPrice(
            $price,
            Ess_M2ePro_Helper_Component_Buy::DEFAULT_CURRENCY,
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

        $listingProduct->getChildObject()
                       ->getBuyItem()
                       ->setData('store_id',$this->getParentObject()->getStoreId())
                       ->save();

        $dataForUpdate = array(
            'general_id' => $listingOtherProduct->getChildObject()->getGeneralId(),
            'sku' => $listingOtherProduct->getChildObject()->getSku(),
            'online_price' => $listingOtherProduct->getChildObject()->getOnlinePrice(),
            'online_qty' => $listingOtherProduct->getChildObject()->getOnlineQty(),
            'condition' => $listingOtherProduct->getChildObject()->getCondition(),
            'condition_note' => $listingOtherProduct->getChildObject()->getConditionNote(),
            'shipping_standard_rate'=>$listingOtherProduct->getChildObject()->getShippingStandardRate(),
            'shipping_expedited_mode'=>$listingOtherProduct->getChildObject()->getShippingExpeditedMode(),
            'shipping_expedited_rate'=>$listingOtherProduct->getChildObject()->getShippingExpeditedRate(),
            'status' => $listingOtherProduct->getStatus(),
            'status_changer' => $listingOtherProduct->getStatusChanger()
        );

        $listingProduct->addData($dataForUpdate)->save();

        return $listingProduct;
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return $this->getSellingFormatTemplate()->getTrackingAttributes();
    }

    // ########################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('listing_id', $this->getId());

        if (is_array($columns) && !empty($columns)) {
            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$listingProductCollection->getData() : (array)$listingProductCollection->getItems();
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id', 'synch_status', 'synch_reasons'));
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