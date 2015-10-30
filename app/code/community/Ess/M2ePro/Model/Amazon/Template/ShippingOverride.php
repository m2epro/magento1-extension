<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Mysql4_Amazon_Template_ShippingOverride getResource()
 */
class Ess_M2ePro_Model_Amazon_Template_ShippingOverride extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_ShippingOverride');
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

        return (bool)Mage::getModel('M2ePro/Amazon_Listing_Product')
            ->getCollection()
            ->addFieldToFilter('template_shipping_override_id', $this->getId())
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

        $this->marketplaceModel = NULL;

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
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

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Abstract[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getServices($asObjects = false, array $filters = array())
    {
        $services = $this->getRelatedSimpleItems('Amazon_Template_ShippingOverride_Service',
                                                 'template_shipping_override_id', $asObjects, $filters);

        if ($asObjects) {
            /** @var $service Ess_M2ePro_Model_Amazon_Template_ShippingOverride_Service */
            foreach ($services as $service) {
                $service->setShippingOverrideTemplate($this);
            }
        }

        return $services;
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
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
     * @return array
     */
    public function getTrackingAttributes()
    {
        $attributes = array();

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $attributes = array_merge($attributes,$service->getTrackingAttributes());
        }

        return array_unique($attributes);
    }

    /**
     * @return array
     */
    public function getUsedAttributes()
    {
        $attributes = array();

        $services = $this->getServices(true);
        foreach ($services as $service) {
            $attributes = array_merge($attributes,$service->getUsedAttributes());
        }

        return array_unique($attributes);
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getDataSnapshot()
    {
        $data = parent::getDataSnapshot();

        $data['services'] = $this->getServices();

        foreach ($data['services'] as &$serviceData) {
            foreach ($serviceData as &$value) {
                !is_null($value) && !is_array($value) && $value = (string)$value;
            }
        }
        unset($value);

        return $data;
    }

    //########################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @param bool $onlyPhysicalUnits
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*', $onlyPhysicalUnits = false)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('template_shipping_override_id', $this->getId());

        if ($onlyPhysicalUnits) {
            $listingProductCollection->addFieldToFilter('is_variation_parent', 0);
        }

        if (is_array($columns) && !empty($columns)) {
            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$listingProductCollection->getData() : (array)$listingProductCollection->getItems();
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id'), true);
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('amazon_template_shippingoverride');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('amazon_template_shippingoverride');
        return parent::delete();
    }

    //########################################
}