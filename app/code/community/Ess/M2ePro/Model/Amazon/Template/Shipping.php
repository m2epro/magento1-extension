<?php

use Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping as Resource;

/**
 * @method Ess_M2ePro_Model_Resource_Amazon_Dictionary_TemplateShipping getResource()
 */
class Ess_M2ePro_Model_Amazon_Template_Shipping extends Ess_M2ePro_Model_Component_Abstract
{
    public function _construct()
    {
        parent::_construct();

        $this->_init('M2ePro/Amazon_Template_Shipping');
    }

    public function getTitle()
    {
        return $this->getData(Resource::COLUMN_TITLE);
    }

    public function getTemplateId()
    {
        return $this->getData(Resource::COLUMN_TEMPLATE_ID);
    }

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('amazon_template_shipping');

        return parent::save();
    }

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
                         ->getSize()
            || (bool)Mage::getModel('M2ePro/Amazon_Listing_Product')
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

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('amazon_template_shipping');

        return parent::delete();
    }
}
