<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride
    extends Ess_M2ePro_Model_Component_Abstract
{
    const COST_MODE_FREE             = 0;
    const COST_MODE_CUSTOM_VALUE     = 1;
    const COST_MODE_CUSTOM_ATTRIBUTE = 2;

    const IS_SHIPPING_ALLOWED_REMOVE          = 0;
    const IS_SHIPPING_ALLOWED_ADD_OR_OVERRIDE = 1;

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_SellingFormat
     */
    protected $_sellingFormatTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride_Source[]
     */
    protected $_sellingFormatShippingOverrideSourceModels = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Template_SellingFormat_ShippingOverride');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_sellingFormatTemplateModel = null;
        $temp && $this->_sellingFormatShippingOverrideSourceModels = array();
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if ($this->_sellingFormatTemplateModel === null) {
            $this->_sellingFormatTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Walmart_Template_SellingFormat', $this->getTemplateSellingFormatId(), null, array('template')
            );
        }

        return $this->_sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Walmart_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Walmart_Template_SellingFormat $instance)
    {
        $this->_sellingFormatTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $id = $magentoProduct->getProductId();

        if (!empty($this->_sellingFormatShippingOverrideSourceModels[$id])) {
            return $this->_sellingFormatShippingOverrideSourceModels[$id];
        }

        $this->_sellingFormatShippingOverrideSourceModels[$id] =
            Mage::getModel('M2ePro/Walmart_Template_SellingFormat_ShippingOverride_Source');

        $this->_sellingFormatShippingOverrideSourceModels[$id]->setMagentoProduct($magentoProduct);
        $this->_sellingFormatShippingOverrideSourceModels[$id]->setSellingFormatShipingOverrideTemplate($this);

        return $this->_sellingFormatShippingOverrideSourceModels[$id];
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
    public function getIsShippingAllowed()
    {
        return (int)$this->getData('is_shipping_allowed');
    }

    /**
     * @return bool
     */
    public function isShippingAllowedAddOrOverride()
    {
        return $this->getIsShippingAllowed() === self::IS_SHIPPING_ALLOWED_ADD_OR_OVERRIDE;
    }

    /**
     * @return bool
     */
    public function isShippingAllowedRemove()
    {
        return $this->getIsShippingAllowed() === self::IS_SHIPPING_ALLOWED_REMOVE;
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