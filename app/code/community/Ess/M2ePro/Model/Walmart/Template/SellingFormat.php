<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Template_SellingFormat getParentObject()
 * @method Ess_M2ePro_Model_Resource_Walmart_Template_SellingFormat getResource()
 */
class Ess_M2ePro_Model_Walmart_Template_SellingFormat extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    const QTY_MODIFICATION_MODE_OFF = 0;
    const QTY_MODIFICATION_MODE_ON = 1;

    const QTY_MIN_POSTED_DEFAULT_VALUE = 1;
    const QTY_MAX_POSTED_DEFAULT_VALUE = 100;

    const PRICE_VARIATION_MODE_PARENT   = 1;
    const PRICE_VARIATION_MODE_CHILDREN = 2;

    const PROMOTIONS_MODE_NO  = 0;
    const PROMOTIONS_MODE_YES = 1;

    const SHIPPING_OVERRIDE_RULE_MODE_NO  = 0;
    const SHIPPING_OVERRIDE_RULE_MODE_YES = 1;

    const LAG_TIME_MODE_RECOMMENDED      = 1;
    const LAG_TIME_MODE_CUSTOM_ATTRIBUTE = 2;

    const WEIGHT_MODE_CUSTOM_VALUE     = 1;
    const WEIGHT_MODE_CUSTOM_ATTRIBUTE = 2;

    const MUST_SHIP_ALONE_MODE_NONE             = 0;
    const MUST_SHIP_ALONE_MODE_YES              = 1;
    const MUST_SHIP_ALONE_MODE_NO               = 2;
    const MUST_SHIP_ALONE_MODE_CUSTOM_ATTRIBUTE = 3;

    const SHIPS_IN_ORIGINAL_PACKAGING_MODE_NONE             = 0;
    const SHIPS_IN_ORIGINAL_PACKAGING_MODE_YES              = 1;
    const SHIPS_IN_ORIGINAL_PACKAGING_MODE_NO               = 2;
    const SHIPS_IN_ORIGINAL_PACKAGING_MODE_CUSTOM_ATTRIBUTE = 3;

    const DATE_NONE       = 0;
    const DATE_VALUE      = 1;
    const DATE_ATTRIBUTE  = 2;

    const ATTRIBUTES_MODE_NONE   = 0;
    const ATTRIBUTES_MODE_CUSTOM = 1;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    protected $_marketplaceModel = null;

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_Source[]
     */
    protected $_sellingFormatSourceModels = array();

    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Template_SellingFormat');
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Walmart_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_selling_format_id', $this->getId())
                            ->getSize();
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLockedMarketplace()
    {
        return $this->isLocked();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        foreach ($this->getPromotions(true) as $promotion) {
            $promotion->deleteInstance();
        }

        foreach ($this->getShippingOverrides(true) as $service) {
            $service->deleteInstance();
        }

        $this->delete();
        $this->_marketplaceModel = null;

        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->_sellingFormatSourceModels[$productId])) {
            return $this->_sellingFormatSourceModels[$productId];
        }

        $this->_sellingFormatSourceModels[$productId] = Mage::getModel('M2ePro/Walmart_Template_SellingFormat_Source');
        $this->_sellingFormatSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->_sellingFormatSourceModels[$productId]->setSellingFormatTemplate($this->getParentObject());

        return $this->_sellingFormatSourceModels[$productId];
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if ($this->_marketplaceModel === null) {
            $this->_marketplaceModel = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->_marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
        $this->_marketplaceModel = $instance;
    }

    //########################################

    /**
     * @param bool $asObjects
     * @return array|Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getPromotions($asObjects = false)
    {
        $collection = $this->_activeRecordFactory->getObjectCollection(
            'Walmart_Template_SellingFormat_Promotion'
        );
        $collection->addFieldToFilter('template_selling_format_id', $this->getId());

        /** @var $service Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion */
        foreach ($collection->getItems() as $service) {
            $service->setSellingFormatTemplate($this);
        }

        if (!$asObjects) {
            $result = $collection->toArray();
            return $result['items'];
        }

        return $collection->getItems();
    }

    /**
     * @param bool $asObjects
     * @return array|Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getShippingOverrides($asObjects = false)
    {
        $collection = $this->_activeRecordFactory->getObjectCollection(
            'Walmart_Template_SellingFormat_ShippingOverride'
        );
        $collection->addFieldToFilter('template_selling_format_id', $this->getId());

        /** @var $shippingOverride Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride */
        foreach ($collection->getItems() as $shippingOverride) {
            $shippingOverride->setSellingFormatTemplate($this);
        }

        if (!$asObjects) {
            $result = $collection->toArray();
            return $result['items'];
        }

        return $collection->getItems();
    }

    //########################################

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getQtyMode()
    {
        return (int)$this->getData('qty_mode');
    }

    /**
     * @return bool
     */
    public function isQtyModeProduct()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isQtyModeNumber()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_NUMBER;
    }

    /**
     * @return bool
     */
    public function isQtyModeAttribute()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE;
    }

    /**
     * @return bool
     */
    public function isQtyModeProductFixed()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED;
    }

    /**
     * @return int
     */
    public function getQtyNumber()
    {
        return (int)$this->getData('qty_custom_value');
    }

    /**
     * @return array
     */
    public function getQtySource()
    {
        return array(
            'mode'      => $this->getQtyMode(),
            'value'     => $this->getQtyNumber(),
            'attribute' => $this->getData('qty_custom_attribute'),
            'qty_modification_mode'     => $this->getQtyModificationMode(),
            'qty_min_posted_value'      => $this->getQtyMinPostedValue(),
            'qty_max_posted_value'      => $this->getQtyMaxPostedValue(),
            'qty_percentage'            => $this->getQtyPercentage()
        );
    }

    /**
     * @return array
     */
    public function getQtyAttributes()
    {
        $attributes = array();
        $src = $this->getQtySource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getQtyPercentage()
    {
        return (int)$this->getData('qty_percentage');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getQtyModificationMode()
    {
        return (int)$this->getData('qty_modification_mode');
    }

    /**
     * @return bool
     */
    public function isQtyModificationModeOn()
    {
        return $this->getQtyModificationMode() == self::QTY_MODIFICATION_MODE_ON;
    }

    /**
     * @return bool
     */
    public function isQtyModificationModeOff()
    {
        return $this->getQtyModificationMode() == self::QTY_MODIFICATION_MODE_OFF;
    }

    /**
     * @return int
     */
    public function getQtyMinPostedValue()
    {
        return (int)$this->getData('qty_min_posted_value');
    }

    /**
     * @return int
     */
    public function getQtyMaxPostedValue()
    {
        return (int)$this->getData('qty_max_posted_value');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceMode()
    {
        return (int)$this->getData('price_mode');
    }

    /**
     * @return bool
     */
    public function isPriceModeProduct()
    {
        return $this->getPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isPriceModeSpecial()
    {
        return $this->getPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isPriceModeAttribute()
    {
        return $this->getPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getPriceCoefficient()
    {
        return $this->getData('price_coefficient');
    }

    /**
     * @return array
     */
    public function getPriceSource()
    {
        return array(
            'mode'        => $this->getPriceMode(),
            'coefficient' => $this->getPriceCoefficient(),
            'attribute'   => $this->getData('price_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getPriceAttributes()
    {
        $attributes = array();
        $src = $this->getPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getPromotionsMode()
    {
        return (int)$this->getData('promotions_mode');
    }

    public function isPromotionsModeNo()
    {
        return $this->getPromotionsMode() == self::PROMOTIONS_MODE_NO;
    }

    public function isPromotionsModeYes()
    {
        return $this->getPromotionsMode() == self::PROMOTIONS_MODE_YES;
    }

    // ---------------------------------------

    public function getShippingOverrideRuleMode()
    {
        return (int)$this->getData('shipping_override_rule_mode');
    }

    public function isShippingOverrideRuleModeNo()
    {
        return $this->getShippingOverrideRuleMode() == self::SHIPPING_OVERRIDE_RULE_MODE_NO;
    }

    public function isShippingOverrideRuleModeYes()
    {
        return $this->getShippingOverrideRuleMode() == self::SHIPPING_OVERRIDE_RULE_MODE_YES;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getSaleTimeStartDateMode()
    {
        return (int)$this->getData('sale_time_start_date_mode');
    }

    /**
     * @return bool
     */
    public function isSaleTimeStartDateModeNone()
    {
        return $this->getSaleTimeStartDateMode() == self::DATE_NONE;
    }

    /**
     * @return bool
     */
    public function isSaleTimeStartDateModeValue()
    {
        return $this->getSaleTimeStartDateMode() == self::DATE_VALUE;
    }

    /**
     * @return bool
     */
    public function isSaleTimeStartDateModeAttribute()
    {
        return $this->getSaleTimeStartDateMode() == self::DATE_ATTRIBUTE;
    }

    public function getSaleTimeStartDateValue()
    {
        return $this->getData('sale_time_start_date_value');
    }

    /**
     * @return array
     */
    public function getSaleTimeStartDateSource()
    {
        return array(
            'mode'        => $this->getSaleTimeStartDateMode(),
            'value'       => $this->getSaleTimeStartDateValue(),
            'attribute'   => $this->getData('sale_time_start_date_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getSaleTimeStartDateAttributes()
    {
        $attributes = array();

        if ($this->isSaleTimeStartDateModeNone()) {
            return $attributes;
        }

        $src = $this->getSaleTimeStartDateSource();

        if ($src['mode'] == self::DATE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getSaleTimeEndDateMode()
    {
        return (int)$this->getData('sale_time_end_date_mode');
    }

    /**
     * @return bool
     */
    public function isSaleTimeEndDateModeNone()
    {
        return $this->getSaleTimeEndDateMode() == self::DATE_NONE;
    }

    /**
     * @return bool
     */
    public function isSaleTimeEndDateModeValue()
    {
        return $this->getSaleTimeEndDateMode() == self::DATE_VALUE;
    }

    /**
     * @return bool
     */
    public function isSaleTimeEndDateModeAttribute()
    {
        return $this->getSaleTimeEndDateMode() == self::DATE_ATTRIBUTE;
    }

    public function getSaleTimeEndDateValue()
    {
        return $this->getData('sale_time_end_date_value');
    }

    /**
     * @return array
     */
    public function getSaleTimeEndDateSource()
    {
        return array(
            'mode'        => $this->getSaleTimeEndDateMode(),
            'value'       => $this->getSaleTimeEndDateValue(),
            'attribute'   => $this->getData('sale_time_end_date_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getSaleTimeEndDateAttributes()
    {
        $attributes = array();

        if ($this->isSaleTimeEndDateModeNone()) {
            return $attributes;
        }

        $src = $this->getSaleTimeEndDateSource();

        if ($src['mode'] == self::DATE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceVariationMode()
    {
        return (int)$this->getData('price_variation_mode');
    }

    /**
     * @return bool
     */
    public function isPriceVariationModeParent()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    /**
     * @return bool
     */
    public function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    // ---------------------------------------

    /**
     * @return float
     */
    public function getPriceVatPercent()
    {
        return (float)$this->getData('price_vat_percent');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getLagTimeMode()
    {
        return (int)$this->getData('lag_time_mode');
    }

    /**
     * @return bool
     */
    public function isLagTimeRecommendedMode()
    {
        return $this->getLagTimeMode() == self::LAG_TIME_MODE_RECOMMENDED;
    }

    /**
     * @return bool
     */
    public function isLagTimeAttributeMode()
    {
        return $this->getLagTimeMode() == self::LAG_TIME_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getLagTimeSource()
    {
        return array(
            'mode'      => $this->getLagTimeMode(),
            'value'     => (int)$this->getData('lag_time_value'),
            'attribute' => $this->getData('lag_time_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getLagTimeAttributes()
    {
        $attributes = array();
        $src = $this->getLagTimeSource();

        if ($src['mode'] == self::LAG_TIME_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getItemWeightMode()
    {
        return (int)$this->getData('item_weight_mode');
    }

    /**
     * @return bool
     */
    public function isItemWeightModeCustomValue()
    {
        return $this->getItemWeightMode() == self::WEIGHT_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isItemWeightModeCustomAttribute()
    {
        return $this->getItemWeightMode() == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getItemWeightSource()
    {
        return array(
            'mode'             => $this->getItemWeightMode(),
            'custom_value'     => $this->getData('item_weight_custom_value'),
            'custom_attribute' => $this->getData('item_weight_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getItemWeightAttributes()
    {
        $attributes = array();
        $src = $this->getItemWeightSource();

        if ($src['mode'] == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getMustShipAloneMode()
    {
        return (int)$this->getData('must_ship_alone_mode');
    }

    /**
     * @return bool
     */
    public function isMustShipAloneModeNone()
    {
        return $this->getMustShipAloneMode() == self::MUST_SHIP_ALONE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isMustShipAloneModeYes()
    {
        return $this->getMustShipAloneMode() == self::MUST_SHIP_ALONE_MODE_YES;
    }

    /**
     * @return bool
     */
    public function isMustShipAloneModeNo()
    {
        return $this->getMustShipAloneMode() == self::MUST_SHIP_ALONE_MODE_NO;
    }

    /**
     * @return bool
     */
    public function isMustShipAloneModeAttribute()
    {
        return $this->getMustShipAloneMode() == self::MUST_SHIP_ALONE_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getMustShipAloneSource()
    {
        return array(
            'mode'      => $this->getMustShipAloneMode(),
            'value'     => $this->getData('must_ship_alone_value'),
            'attribute' => $this->getData('must_ship_alone_custom_attribute'),
        );
    }

    /**
     * @return array
     */
    public function getMustShipAloneAttributes()
    {
        $attributes = array();
        $src = $this->getMustShipAloneSource();

        if ($src['mode'] == self::MUST_SHIP_ALONE_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getShipsInOriginalPackagingModeMode()
    {
        return (int)$this->getData('ships_in_original_packaging_mode');
    }

    /**
     * @return bool
     */
    public function isShipsInOriginalPackagingModeModeNone()
    {
        return $this->getShipsInOriginalPackagingModeMode() == self::SHIPS_IN_ORIGINAL_PACKAGING_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isShipsInOriginalPackagingModeModeYes()
    {
        return $this->getShipsInOriginalPackagingModeMode() == self::SHIPS_IN_ORIGINAL_PACKAGING_MODE_YES;
    }

    /**
     * @return bool
     */
    public function isShipsInOriginalPackagingModeModeNo()
    {
        return $this->getShipsInOriginalPackagingModeMode() == self::SHIPS_IN_ORIGINAL_PACKAGING_MODE_NO;
    }

    /**
     * @return bool
     */
    public function isShipsInOriginalPackagingModeModeAttribute()
    {
        return $this->getShipsInOriginalPackagingModeMode() == self::SHIPS_IN_ORIGINAL_PACKAGING_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getShipsInOriginalPackagingModeSource()
    {
        return array(
            'mode'      => $this->getShipsInOriginalPackagingModeMode(),
            'value'     => $this->getData('ships_in_original_packaging_value'),
            'attribute' => $this->getData('ships_in_original_packaging_custom_attribute'),
        );
    }

    /**
     * @return array
     */
    public function getShipsInOriginalPackagingModeAttributes()
    {
        $attributes = array();
        $src = $this->getShipsInOriginalPackagingModeSource();

        if ($src['mode'] == self::SHIPS_IN_ORIGINAL_PACKAGING_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return bool
     */
    public function usesConvertiblePrices()
    {
        $attributeHelper = Mage::helper('M2ePro/Magento_Attribute');

        $isPriceConvertEnabled = Mage::helper('M2ePro/Module_Configuration')
            ->getMagentoAttributePriceTypeConvertingMode();

        if ($this->isPriceModeProduct() || $this->isPriceModeSpecial()) {
            return true;
        }

        if ($isPriceConvertEnabled && $this->isPriceModeAttribute() &&
            $attributeHelper->isAttributeInputTypePrice($this->getData('price_custom_attribute'))) {
            return true;
        }

        foreach ($this->getPromotions(true) as $promotion) {
            if ($promotion->isPriceModeProduct() || $promotion->isPriceModeSpecial()) {
                return true;
            }

            if ($promotion->isComparisonPriceModeProduct() || $promotion->isComparisonPriceModeSpecial()) {
                return true;
            }

            if ($isPriceConvertEnabled && $promotion->isComparisonPriceModeAttribute() &&
                $attributeHelper->isAttributeInputTypePrice($promotion->getComparisonPriceAttribute())) {
                return true;
            }

            if ($isPriceConvertEnabled && $promotion->isPriceModeAttribute() &&
                $attributeHelper->isAttributeInputTypePrice($promotion->getPriceAttribute())) {
                return true;
            }
        }

        foreach ($this->getShippingOverrides(true) as $service) {
            if ($isPriceConvertEnabled && $service->isCostModeCustomAttribute() &&
                $attributeHelper->isAttributeInputTypePrice($service->getCostAttribute())) {
                return true;
            }
        }

        return false;
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_sellingformat');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_sellingformat');
        return parent::delete();
    }

    //########################################
}
