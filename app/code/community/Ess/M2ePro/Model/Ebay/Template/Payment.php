<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Resource_Ebay_Template_Payment getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Payment extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    protected $_marketplaceModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Payment');
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;
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
                                'template_payment_mode',
                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                            )
                            ->addFieldToFilter('template_payment_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter(
                                'template_payment_mode',
                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                            )
                            ->addFieldToFilter('template_payment_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $service->deleteInstance();
        }

        $this->_marketplaceModel = null;

        $this->delete();
        return true;
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

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Ebay_Template_Payment_Service[]
     */
    public function getServices($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems(
            'Ebay_Template_Payment_Service', 'template_payment_id',
            $asObjects, $filters
        );
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

    /**
     * @return bool
     */
    public function isPayPalEnabled()
    {
        return (bool)$this->getData('pay_pal_mode');
    }

    public function getPayPalEmailAddress()
    {
        return $this->getData('pay_pal_email_address');
    }

    /**
     * @return bool
     */
    public function isPayPalImmediatePaymentEnabled()
    {
        return (bool)$this->getData('pay_pal_immediate_payment');
    }

    //########################################

    /**
     * @return array
     */
    public function getDefaultSettings()
    {
        return array(
            'pay_pal_mode'              => 0,
            'pay_pal_email_address'     => '',
            'pay_pal_immediate_payment' => 0,
            'services'                  => array()
        );
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_payment');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_payment');
        return parent::delete();
    }

    //########################################
}
