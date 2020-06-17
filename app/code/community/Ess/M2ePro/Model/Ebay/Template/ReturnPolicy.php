<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Resource_Ebay_Template_ReturnPolicy getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_ReturnPolicy extends Ess_M2ePro_Model_Component_Abstract
{
    const RETURNS_ACCEPTED     = 'ReturnsAccepted';
    const RETURNS_NOT_ACCEPTED = 'ReturnsNotAccepted';

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    protected $_marketplaceModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_ReturnPolicy');
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY;
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

        return (bool)Mage::getModel('M2ePro/Ebay_Listing')
                ->getCollection()
                ->addFieldToFilter(
                    'template_return_policy_mode',
                    Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                )
                ->addFieldToFilter('template_return_policy_id', $this->getId())
                ->getSize() ||
            (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                ->getCollection()
                ->addFieldToFilter(
                    'template_return_policy_mode',
                    Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                )
                ->addFieldToFilter('template_return_policy_id', $this->getId())
                ->getSize();
    }

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_marketplaceModel = null;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if ($this->_marketplaceModel === null) {
            $this->_marketplaceModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
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

    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * @return bool
     */
    public function isCustomTemplate()
    {
        return (bool)$this->getData('is_custom_template');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
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

    public function getAccepted()
    {
        return $this->getData('accepted');
    }

    public function getOption()
    {
        return $this->getData('option');
    }

    public function getWithin()
    {
        return $this->getData('within');
    }

    public function getShippingCost()
    {
        return $this->getData('shipping_cost');
    }

    // ---------------------------------------

    public function getInternationalAccepted()
    {
        return $this->getData('international_accepted');
    }

    public function getInternationalOption()
    {
        return $this->getData('international_option');
    }

    public function getInternationalWithin()
    {
        return $this->getData('international_within');
    }

    public function getInternationalShippingCost()
    {
        return $this->getData('international_shipping_cost');
    }

    // ---------------------------------------

    public function getDescription()
    {
        return $this->getData('description');
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_returnpolicy');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_returnpolicy');
        return parent::delete();
    }

    //########################################
}
