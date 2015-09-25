<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_Description getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Amazon_Template_Description getResource()
 */
class Ess_M2ePro_Model_Amazon_Template_Description extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const WORLDWIDE_ID_MODE_NONE             = 0;
    const WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    const ITEM_PACKAGE_QUANTITY_MODE_NONE             = 0;
    const ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_VALUE     = 1;
    const ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_ATTRIBUTE = 2;

    const NUMBER_OF_ITEMS_MODE_NONE             = 0;
    const NUMBER_OF_ITEMS_MODE_CUSTOM_VALUE     = 1;
    const NUMBER_OF_ITEMS_MODE_CUSTOM_ATTRIBUTE = 2;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Definition
     */
    private $descriptionDefinitionModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Source[]
     */
    private $descriptionSourceModels = array();

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_Description');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        $collection = Mage::getModel('M2ePro/Amazon_Listing')->getCollection();
        $collection->getSelect()
            ->where("main_table.auto_global_adding_description_template_id = {$this->getId()} OR
                     main_table.auto_website_adding_description_template_id = {$this->getId()}");

        return (bool)Mage::getModel('M2ePro/Amazon_Listing_Product')->getCollection()
                        ->addFieldToFilter('template_description_id', $this->getId())
                        ->getSize() ||
               (bool)Mage::getModel('M2ePro/Amazon_Listing_Auto_Category_Group')->getCollection()
                        ->addFieldToFilter('adding_description_template_id', $this->getId())
                        ->getSize() ||
               (bool)$collection->getSize();
    }

    public function isLockedForCategoryChange()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product')
            ->addFieldToFilter('template_description_id', $this->getId())
            ->addFieldToFilter('is_general_id_owner', Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES);

        if ($collection->getSize() <= 0) {
            return false;
        }

        $lockedCollection = Mage::getModel('M2ePro/LockedObject')->getCollection();
        $lockedCollection->addFieldToFilter('model_name', 'M2ePro/Listing_Product');
        $lockedListingProductsIds = $lockedCollection->getColumnValues('object_id');

        $mysqlIds = implode(',', array_map('intval', $lockedListingProductsIds));
        $statusNotListed = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

        $collection->getSelect()->where(
            "(`is_variation_parent` = 0 AND `status` = {$statusNotListed} AND `id` IN ({$mysqlIds})) OR
             (`is_variation_parent` = 1 AND `general_id` IS NULL AND `id` IN ({$mysqlIds})) OR
             (`is_variation_parent` = 1 AND `general_id` IS NOT NULL)"
        );

        return (bool)$collection->getSize();
    }

    public function isLockedForNewAsinCreation()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product')
            ->addFieldToFilter('template_description_id', $this->getId())
            ->addFieldToFilter('is_general_id_owner', Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES);

        $collection->getSelect()
            ->where("(`is_variation_parent` = 0 AND `status` = ?) OR
                     (`is_variation_parent` = 1)", Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);

        return (bool)$collection->getSize();
    }

    //---------------------------------------

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->getDefinitionTemplate()->deleteInstance();

        foreach ($this->getSpecifics(true) as $specific) {
            $specific->deleteInstance();
        }

        $this->marketplaceModel           = NULL;
        $this->descriptionDefinitionModel = NULL;
        $this->descriptionSourceModels    = array();

        $this->delete();
        return true;
    }

    // ########################################

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

    //---------------------------------------

    public function getDefinitionTemplate()
    {
        if (is_null($this->descriptionDefinitionModel)) {

            $this->descriptionDefinitionModel = Mage::helper('M2ePro')->getCachedObject(
                'Amazon_Template_Description_Definition', $this->getId(), NULL, array('template')
            );

            $this->descriptionDefinitionModel->setDescriptionTemplate($this->getParentObject());
        }

        return $this->descriptionDefinitionModel;
    }

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Amazon_Template_Description_Specific[]
     */
    public function getSpecifics($asObjects = false, array $filters = array())
    {
        $specifics = $this->getRelatedSimpleItems('Amazon_Template_Description_Specific','template_description_id',
                                                  $asObjects, $filters);
        if ($asObjects) {
            /** @var Ess_M2ePro_Model_Amazon_Template_Description_Specific $specific */
            foreach ($specifics as $specific) {
                $specific->setDescriptionTemplate($this->getParentObject());
            }
        }

        if (!$asObjects) {
            foreach ($specifics as &$specific) {
                $specific['attributes'] = (array)json_decode($specific['attributes'], true);
            }
        }

        return $specifics;
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->descriptionSourceModels[$productId])) {
            return $this->descriptionSourceModels[$productId];
        }

        $this->descriptionSourceModels[$productId] = Mage::getModel('M2ePro/Amazon_Template_Description_Source');
        $this->descriptionSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->descriptionSourceModels[$productId]->setDescriptionTemplate($this->getParentObject());

        return $this->descriptionSourceModels[$productId];
    }

    // ########################################

    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    public function isNewAsinAccepted()
    {
        return (bool)$this->getData('is_new_asin_accepted');
    }

    // ---------------------------------------

    public function getWorldwideIdMode()
    {
        return (int)$this->getData('worldwide_id_mode');
    }

    public function isWorldwideIdModeNone()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_NONE;
    }

    public function isWorldwideIdModeCustomAttribute()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getWorldwideIdSource()
    {
        return array(
            'mode'      => $this->getWorldwideIdMode(),
            'attribute' => $this->getData('worldwide_id_custom_attribute')
        );
    }

    public function getWorldwideIdAttributes()
    {
        $attributes = array();
        $src = $this->getWorldwideIdSource();

        if ($src['mode'] == self::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getItemPackageQuantityMode()
    {
        return (int)$this->getData('item_package_quantity_mode');
    }

    public function getItemPackageQuantityCustomValue()
    {
        return $this->getData('item_package_quantity_custom_value');
    }

    public function getItemPackageQuantityCustomAttribute()
    {
        return $this->getData('item_package_quantity_custom_attribute');
    }

    public function isItemPackageQuantityModeNone()
    {
        return $this->getItemPackageQuantityMode() == self::ITEM_PACKAGE_QUANTITY_MODE_NONE;
    }

    public function isItemPackageQuantityModeCustomValue()
    {
        return $this->getItemPackageQuantityMode() == self::ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_VALUE;
    }

    public function isItemPackageQuantityModeCustomAttribute()
    {
        return $this->getItemPackageQuantityMode() == self::ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getItemPackageQuantitySource()
    {
        return array(
            'mode'      => $this->getItemPackageQuantityMode(),
            'value'     => $this->getItemPackageQuantityCustomValue(),
            'attribute' => $this->getItemPackageQuantityCustomAttribute()
        );
    }

    public function getItemPackageQuantityAttributes()
    {
        $attributes = array();
        $src = $this->getItemPackageQuantitySource();

        if ($src['mode'] == self::ITEM_PACKAGE_QUANTITY_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getNumberOfItemsMode()
    {
        return (int)$this->getData('number_of_items_mode');
    }

    public function isNumberOfItemsModeNone()
    {
        return $this->getNumberOfItemsMode() == self::NUMBER_OF_ITEMS_MODE_NONE;
    }

    public function isNumberOfItemsModeCustomValue()
    {
        return $this->getNumberOfItemsMode() == self::NUMBER_OF_ITEMS_MODE_CUSTOM_VALUE;
    }

    public function isNumberOfItemsModeCustomAttribute()
    {
        return $this->getNumberOfItemsMode() == self::NUMBER_OF_ITEMS_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getNumberOfItemsSource()
    {
        return array(
            'mode'      => $this->getNumberOfItemsMode(),
            'value'     => $this->getData('number_of_items_custom_value'),
            'attribute' => $this->getData('number_of_items_custom_attribute')
        );
    }

    public function getNumberOfItemsAttributes()
    {
        $attributes = array();
        $src = $this->getNumberOfItemsSource();

        if ($src['mode'] == self::NUMBER_OF_ITEMS_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getCategoryPath()
    {
        return $this->getData('category_path');
    }

    public function getBrowsenodeId()
    {
        return $this->getData('browsenode_id');
    }

    public function getProductDataNick()
    {
        return $this->getData('product_data_nick');
    }

    // ---------------------------------------

    public function getRegisteredParameter()
    {
        return $this->getData('registered_parameter');
    }

    // ########################################

    public function getTrackingAttributes()
    {
        $attributes = $this->getDefinitionTemplate()->getTrackingAttributes();

        $specifics = $this->getSpecifics(true);
        foreach ($specifics as $specific) {
            $attributes = array_merge($attributes,$specific->getTrackingAttributes());
        }

        return array_unique($attributes);
    }

    public function getUsedAttributes()
    {
        $attributes = $this->getDefinitionTemplate()->getUsedAttributes();

        $specifics = $this->getSpecifics(true);
        foreach ($specifics as $specific) {
            $attributes = array_merge($attributes,$specific->getUsedAttributes());
        }

        return array_unique(array_merge(
            $attributes,
            $this->getWorldwideIdAttributes(),
            $this->getNumberOfItemsAttributes(),
            $this->getItemPackageQuantityAttributes()
        ));
    }

    // ----------------------------------------

    public function getDataSnapshot()
    {
        $data = parent::getDataSnapshot();

        $data['specifics'] = $this->getSpecifics();
        $data['definition'] = $this->getDefinitionTemplate() ? $this->getDefinitionTemplate()->getData() : array();

        foreach ($data['specifics'] as &$specificsData) {
            foreach ($specificsData as &$value) {
                !is_null($value) && !is_array($value) && $value = (string)$value;
            }
        }
        unset($value);

        foreach ($data['definition'] as &$value) {
            !is_null($value) && !is_array($value) && $value = (string)$value;
        }

        return $data;
    }

    // ########################################

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
        $listingProductCollection->addFieldToFilter('template_description_id', $this->getId());

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

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('amazon_template_description');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('amazon_template_description');
        return parent::delete();
    }

    // ########################################
}