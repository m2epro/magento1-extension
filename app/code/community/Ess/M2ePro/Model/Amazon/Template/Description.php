<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Template_Description getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Amazon_Template_Description getResource()
 */
class Ess_M2ePro_Model_Amazon_Template_Description extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const WORLDWIDE_ID_MODE_NONE             = 0;
    const WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE = 1;

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

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_Description');
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

    /**
     * @return bool
     */
    public function isLockedForCategoryChange()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product')
            ->addFieldToFilter('template_description_id', $this->getId())
            ->addFieldToFilter('is_general_id_owner', Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES);

        if ($collection->getSize() <= 0) {
            return false;
        }

        $processingLockCollection = Mage::getModel('M2ePro/Processing_Lock')->getCollection();
        $processingLockCollection->addFieldToFilter('model_name', 'M2ePro/Listing_Product');
        $lockedListingProductsIds = $processingLockCollection->getColumnValues('object_id');

        $mysqlIds = implode(',', array_map('intval', $lockedListingProductsIds));
        $notListed = Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;

        $whereConditions = array('(`is_variation_parent` = 1 AND `general_id` IS NOT NULL)');
        if (!empty($mysqlIds)) {
            $whereConditions[] = "(`is_variation_parent` = 0 AND `status` = {$notListed} AND `id` IN ({$mysqlIds}))";
            $whereConditions[] = "(`is_variation_parent` = 1 AND `general_id` IS NULL AND `id` IN ({$mysqlIds}))";
        }

        $collection->getSelect()->where(implode(' OR ', $whereConditions));

        return (bool)$collection->getSize();
    }

    /**
     * @return bool
     */
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

    // ---------------------------------------

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

    // ---------------------------------------

    public function setDefinitionTemplate(Ess_M2ePro_Model_Amazon_Template_Description_Definition $template)
    {
        $this->descriptionDefinitionModel = $template;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Definition
     */
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
                $specific['attributes'] = (array)Mage::helper('M2ePro')->jsonDecode($specific['attributes']);
            }
        }

        return $specifics;
    }

    //########################################

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

    //########################################

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    /**
     * @return bool
     */
    public function isNewAsinAccepted()
    {
        return (bool)$this->getData('is_new_asin_accepted');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getWorldwideIdMode()
    {
        return (int)$this->getData('worldwide_id_mode');
    }

    /**
     * @return bool
     */
    public function isWorldwideIdModeNone()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isWorldwideIdModeCustomAttribute()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getWorldwideIdSource()
    {
        return array(
            'mode'      => $this->getWorldwideIdMode(),
            'attribute' => $this->getData('worldwide_id_custom_attribute')
        );
    }

    /**
     * @return array
     */
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

    //########################################

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

    //########################################
}