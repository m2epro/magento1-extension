<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated extends Ess_M2ePro_Model_Component_Abstract
{
    const MEASUREMENT_SYSTEM_ENGLISH = 1;
    const MEASUREMENT_SYSTEM_METRIC  = 2;

    const PACKAGE_SIZE_CUSTOM_VALUE     = 1;
    const PACKAGE_SIZE_CUSTOM_ATTRIBUTE = 2;

    const DIMENSION_NONE               = 0;
    const DIMENSION_CUSTOM_VALUE       = 1;
    const DIMENSION_CUSTOM_ATTRIBUTE   = 2;

    const WEIGHT_NONE                   = 0;
    const WEIGHT_CUSTOM_VALUE           = 1;
    const WEIGHT_CUSTOM_ATTRIBUTE       = 2;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    private $shippingTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated_Source[]
     */
    private $shippingCalculatedSourceModels = array();

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Shipping_Calculated');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->shippingTemplateModel = NULL;
        $temp && $this->shippingCalculatedSourceModels = array();
        return $temp;
    }

    // #######################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        if (is_null($this->shippingTemplateModel)) {
            $this->shippingTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Shipping', $this->getId(), NULL, array('template')
            );
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

    //------------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->shippingCalculatedSourceModels[$productId])) {
            return $this->shippingCalculatedSourceModels[$productId];
        }

        $this->shippingCalculatedSourceModels[$productId] = Mage::getModel(
            'M2ePro/Ebay_Template_Shipping_Calculated_Source'
        );
        $this->shippingCalculatedSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->shippingCalculatedSourceModels[$productId]->setShippingCalculatedTemplate($this);

        return $this->shippingCalculatedSourceModels[$productId];
    }

    // #######################################

    public function getMeasurementSystem()
    {
        return (int)$this->getData('measurement_system');
    }

    //-----------------------------------------

    public function isMeasurementSystemMetric()
    {
        return $this->getMeasurementSystem() == self::MEASUREMENT_SYSTEM_METRIC;
    }

    public function isMeasurementSystemEnglish()
    {
        return $this->getMeasurementSystem() == self::MEASUREMENT_SYSTEM_ENGLISH;
    }

    // #######################################

    public function getPackageSizeSource()
    {
        return array(
            'mode'      => (int)$this->getData('package_size_mode'),
            'value'     => $this->getData('package_size_value'),
            'attribute' => $this->getData('package_size_attribute')
        );
    }

    public function getPackageSizeAttributes()
    {
        $attributes = array();
        $src = $this->getPackageSizeSource();

        if ($src['mode'] == self::PACKAGE_SIZE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //----------------------------------------

    public function getDimensionSource()
    {
        return array(
            'mode' => (int)$this->getData('dimension_mode'),

            'width_value'  => $this->getData('dimension_width_value'),
            'width_attribute'  => $this->getData('dimension_width_attribute'),

            'length_value' => $this->getData('dimension_length_value'),
            'length_attribute' => $this->getData('dimension_length_attribute'),

            'depth_value'  => $this->getData('dimension_depth_value'),
            'depth_attribute'  => $this->getData('dimension_depth_attribute')
        );
    }

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

    //----------------------------------------

    public function getWeightSource()
    {
        return array(
            'mode' => (int)$this->getData('weight_mode'),
            'major' => $this->getData('weight_major'),
            'minor' => $this->getData('weight_minor'),
            'attribute' => $this->getData('weight_attribute')
        );
    }

    public function getWeightAttributes()
    {
        $attributes = array();
        $src = $this->getWeightSource();

        if ($src['mode'] == self::WEIGHT_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // #######################################

    public function getLocalHandlingCost()
    {
        return (float)$this->getData('local_handling_cost');
    }

    public function getInternationalHandlingCost()
    {
        return (float)$this->getData('international_handling_cost');
    }

    // #######################################

    public function getTrackingAttributes()
    {
        return array();
    }

    public function getUsedAttributes()
    {
        return array_unique(array_merge(
            $this->getPackageSizeAttributes(),
            $this->getDimensionAttributes(),
            $this->getWeightAttributes()
        ));
    }

    // #######################################

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

    // #######################################
}