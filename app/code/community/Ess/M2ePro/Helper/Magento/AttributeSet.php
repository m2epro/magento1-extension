<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Magento_AttributeSet extends Ess_M2ePro_Helper_Magento_Abstract
{
    //########################################

    public function getAll($returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeSetsCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->setOrder('attribute_set_name', 'ASC');

        return $this->_convertCollectionToReturnType($attributeSetsCollection, $returnType);
    }

    // ---------------------------------------

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

    // ---------------------------------------

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

    //########################################

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

    //########################################

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

    //########################################

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

    //########################################
}