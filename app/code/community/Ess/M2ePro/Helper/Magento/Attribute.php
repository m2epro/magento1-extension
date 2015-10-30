<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Magento_Attribute extends Ess_M2ePro_Helper_Magento_Abstract
{
    const PRICE_CODE = 'price';
    const SPECIAL_PRICE_CODE = 'special_price';

    //########################################

    public function getAll()
    {
        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $resultAttributes = array();
        foreach ($attributeCollection->getItems() as $attribute) {
            $resultAttributes[] = array(
                'code' => $attribute['attribute_code'],
                'label' => $attribute['frontend_label']
            );
        }

        return $resultAttributes;
    }

    public function getAllAsObjects()
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC)
            ->getItems();

        return $attributes;
    }

    // ---------------------------------------

    public function getByCode($code, $returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setCodeFilter($code)
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $attributes = $this->_convertCollectionToReturnType($attributeCollection, $returnType);
        if ($returnType != self::RETURN_TYPE_ARRAYS) {
            return $attributes;
        }

        $resultAttributes = array();
        foreach ($attributeCollection->getItems() as $attribute) {
            $resultAttributes[] = array(
                'code' => $attribute['attribute_code'],
                'label' => $attribute['frontend_label']
            );
        }

        return $resultAttributes;
    }

    // ---------------------------------------

    public function getByAttributeSet($attributeSet, $returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeSetId = $this->_getIdFromInput($attributeSet);
        if ($attributeSetId === false) {
            return array();
        }

        return $this->getByAttributeSets(array($attributeSetId), $returnType);
    }

    public function getByAttributeSets(array $attributeSets, $returnType = self::RETURN_TYPE_ARRAYS)
    {
        $attributeSetIds = $this->_getIdsFromInput($attributeSets, 'attribute_set_id');
        if (empty($attributeSetIds)) {
            return array();
        }

        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setAttributeSetFilter($attributeSetIds)
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $attributeCollection->getSelect()->group('entity_attribute.attribute_id');

        $attributes = $this->_convertCollectionToReturnType($attributeCollection, $returnType);
        if ($returnType != self::RETURN_TYPE_ARRAYS) {
            return $attributes;
        }

        $resultAttributes = array();
        foreach ($attributes as $attribute) {
            $resultAttributes[] = array(
                'code' => $attribute['attribute_code'],
                'label' => $attribute['frontend_label']
            );
        }

        return $resultAttributes;
    }

    //########################################

    public function getGeneralFromAttributeSets(array $attributeSets)
    {
        $attributeSetIds = $this->_getIdsFromInput($attributeSets, 'attribute_set_id');
        if (empty($attributeSetIds)) {
            return array();
        }

        $attributes = array();
        $isFirst = true;
        $idsParts = array_chunk($attributeSetIds, 50);
        foreach ($idsParts as $part) {
            $tempAttributes = $this->_getGeneralFromAttributeSets($part);

            if ($isFirst) {
                $attributes = $tempAttributes;
                $isFirst = false;

                continue;
            }

            if (!$isFirst && empty($attributes)) {
                return array();
            }

            $attributes = array_intersect($attributes, $tempAttributes);
        }

        if (empty($attributes)) {
            return array();
        }

        $attributesData = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->addFieldToFilter('main_table.attribute_id', array('in' => $attributes))
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC)
            ->toArray();

        $resultAttributes = array();
        foreach ($attributesData['items'] as $attribute) {
            $resultAttributes[] = array(
                'code' => $attribute['attribute_code'],
                'label' => $attribute['frontend_label'],
            );
        }

        return $resultAttributes;
    }

    public function getGeneralFromAllAttributeSets()
    {
        $allAttributeSets = Mage::helper('M2ePro/Magento_AttributeSet')->getAll(self::RETURN_TYPE_IDS);
        return $this->getGeneralFromAttributeSets($allAttributeSets);
    }

    // ---------------------------------------

    private function _getGeneralFromAttributeSets(array $attributeSetIds)
    {
        if (count($attributeSetIds) > 50) {
            throw new Ess_M2ePro_Model_Exception("Attribute sets must be less then 50");
        }

        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->setInAllAttributeSetsFilter($attributeSetIds)
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        return $attributeCollection->getAllIds();
    }

    // ---------------------------------------

    public function getGeneralFromProducts(array $products)
    {
        $productsAttributeSetIds = Mage::helper('M2ePro/Magento_AttributeSet')->getFromProducts(
            $products, self::RETURN_TYPE_IDS
        );

        return $this->getGeneralFromAttributeSets($productsAttributeSetIds);
    }

    //########################################

    public function getConfigurableByAttributeSets(array $attributeSets)
    {
        if (empty($attributeSets)) {
            return array();
        }

        return $this->getConfigurable($attributeSets);
    }

    public function getAllConfigurable()
    {
        return $this->getConfigurable();
    }

    // ---------------------------------------

    private function getConfigurable(array $attributeSetIds = array())
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $cpTable  = Mage::getSingleton('core/resource')->getTableName('catalog/product');
        $saTable  = Mage::getSingleton('core/resource')->getTableName('catalog/product_super_attribute');
        $aTable   = Mage::getSingleton('core/resource')->getTableName('eav_attribute');

        $select = $connRead->select()
            ->distinct(true)
            ->from(array('p' => $cpTable), null)
            ->join(
                array('sa' => $saTable),
                'p.entity_id = sa.product_id',
                null
            )
            ->join(
                array('a' => $aTable),
                'sa.attribute_id = a.attribute_id',
                array('label' => 'frontend_label', 'code' => 'attribute_code')
            )
            ->where('p.type_id = ?', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);

        if (!empty($attributeSetIds)) {
            $select->where('e.attribute_set_id IN ?', $attributeSetIds);
        }

        return $connRead->fetchAll($select);
    }

    //########################################

    public function getAttributeLabel($attributeCode, $storeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        /** @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);

        if (!$attribute) {
            return $attributeCode;
        }

        $label = $attribute->getStoreLabel($storeId);
        $label == '' && $label = $attribute->getFrontendLabel();

        return $label == '' ? $attributeCode : $label;
    }

    public function getAttributesLabels(array $attributeCodes)
    {
        if (empty($attributeCodes)) {
            return array();
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('eav_attribute');

        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getId();
        $dbSelect = $connRead->select();
        $dbSelect->from($tableName)
            ->where('attribute_code in (\''.implode('\',\'', $attributeCodes).'\')')
            ->where('entity_type_id = ?', $entityTypeId);
        $fetchResult = $connRead->fetchAll($dbSelect);

        $result = array();
        foreach ($fetchResult as $attribute) {
            $result[] = array(
                'label' => $attribute['frontend_label'],
                'code'  => $attribute['attribute_code']
            );
        }

        return $result;
    }

    public function isExistInAttributesArray($attributeCode, array $attributes)
    {
        if ($attributeCode == '') {
            return false;
        }

        foreach ($attributes as $attribute) {
            if ($attribute['code'] == $attributeCode) {
                return true;
            }
        }
        return false;
    }

    public function filterByInputTypes(array $attributes, array $inputTypes)
    {
        if (empty($attributes)) {
            return array();
        }

        if (empty($inputTypes)) {
            return $attributes;
        }

        $attributeCodes = array();
        foreach ($attributes as $attribute) {
            $attributeCodes[] = $attribute['code'];
        }

        $attributeCollection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addFieldToFilter('attribute_code', array('in' => $attributeCodes))
            ->addFieldToFilter('frontend_input', array('in' => $inputTypes))
            ->setOrder('frontend_label', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $filteredAttributes = $attributeCollection->toArray();
        $resultAttributes = array();
        foreach ($filteredAttributes['items'] as $attribute) {
            $resultAttributes[] = array(
                'code' => $attribute['attribute_code'],
                'label' => $attribute['frontend_label'],
            );
        }

        return $resultAttributes;
    }

    //########################################

    public function getSetsFromProductsWhichLacksAttributes(array $attributes, array $productIds)
    {
        if (count($attributes) == 0 || count($productIds) == 0) {
            return array();
        }

        // ---------------------------------------
        $scopeAttributesOptionArray = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromProducts($productIds);
        $scopeAttributes = array();
        foreach ($scopeAttributesOptionArray as $scopeAttributesOption) {
            $scopeAttributes[] = $scopeAttributesOption['code'];
        }
        // ---------------------------------------

        $missingAttributes = array_diff($attributes, $scopeAttributes);

        if (count($missingAttributes) == 0) {
            return array();
        }

        // ---------------------------------------
        $attributesCollection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->addFieldToFilter('attribute_code', array('in' => $missingAttributes))
            ->addSetInfo(true);
        // ---------------------------------------

        // ---------------------------------------
        $attributeSets = Mage::helper('M2ePro/Magento_AttributeSet')
            ->getFromProducts(
                $productIds,
                Ess_M2ePro_Helper_Magento_Abstract::RETURN_TYPE_IDS
            );
        // ---------------------------------------

        $missingAttributesSets = array();

        foreach ($attributesCollection->getItems() as $attribute) {
            foreach ($attributeSets as $setId) {
                if (!$attribute->isInSet($setId)) {
                    $missingAttributesSets[] = $setId;
                }
            }
        }

        return array_unique($missingAttributesSets);
    }

    //########################################
}