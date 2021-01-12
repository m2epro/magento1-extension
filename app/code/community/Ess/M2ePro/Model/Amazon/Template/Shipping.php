<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Resource_Amazon_Template_Shipping getResource()
 */
class Ess_M2ePro_Model_Amazon_Template_Shipping extends Ess_M2ePro_Model_Component_Abstract
{
    const TEMPLATE_NAME_VALUE     = 1;
    const TEMPLATE_NAME_ATTRIBUTE = 2;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Shipping_Source[]
     */
    protected $_shippingTemplateSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_Shipping');
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
                            ->addFieldToFilter('template_shipping_id', $this->getId())
                            ->getSize() ||
                (bool)Mage::getModel('M2ePro/Amazon_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter('template_shipping_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Amazon_Template_Shipping_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $id = $magentoProduct->getProductId();

        if (!empty($this->_shippingTemplateSourceModels[$id])) {
            return $this->_shippingTemplateSourceModels[$id];
        }

        $this->_shippingTemplateSourceModels[$id] =
            Mage::getModel('M2ePro/Amazon_Template_Shipping_Source');

        $this->_shippingTemplateSourceModels[$id]->setMagentoProduct($magentoProduct);
        $this->_shippingTemplateSourceModels[$id]->setShippingTemplate($this);

        return $this->_shippingTemplateSourceModels[$id];
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    // ---------------------------------------

    public function getTemplateNameMode()
    {
        return (int)$this->getData('template_name_mode');
    }

    public function isTemplateNameModeValue()
    {
        return $this->getTemplateNameMode() == self::TEMPLATE_NAME_VALUE;
    }

    public function isTemplateNameModeAttribute()
    {
        return $this->getTemplateNameMode() == self::TEMPLATE_NAME_ATTRIBUTE;
    }

    // ---------------------------------------

    public function getTemplateNameValue()
    {
        return $this->getData('template_name_value');
    }

    public function getTemplateNameAttribute()
    {
        return $this->getData('template_name_attribute');
    }

    // ---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    public function getTemplateNameAttributes()
    {
        $attributes = array();

        if ($this->isTemplateNameModeAttribute()) {
            $attributes[] = $this->getTemplateNameAttribute();
        }

        return $attributes;
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('amazon_template_shipping');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('amazon_template_shipping');
        return parent::delete();
    }

    //########################################
}
