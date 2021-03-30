<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated extends Ess_M2ePro_Model_Component_Abstract
{
    const MEASUREMENT_SYSTEM_ENGLISH = 1;
    const MEASUREMENT_SYSTEM_METRIC  = 2;

    const PACKAGE_SIZE_NONE             = 0;
    const PACKAGE_SIZE_CUSTOM_VALUE     = 1;
    const PACKAGE_SIZE_CUSTOM_ATTRIBUTE = 2;

    const DIMENSION_NONE             = 0;
    const DIMENSION_CUSTOM_VALUE     = 1;
    const DIMENSION_CUSTOM_ATTRIBUTE = 2;

    const WEIGHT_NONE             = 0;
    const WEIGHT_CUSTOM_VALUE     = 1;
    const WEIGHT_CUSTOM_ATTRIBUTE = 2;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    protected $_shippingTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated_Source[]
     */
    protected $_shippingCalculatedSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Shipping_Calculated');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_shippingTemplateModel = null;
        $temp && $this->_shippingCalculatedSourceModels = array();

        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        if ($this->_shippingTemplateModel === null) {
            $this->_shippingTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Shipping',
                $this->getId(),
                null,
                array('template')
            );
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
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->_shippingCalculatedSourceModels[$productId])) {
            return $this->_shippingCalculatedSourceModels[$productId];
        }

        $this->_shippingCalculatedSourceModels[$productId] = Mage::getModel(
            'M2ePro/Ebay_Template_Shipping_Calculated_Source'
        );
        $this->_shippingCalculatedSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->_shippingCalculatedSourceModels[$productId]->setShippingCalculatedTemplate($this);

        return $this->_shippingCalculatedSourceModels[$productId];
    }

    //########################################

    /**
     * @return int
     */
    public function getMeasurementSystem()
    {
        return (int)$this->getData('measurement_system');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMeasurementSystemMetric()
    {
        return $this->getMeasurementSystem() == self::MEASUREMENT_SYSTEM_METRIC;
    }

    /**
     * @return bool
     */
    public function isMeasurementSystemEnglish()
    {
        return $this->getMeasurementSystem() == self::MEASUREMENT_SYSTEM_ENGLISH;
    }

    //########################################

    /**
     * @return bool
     */
    public function isPackageSizeSet()
    {
        return (int)$this->getData('package_size_mode') !== self::PACKAGE_SIZE_NONE;
    }

    /**
     * @return array
     */
    public function getPackageSizeSource()
    {
        return array(
            'mode'      => (int)$this->getData('package_size_mode'),
            'value'     => $this->getData('package_size_value'),
            'attribute' => $this->getData('package_size_attribute')
        );
    }

    /**
     * @return array
     */
    public function getPackageSizeAttributes()
    {
        $attributes = array();
        $src = $this->getPackageSizeSource();

        if ($src['mode'] == self::PACKAGE_SIZE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isDimensionSet()
    {
        return (int)$this->getData('dimension_mode') !== self::DIMENSION_NONE;
    }

    /**
     * @return array
     */
    public function getDimensionSource()
    {
        return array(
            'mode' => (int)$this->getData('dimension_mode'),

            'width_value'     => $this->getData('dimension_width_value'),
            'width_attribute' => $this->getData('dimension_width_attribute'),

            'length_value'     => $this->getData('dimension_length_value'),
            'length_attribute' => $this->getData('dimension_length_attribute'),

            'depth_value'     => $this->getData('dimension_depth_value'),
            'depth_attribute' => $this->getData('dimension_depth_attribute')
        );
    }

    /**
     * @return array
     */
    public function getDimensionAttributes()
    {
        $attributes = array();
        $src = $this->getDimensionSource();

        if ($src['mode'] == self::DIMENSION_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['width_attribute'];
            $attributes[] = $src['length_attribute'];
            $attributes[] = $src['depth_attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isWeightSet()
    {
        return (int)$this->getData('weight_mode') !== self::WEIGHT_NONE;
    }

    /**
     * @return array
     */
    public function getWeightSource()
    {
        return array(
            'mode'      => (int)$this->getData('weight_mode'),
            'major'     => $this->getData('weight_major'),
            'minor'     => $this->getData('weight_minor'),
            'attribute' => $this->getData('weight_attribute')
        );
    }

    /**
     * @return array
     */
    public function getWeightAttributes()
    {
        $attributes = array();
        $src = $this->getWeightSource();

        if ($src['mode'] == self::WEIGHT_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return float
     */
    public function getLocalHandlingCost()
    {
        return (float)$this->getData('local_handling_cost');
    }

    /**
     * @return float
     */
    public function getInternationalHandlingCost()
    {
        return (float)$this->getData('international_handling_cost');
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_shipping_calculated');

        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_shipping_calculated');

        return parent::delete();
    }

    //########################################
}
