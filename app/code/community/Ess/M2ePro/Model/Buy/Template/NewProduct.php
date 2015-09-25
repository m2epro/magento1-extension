<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Template_NewProduct extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Buy_Template_NewProduct_Core
     */
    private $newProductCoreTemplateModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Template_NewProduct');
    }

    // ########################################

    static public function isAllowedUpcExemption()
    {
        return (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/buy/template/new_sku/','upc_exemption'
        );
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::helper('M2ePro/Component_Buy')
                ->getCollection('Listing_Product')
                ->addFieldToFilter('template_new_product_id', $this->getId())
                ->addFieldToFilter('status',Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
                ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        /* @var $writeConnection Varien_Db_Adapter_Pdo_Mysql */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingProductTable = Mage::getSingleton('core/resource')->getTableName('m2epro_buy_listing_product');

        $writeConnection->update(
                                $listingProductTable,
                                array('template_new_product_id' => null),
                                array('template_new_product_id = ?' => $this->getId())
                                );

        foreach ($this->getAttributes(true) as $attribute) {
            $attribute->deleteInstance();
        }

        $this->getCoreTemplate()->deleteInstance();

        $this->delete();

        $this->newProductCoreTemplateModel = NULL;

        return true;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Template_NewProduct_Core
     */
    public function getCoreTemplate()
    {
        if (is_null($this->newProductCoreTemplateModel)) {

            $this->newProductCoreTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Buy_Template_NewProduct_Core', $this->getId(), NULL, array('template')
            );

            $this->newProductCoreTemplateModel->setNewProductTemplate($this);
        }

        return $this->newProductCoreTemplateModel;
    }

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute[]
     */
    public function getAttributes($asObjects = false, array $filters = array())
    {
        $attributes = $this->getRelatedSimpleItems('Buy_Template_NewProduct_Attribute','template_new_product_id',
                                                   $asObjects, $filters);

        if ($asObjects) {
            /** @var Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute $attribute */
            foreach ($attributes as $attribute) {
                $attribute->setNewProductTemplate($this);
            }
        }

        return $attributes;
    }

    // ########################################

    public function getCategoryPath()
    {
        return $this->getData('category_path');
    }

    public function getNodeTitle()
    {
        return $this->getData('node_title');
    }

    public function getCategoryId()
    {
        return $this->getData('category_id');
    }

    // ########################################

    public function map($listingProductIds)
    {
        if (count($listingProductIds) < 0) {
            return false;
        }

        foreach ($listingProductIds as $listingProductId) {
            $listingProductInstance = Mage::helper('M2ePro/Component_Buy')
                    ->getObject('Listing_Product',$listingProductId);

            $generalId = $listingProductInstance->getChildObject()->getData('general_id');

            if (!is_null($generalId)){
                continue;
            }

            $listingProductInstance->getChildObject()->setData('template_new_product_id',$this->getId())->save();
        }

        return true;
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('buy_template_newproduct');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('buy_template_newproduct');
        return parent::delete();
    }

    // ########################################
}