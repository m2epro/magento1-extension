<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService
    extends Ess_M2ePro_Model_Component_Abstract
{
    const COST_MODE_FREE             = 0;
    const COST_MODE_CUSTOM_VALUE     = 1;
    const COST_MODE_CUSTOM_ATTRIBUTE = 2;

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService_Source[]
     */
    private $sellingFormatShippingServiceSourceModels = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Template_SellingFormat_ShippingOverrideService');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->sellingFormatTemplateModel = NULL;
        $temp && $this->sellingFormatShippingServiceSourceModels = array();
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
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $id = $magentoProduct->getProductId();

        if (!empty($this->sellingFormatShippingServiceSourceModels[$id])) {
            return $this->sellingFormatShippingServiceSourceModels[$id];
        }

        $this->sellingFormatShippingServiceSourceModels[$id] =
            Mage::getModel('M2ePro/Walmart_Template_SellingFormat_ShippingOverrideService_Source');

        $this->sellingFormatShippingServiceSourceModels[$id]->setMagentoProduct($magentoProduct);
        $this->sellingFormatShippingServiceSourceModels[$id]->setSellingFormatShipingServiceTemplate($this);

        return $this->sellingFormatShippingServiceSourceModels[$id];
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

    public function getRegion()
    {
        return $this->getData('region');
    }

    public function getMethod()
    {
        return $this->getData('method');
    }

    // ---------------------------------------

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
        return $this->getCostMode() === self::COST_MODE_FREE;
    }

    /**
     * @return bool
     */
    public function isCostModeCustomValue()
    {
        return $this->getCostMode() === self::COST_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isCostModeCustomAttribute()
    {
        return $this->getCostMode() === self::COST_MODE_CUSTOM_ATTRIBUTE;
    }

    //########################################

    public function getCostValue()
    {
        return $this->getData('cost_value');
    }

    public function getCostAttribute()
    {
        return $this->getData('cost_attribute');
    }

    //########################################
}