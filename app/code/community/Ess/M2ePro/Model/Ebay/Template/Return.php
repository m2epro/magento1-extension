<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Mysql4_Ebay_Template_Return getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Return extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Return');
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN;
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
                            ->addFieldToFilter('template_return_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_return_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter('template_return_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_return_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->marketplaceModel = NULL;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->marketplaceModel = $instance;
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

    /**
     * @return bool
     */
    public function isHolidayEnabled()
    {
        return (bool)$this->getData('holiday_mode');
    }

    public function getShippingCost()
    {
        return $this->getData('shipping_cost');
    }

    public function getRestockingFee()
    {
        return $this->getData('restocking_fee');
    }

    public function getDescription()
    {
        return $this->getData('description');
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
        return array();
    }

    //########################################

    /**
     * @return array
     */
    public function getDefaultSettingsSimpleMode()
    {
        return array(
            'accepted'       => 'ReturnsAccepted',
            'option'         => '',
            'within'         => '',
            'holiday_mode'   => 0,
            'shipping_cost'  => '',
            'restocking_fee' => '',
            'description'    => ''
        );
    }

    /**
     * @return array
     */
    public function getDefaultSettingsAdvancedMode()
    {
        return $this->getDefaultSettingsSimpleMode();
    }

    //########################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $templateManager->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN);

        $listingsProducts = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING_PRODUCT, $this->getId(), $asArrays, $columns
        );

        $listings = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING, $this->getId(), false
        );

        foreach ($listings as $listing) {

            $tempListingsProducts = $listing->getChildObject()
                                            ->getAffectedListingsProductsByTemplate(
                                                Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN,
                                                $asArrays, $columns
                                            );

            foreach ($tempListingsProducts as $listingProduct) {
                if (!isset($listingsProducts[$listingProduct['id']])) {
                    $listingsProducts[$listingProduct['id']] = $listingProduct;
                }
            }
        }

        return $listingsProducts;
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_return');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('ebay_template_return');
        return parent::delete();
    }

    //########################################
}