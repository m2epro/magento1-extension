<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion extends Ess_M2ePro_Model_Component_Abstract
{
    const START_DATE_MODE_VALUE     = 1;
    const START_DATE_MODE_ATTRIBUTE = 2;

    const END_DATE_MODE_VALUE     = 1;
    const END_DATE_MODE_ATTRIBUTE = 2;

    const PRICE_MODE_PRODUCT   = 1;
    const PRICE_MODE_SPECIAL   = 2;
    const PRICE_MODE_ATTRIBUTE = 3;

    const COMPARISON_PRICE_MODE_PRODUCT   = 1;
    const COMPARISON_PRICE_MODE_SPECIAL   = 2;
    const COMPARISON_PRICE_MODE_ATTRIBUTE = 3;

    const TYPE_REDUCED   = 'reduced';
    const TYPE_CLEARANCE = 'clearance';

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion_Source[]
     */
    private $sellingFormatPromotionSourceModels = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Template_SellingFormat_Promotion');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->sellingFormatTemplateModel = NULL;
        $temp && $this->sellingFormatPromotionSourceModels = array();
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplateModel)) {
            $this->sellingFormatTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Walmart_Template_SellingFormat', $this->getTemplateSellingFormatId(), NULL, array('template')
            );
        }

        return $this->sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Walmart_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Walmart_Template_SellingFormat $instance)
    {
        $this->sellingFormatTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $id = $magentoProduct->getProductId();

        if (!empty($this->sellingFormatPromotionSourceModels[$id])) {
            return $this->sellingFormatPromotionSourceModels[$id];
        }

        $this->sellingFormatPromotionSourceModels[$id] =
            Mage::getModel('M2ePro/Walmart_Template_SellingFormat_Promotion_Source');

        $this->sellingFormatPromotionSourceModels[$id]->setMagentoProduct($magentoProduct);
        $this->sellingFormatPromotionSourceModels[$id]->setSellingFormatPromotion($this);

        return $this->sellingFormatPromotionSourceModels[$id];
    }

    //########################################

    /**
     * @return int
     */
    public function getTemplateSellingFormatId()
    {
        return (int)$this->getData('template_shipping_override_id');
    }

    // ---------------------------------------

    public function getStartDateMode()
    {
        return (int)$this->getData('start_date_mode');
    }

    public function isStartDateModeValue()
    {
        return $this->getStartDateMode() == self::START_DATE_MODE_VALUE;
    }

    public function isStartDateModeAttribute()
    {
        return $this->getStartDateMode() == self::START_DATE_MODE_ATTRIBUTE;
    }

    // ---------------------------------------

    public function getStartDateValue()
    {
        return $this->getData('start_date_value');
    }

    public function getStartDateAttribute()
    {
        return $this->getData('start_date_attribute');
    }

    // ---------------------------------------

    public function getStartDateAttributes()
    {
        $attributes = array();

        if ($this->isStartDateModeAttribute()) {
            $attributes[] = $this->getStartDateAttribute();
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getEndDateMode()
    {
        return (int)$this->getData('end_date_mode');
    }

    public function isEndDateModeValue()
    {
        return $this->getEndDateMode() == self::END_DATE_MODE_VALUE;
    }

    public function isEndDateModeAttribute()
    {
        return $this->getEndDateMode() == self::END_DATE_MODE_ATTRIBUTE;
    }

    // ---------------------------------------

    public function getEndDateValue()
    {
        return $this->getData('end_date_value');
    }

    public function getEndDateAttribute()
    {
        return $this->getData('end_date_attribute');
    }

    // ---------------------------------------

    public function getEndDateAttributes()
    {
        $attributes = array();

        if ($this->isEndDateModeAttribute()) {
            $attributes[] = $this->getEndDateAttribute();
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getPriceMode()
    {
        return (int)$this->getData('price_mode');
    }

    public function isPriceModeProduct()
    {
        return $this->getPriceMode() == self::PRICE_MODE_PRODUCT;
    }

    public function isPriceModeSpecial()
    {
        return $this->getPriceMode() == self::PRICE_MODE_SPECIAL;
    }

    public function isPriceModeAttribute()
    {
        return $this->getPriceMode() == self::PRICE_MODE_ATTRIBUTE;
    }

    // ---------------------------------------

    public function getPriceAttribute()
    {
        return $this->getData('price_attribute');
    }

    public function getPriceCoefficient()
    {
        return $this->getData('price_coefficient');
    }

    // ---------------------------------------

    public function getPriceAttributes()
    {
        $attributes = array();

        if ($this->isPriceModeAttribute()) {
            $attributes[] = $this->getPriceAttribute();
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getPriceSource()
    {
        return array(
            'mode'        => $this->getPriceMode(),
            'coefficient' => $this->getPriceCoefficient(),
            'attribute'   => $this->getPriceAttribute(),
        );
    }

    // ---------------------------------------

    public function getComparisonPriceMode()
    {
        return (int)$this->getData('comparison_price_mode');
    }

    public function isComparisonPriceModeProduct()
    {
        return $this->getComparisonPriceMode() == self::COMPARISON_PRICE_MODE_PRODUCT;
    }

    public function isComparisonPriceModeSpecial()
    {
        return $this->getComparisonPriceMode() == self::COMPARISON_PRICE_MODE_SPECIAL;
    }

    public function isComparisonPriceModeAttribute()
    {
        return $this->getComparisonPriceMode() == self::COMPARISON_PRICE_MODE_ATTRIBUTE;
    }

    // ---------------------------------------

    public function getComparisonPriceAttribute()
    {
        return $this->getData('comparison_price_attribute');
    }

    public function getComparisonPriceCoefficient()
    {
        return $this->getData('comparison_price_coefficient');
    }

    // ---------------------------------------

    public function getComparisonPriceSource()
    {
        return array(
            'mode'        => $this->getComparisonPriceMode(),
            'coefficient' => $this->getComparisonPriceCoefficient(),
            'attribute'   => $this->getComparisonPriceAttribute(),
        );
    }

    // ---------------------------------------

    public function getComparisonPriceAttributes()
    {
        $attributes = array();

        if ($this->isComparisonPriceModeAttribute()) {
            $attributes[] = $this->getComparisonPriceAttribute();
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getType()
    {
        return $this->getData('type');
    }

    public function isTypeReduced()
    {
        return $this->getType() == self::TYPE_REDUCED;
    }

    public function isTypeClearance()
    {
        return $this->getType() == self::TYPE_CLEARANCE;
    }

    //########################################
}