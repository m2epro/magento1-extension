<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Template_SellingFormat getParentObject()
 * @method Ess_M2ePro_Model_Resource_Amazon_Template_SellingFormat getResource()
 */
class Ess_M2ePro_Model_Amazon_Template_SellingFormat extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const QTY_MODIFICATION_MODE_OFF = 0;
    const QTY_MODIFICATION_MODE_ON = 1;

    const QTY_MIN_POSTED_DEFAULT_VALUE = 1;
    const QTY_MAX_POSTED_DEFAULT_VALUE = 100;

    const PRICE_VARIATION_MODE_PARENT   = 1;
    const PRICE_VARIATION_MODE_CHILDREN = 2;

    const BUSINESS_DISCOUNTS_MODE_NONE          = 0;
    const BUSINESS_DISCOUNTS_MODE_TIER          = 1;
    const BUSINESS_DISCOUNTS_MODE_CUSTOM_VALUE  = 2;

    const DATE_VALUE      = 0;
    const DATE_ATTRIBUTE  = 1;

    const LIST_PRICE_MODE_NONE = 0;
    const LIST_PRICE_MODE_ATTRIBUTE = 3;

    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_SellingFormat');
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

        return (bool)Mage::getModel('M2ePro/Amazon_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_selling_format_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $businessDiscounts = $this->getBusinessDiscounts(true);
        foreach ($businessDiscounts as $businessDiscount) {
            $businessDiscount->deleteInstance();
        }

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @param bool $asObjects
     * @return array|Ess_M2ePro_Model_Amazon_Template_SellingFormat_BusinessDiscount[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getBusinessDiscounts($asObjects = false)
    {
        $collection = $this->_activeRecordFactory->getObjectCollection(
            'Amazon_Template_SellingFormat_BusinessDiscount'
        );
        $collection->addFieldToFilter('template_selling_format_id', $this->getId());

        /** @var $businessDiscount Ess_M2ePro_Model_Amazon_Template_SellingFormat_BusinessDiscount */
        foreach ($collection->getItems() as $businessDiscount) {
            $businessDiscount->setSellingFormatTemplate($this->getParentObject());
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
     * @return bool
     */
    public function isRegularCustomerAllowed()
    {
        return (bool)$this->getData('is_regular_customer_allowed');
    }

    /**
     * @return bool
     */
    public function isBusinessCustomerAllowed()
    {
        return (bool)$this->getData('is_business_customer_allowed');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getRegularPriceMode()
    {
        return (int)$this->getData('regular_price_mode');
    }

    /**
     * @return bool
     */
    public function isRegularPriceModeProduct()
    {
        return $this->getRegularPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isRegularPriceModeSpecial()
    {
        return $this->getRegularPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isRegularPriceModeAttribute()
    {
        return $this->getRegularPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getRegularPriceCoefficient()
    {
        return $this->getData('regular_price_coefficient');
    }

    /**
     * @return array
     */
    public function getRegularPriceSource()
    {
        return array(
            'mode'        => $this->getRegularPriceMode(),
            'coefficient' => $this->getRegularPriceCoefficient(),
            'attribute'   => $this->getData('regular_price_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getRegularPriceAttributes()
    {
        $attributes = array();
        $src = $this->getRegularPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getRegularMapPriceMode()
    {
        return (int)$this->getData('regular_map_price_mode');
    }

    /**
     * @return bool
     */
    public function isRegularMapPriceModeNone()
    {
        return $this->getRegularMapPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isRegularMapPriceModeProduct()
    {
        return $this->getRegularMapPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isRegularMapPriceModeSpecial()
    {
        return $this->getRegularMapPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isRegularMapPriceModeAttribute()
    {
        return $this->getRegularMapPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getRegularMapPriceSource()
    {
        return array(
            'mode'        => $this->getRegularMapPriceMode(),
            'attribute'   => $this->getData('regular_map_price_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getRegularMapPriceAttributes()
    {
        $attributes = array();
        $src = $this->getRegularMapPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getRegularSalePriceMode()
    {
        return (int)$this->getData('regular_sale_price_mode');
    }

    /**
     * @return bool
     */
    public function isRegularSalePriceModeNone()
    {
        return $this->getRegularSalePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isRegularSalePriceModeProduct()
    {
        return $this->getRegularSalePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isRegularSalePriceModeSpecial()
    {
        return $this->getRegularSalePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isRegularSalePriceModeAttribute()
    {
        return $this->getRegularSalePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getRegularSalePriceCoefficient()
    {
        return $this->getData('regular_sale_price_coefficient');
    }

    /**
     * @return array
     */
    public function getRegularSalePriceSource()
    {
        return array(
            'mode'        => $this->getRegularSalePriceMode(),
            'coefficient' => $this->getRegularSalePriceCoefficient(),
            'attribute'   => $this->getData('regular_sale_price_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getRegularSalePriceAttributes()
    {
        $attributes = array();
        $src = $this->getRegularSalePriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getRegularSalePriceStartDateMode()
    {
        return (int)$this->getData('regular_sale_price_start_date_mode');
    }

    /**
     * @return bool
     */
    public function isRegularSalePriceStartDateModeValue()
    {
        return $this->getRegularSalePriceStartDateMode() == self::DATE_VALUE;
    }

    /**
     * @return bool
     */
    public function isRegularSalePriceStartDateModeAttribute()
    {
        return $this->getRegularSalePriceStartDateMode() == self::DATE_ATTRIBUTE;
    }

    public function getRegularSalePriceStartDateValue()
    {
        return $this->getData('regular_sale_price_start_date_value');
    }

    /**
     * @return array
     */
    public function getRegularSalePriceStartDateSource()
    {
        return array(
            'mode'        => $this->getRegularSalePriceStartDateMode(),
            'value'       => $this->getRegularSalePriceStartDateValue(),
            'attribute'   => $this->getData('regular_sale_price_start_date_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getRegularSalePriceStartDateAttributes()
    {
        $attributes = array();
        $src = $this->getRegularSalePriceStartDateSource();

        if ($src['mode'] == self::DATE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getRegularSalePriceEndDateMode()
    {
        return (int)$this->getData('regular_sale_price_end_date_mode');
    }

    /**
     * @return bool
     */
    public function isRegularSalePriceEndDateModeValue()
    {
        return $this->getRegularSalePriceEndDateMode() == self::DATE_VALUE;
    }

    /**
     * @return bool
     */
    public function isRegularSalePriceEndDateModeAttribute()
    {
        return $this->getRegularSalePriceEndDateMode() == self::DATE_ATTRIBUTE;
    }

    public function getRegularSalePriceEndDateValue()
    {
        return $this->getData('regular_sale_price_end_date_value');
    }

    /**
     * @return array
     */
    public function getRegularSalePriceEndDateSource()
    {
        return array(
            'mode'        => $this->getRegularSalePriceEndDateMode(),
            'value'       => $this->getRegularSalePriceEndDateValue(),
            'attribute'   => $this->getData('regular_sale_price_end_date_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getRegularSalePriceEndDateAttributes()
    {
        $attributes = array();
        $src = $this->getRegularSalePriceEndDateSource();

        if ($src['mode'] == self::DATE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getRegularPriceVariationMode()
    {
        return (int)$this->getData('regular_price_variation_mode');
    }

    /**
     * @return bool
     */
    public function isRegularPriceVariationModeParent()
    {
        return $this->getRegularPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    /**
     * @return bool
     */
    public function isRegularPriceVariationModeChildren()
    {
        return $this->getRegularPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getListPriceMode()
    {
        return (int)$this->getData('regular_list_price_mode');
    }

    /**
     * @return bool
     */
    public function isListPriceModeNone()
    {
        return $this->getListPriceMode() === self::LIST_PRICE_MODE_NONE;
    }

    /**
     * @return string
     */
    public function getListPriceAttribute()
    {
        return (string)$this->getData('regular_list_price_custom_attribute');
    }

    // ---------------------------------------

    /**
     * @return float
     */
    public function getRegularPriceVatPercent()
    {
        return (float)$this->getData('regular_price_vat_percent');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getBusinessPriceMode()
    {
        return (int)$this->getData('business_price_mode');
    }

    /**
     * @return bool
     */
    public function isBusinessPriceModeProduct()
    {
        return $this->getBusinessPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isBusinessPriceModeSpecial()
    {
        return $this->getBusinessPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isBusinessPriceModeAttribute()
    {
        return $this->getRegularPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getBusinessPriceCoefficient()
    {
        return $this->getData('business_price_coefficient');
    }

    /**
     * @return array
     */
    public function getBusinessPriceSource()
    {
        return array(
            'mode'        => $this->getBusinessPriceMode(),
            'coefficient' => $this->getBusinessPriceCoefficient(),
            'attribute'   => $this->getData('business_price_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getBusinessPriceAttributes()
    {
        $attributes = array();
        $src = $this->getBusinessPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getBusinessPriceVariationMode()
    {
        return (int)$this->getData('business_price_variation_mode');
    }

    /**
     * @return bool
     */
    public function isBusinessPriceVariationModeParent()
    {
        return $this->getBusinessPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    /**
     * @return bool
     */
    public function isBusinessPriceVariationModeChildren()
    {
        return $this->getBusinessPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    // ---------------------------------------

    /**
     * @return float
     */
    public function getBusinessPriceVatPercent()
    {
        return (float)$this->getData('business_price_vat_percent');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getBusinessDiscountsMode()
    {
        return (int)$this->getData('business_discounts_mode');
    }

    /**
     * @return bool
     */
    public function isBusinessDiscountsModeNone()
    {
        return $this->getBusinessDiscountsMode() == self::BUSINESS_DISCOUNTS_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isBusinessDiscountsModeTier()
    {
        return $this->getBusinessDiscountsMode() == self::BUSINESS_DISCOUNTS_MODE_TIER;
    }

    /**
     * @return bool
     */
    public function isBusinessDiscountsModeCustomValue()
    {
        return $this->getBusinessDiscountsMode() == self::BUSINESS_DISCOUNTS_MODE_CUSTOM_VALUE;
    }

    // ---------------------------------------

    public function getBusinessDiscountsTierCoefficient()
    {
        return $this->getData('business_discounts_tier_coefficient');
    }

    /**
     * @return int|null
     */
    public function getBusinessDiscountsTierCustomerGroupId()
    {
        return $this->getData('business_discounts_tier_customer_group_id');
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getBusinessDiscountsSource()
    {
        return array(
            'mode'                   => $this->getBusinessDiscountsMode(),
            'tier_customer_group_id' => $this->getBusinessDiscountsTierCustomerGroupId(),
        );
    }

    //########################################

    /**
     * @return bool
     */
    public function usesConvertiblePrices()
    {
        $attributeHelper = Mage::helper('M2ePro/Magento_Attribute');

        $isPriceConvertEnabled = Mage::helper('M2ePro/Module_Configuration')
            ->isEnableMagentoAttributePriceTypeConvertingMode();

        if ($this->isRegularCustomerAllowed()) {
            if ($this->isRegularPriceModeProduct() || $this->isRegularPriceModeSpecial()) {
                return true;
            }

            if ($this->isRegularSalePriceModeProduct() || $this->isRegularSalePriceModeSpecial()) {
                return true;
            }

            if ($this->isRegularMapPriceModeProduct() || $this->isRegularMapPriceModeSpecial()) {
                return true;
            }

            if ($isPriceConvertEnabled) {
                if ($this->isRegularPriceModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('regular_price_custom_attribute'))) {
                    return true;
                }

                if ($this->isRegularSalePriceModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('regular_sale_price_custom_attribute'))
                ) {
                    return true;
                }

                if ($this->isRegularMapPriceModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('regular_map_price_custom_attribute'))) {
                    return true;
                }
            }
        }

        if ($this->isBusinessCustomerAllowed()) {
            if ($this->isBusinessPriceModeProduct() || $this->isBusinessPriceModeSpecial()) {
                return true;
            }

            if ($isPriceConvertEnabled) {
                if ($this->isBusinessPriceModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('business_price_custom_attribute'))) {
                    return true;
                }
            }

            foreach ($this->getBusinessDiscounts(true) as $businessDiscount) {
                if ($businessDiscount->isModeProduct() || $businessDiscount->isModeSpecial()) {
                    return true;
                }

                if ($isPriceConvertEnabled && $businessDiscount->isModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($businessDiscount->getAttribute())) {
                    return true;
                }
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
