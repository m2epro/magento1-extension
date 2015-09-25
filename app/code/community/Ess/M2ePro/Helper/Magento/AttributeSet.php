<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Magento_AttributeSet extends Ess_M2ePro_Helper_Magento_Abstract
{
    // ################################

    public function getAll($returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeSetsCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->setOrder('attribute_set_name', 'ASC');

        return $this->_convertCollectionToReturnType($attributeSetsCollection, $returnType);
    }

    // --------------------------------

    public function getFromProducts($products, $returnType = self::RETURN_TYPE_ARRAYS)
    {
        $productIds = $this->_getIdsFromInput($products, 'product_id');
        if (empty($productIds)) {
            return array();
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('catalog/product');

        $dbSelect = $connRead->select()
            ->from($tableName, 'attribute_set_id')
            ->where('`entity_id` IN ('.implode(',', $productIds).')')
            ->group('attribute_set_id');

        $oldFetchMode = $connRead->getFetchMode();
        $connRead->setFetchMode(Zend_Db::FETCH_NUM);
        $fetchArray = $connRead->fetchAll($dbSelect);
        $connRead->setFetchMode($oldFetchMode);

        return $this->_convertFetchNumArrayToReturnType($fetchArray, $returnType, 'eav/entity_attribute_set');
    }

    // --------------------------------

    public function getContainsAttribute($attribute, $returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeId = $this->_getIdFromInput($attribute);
        if ($attributeId === false) {
            return array();
        }

        return $this->_getContainsAttributeIds(array($attribute), $returnType);
    }

    public function getFullyContainsAttributes(array $attributes, $returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeIds = $this->_getIdsFromInput($attributes, 'attribute_id');
        if (empty($attributeIds)) {
            return array();
        }

        return $this->_getContainsAttributeIds($attributeIds, $returnType, true);
    }

    public function getContainsAttributes(array $attributes, $returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeIds = $this->_getIdsFromInput($attributes, 'attribute_id');
        if (empty($attributeIds)) {
            return array();
        }

        return $this->_getContainsAttributeIds($attributes, $returnType);
    }

    // ################################

    public function getProductsByAttributeSet($attributeSet, $returnType = self::RETURN_TYPE_IDS)
    {
        $attributeSetId = $this->_getIdFromInput($attributeSet);
        if ($attributeSetId === false) {
            return array();
        }

        return $this->getProductsByAttributeSets(array($attributeSetId), $returnType);
    }

    public function getProductsByAttributeSets(array $attributeSets, $returnType = self::RETURN_TYPE_IDS)
    {
        $attributeSetIds = $this->_getIdsFromInput($attributeSets, 'attribute_set_id');
        if (empty($attributeSets)) {
            return array();
        }

        $productsCollection = Mage::getModel('catalog/product')->getCollection();
        $productsCollection->addFieldToFilter('attribute_set_id', array('in' => $attributeSetIds));

        return $this->_convertCollectionToReturnType($productsCollection, $returnType);
    }

    // ################################

    public function isDefault($setId)
    {
        return Mage::getModel('catalog/product')->getDefaultAttributeSetId() == $setId;
    }

    public function getName($setId)
    {
        $set = Mage::getModel('eav/entity_attribute_set')->load($setId);

        if (!$set->getId()) {
            return NULL;
        }

        return $set->getData('attribute_set_name');
    }

    public function getNames(array $setIds)
    {
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection');
        $collection->addFieldToFilter('attribute_set_id', array('in' => $setIds));

        return $collection->getColumnValues('attribute_set_name');
    }

    // ################################

    public function create($attributeSetName, $sourceAttributeSetId = NULL)
    {
        empty($sourceAttributeSetId) &&
            $sourceAttributeSetId = Mage::getModel('catalog/product')->getDefaultAttributeSetId();

        if (!Mage::getModel('eav/entity_attribute_set')->load($sourceAttributeSetId)->getId()){
            return false;
        }

        $entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();

        /** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
        $attributeSet = Mage::getModel('eav/entity_attribute_set')
            ->setEntityTypeId($entityTypeId)
            ->setAttributeSetName($attributeSetName);

        try {
            // check if name is valid
            $attributeSet->validate();
            // copy parameters to new set from source set
            $attributeSet->save();
            $attributeSet->initFromSkeleton($sourceAttributeSetId)->save();
        } catch (Exception $e) {
            return false;
        }

        return (int)$attributeSet->getId();
    }

    // ################################

    public function attributeAdd($attributeId, $attributeSetId, $attributeGroupId = NULL, $sortOrder = '0')
    {
        /** @var $attribute Mage_Eav_Model_Entity_Attribute */
        $attribute = Mage::getModel('eav/entity_attribute')->load($attributeId);
        if (!$attribute->getId()) {
            return false;
        }

        /** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
        $attributeSet = Mage::getModel('eav/entity_attribute_set')->load($attributeSetId);
        if (!$attributeSet->getId()) {
            return false;
        }

        if (!empty($attributeGroupId)) {
            if (!Mage::getModel('eav/entity_attribute_group')->load($attributeGroupId)->getId()) {
                return false;
            }
        } else {
            $attributeGroupId = $attributeSet->getDefaultGroupId();
        }

        $attribute->setAttributeSetId($attributeSet->getId());

        $filteredAttributes = Mage::getModel('eav/entity_attribute')->getResourceCollection()
            ->setAttributeSetFilter($attributeSet->getId())
            ->addFieldToFilter('entity_attribute.attribute_id', $attributeId)
            ->load();

        // attribute is already in set
        if (count($filteredAttributes) > 0) {
            return false;
        }

        try {
            $attribute->setEntityTypeId($attributeSet->getEntityTypeId())
                ->setAttributeSetId($attributeSetId)
                ->setAttributeGroupId($attributeGroupId)
                ->setSortOrder($sortOrder)
                ->save();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    // ################################

    protected function _getContainsAttributeIds(array $attributeIds,
                                                             $returnType = self::RETURN_TYPE_ARRAYS,
                                                             $isFully = false)
    {
        if (empty($attributeIds)) {
            return array();
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('eav/entity_attribute');

        $dbSelect = $connRead->select()
            ->from($tableName, 'attribute_set_id')
            ->where('attribute_id IN ('.implode(',', $attributeIds).')')
            ->where('entity_type_id = ?', Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->group('attribute_set_id');

        if ($isFully) {
            $dbSelect->having('count(*) = ?', count($attributeIds));
        }

        $oldFetchMode = $connRead->getFetchMode();
        $connRead->setFetchMode(Zend_Db::FETCH_NUM);
        $fetchArray = $connRead->fetchAll($dbSelect);
        $connRead->setFetchMode($oldFetchMode);

        return $this->_convertFetchNumArrayToReturnType($fetchArray, $returnType, 'eav/entity_attribute_set');
    }

    // ################################
}