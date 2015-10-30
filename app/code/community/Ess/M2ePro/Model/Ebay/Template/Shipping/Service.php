<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Service extends Ess_M2ePro_Model_Component_Abstract
{
    const SHIPPING_TYPE_LOCAL         = 0;
    const SHIPPING_TYPE_INTERNATIONAL = 1;

    const COST_MODE_FREE             = 0;
    const COST_MODE_CUSTOM_VALUE     = 1;
    const COST_MODE_CUSTOM_ATTRIBUTE = 2;
    const COST_MODE_CALCULATED       = 3;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    private $shippingTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping_Service_Source[]
     */
    private $shippingServiceSourceModels = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Shipping_Service');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->shippingTemplateModel = NULL;
        $temp && $this->shippingServiceSourceModels = array();
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        if (is_null($this->shippingTemplateModel)) {
            $this->shippingTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Shipping', $this->getTemplateShippingId(), NULL, array('template')
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

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Service_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->shippingServiceSourceModels[$productId])) {
            return $this->shippingServiceSourceModels[$productId];
        }

        $this->shippingServiceSourceModels[$productId] = Mage::getModel('M2ePro/Ebay_Template_Shipping_Service_Source');
        $this->shippingServiceSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->shippingServiceSourceModels[$productId]->setShippingServiceTemplate($this);

        return $this->shippingServiceSourceModels[$productId];
    }

    //########################################

    /**
     * @return int
     */
    public function getTemplateShippingId()
    {
        return (int)$this->getData('template_shipping_id');
    }

    /**
     * @return array
     */
    public function getLocations()
    {
        return json_decode($this->getData('locations'),true);
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return (int)$this->getData('priority');
    }

    //########################################

    /**
     * @return int
     */
    public function getShippingType()
    {
        return (int)$this->getData('shipping_type');
    }

    public function getShippingValue()
    {
        return $this->getData('shipping_value');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isShippingTypeLocal()
    {
        return $this->getShippingType() == self::SHIPPING_TYPE_LOCAL;
    }

    /**
     * @return bool
     */
    public function isShippingTypeInternational()
    {
        return $this->getShippingType() == self::SHIPPING_TYPE_INTERNATIONAL;
    }

    //########################################

    /**
     * @return int
     */
    public function getCostMode()
    {
        return (int)$this->getData('cost_mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isCostModeFree()
    {
        return $this->getCostMode() == self::COST_MODE_FREE;
    }

    /**
     * @return bool
     */
    public function isCostModeCustomValue()
    {
        return $this->getCostMode() == self::COST_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isCostModeCustomAttribute()
    {
        return $this->getCostMode() == self::COST_MODE_CUSTOM_ATTRIBUTE;
    }

    //########################################

    public function getCostValue()
    {
        return $this->getData('cost_value');
    }

    public function getCostAdditionalValue()
    {
        return $this->getData('cost_additional_value');
    }

    public function getCostSurchargeValue()
    {
        return $this->getData('cost_surcharge_value');
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getCostAttributes()
    {
        $attributes = array();

        if ($this->isCostModeCustomAttribute()) {
            $attributes[] = $this->getCostValue();
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function getCostAdditionalAttributes()
    {
        $attributes = array();

        if ($this->isCostModeCustomAttribute()) {
            $attributes[] = $this->getCostAdditionalValue();
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function getCostSurchargeAttributes()
    {
        $attributes = array();

        if ($this->isCostModeCustomAttribute()) {
            $attributes[] = $this->getCostSurchargeValue();
        }

        return $attributes;
    }

    //########################################

    /**
     * @return array
     */
    public function getTrackingAttributes()
    {
        return array();
    }

    /**
     * @return array
     */
    public function getUsedAttributes()
    {
        return array_unique(array_merge(
            $this->getCostAttributes(),
            $this->getCostAdditionalAttributes(),
            $this->getCostSurchargeAttributes()
        ));
    }

    //########################################
}