<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // ####################################

    public function getStoreId()
    {
        return !is_null($this->getData('store_id')) ? $this->getData('store_id') : Mage_Core_Model_App::ADMIN_STORE_ID;
    }

    public function setStoreId($storeId)
    {
        $this->setData('store_id',$storeId);
        return $this;
    }

    // ####################################

    public function setCollection($collection)
    {
        $this->_prepareCache(clone $collection);
        parent::setCollection($collection);
    }

    protected function _prepareCache($collection)
    {
        $stmt = $collection->getSelect()->query();

        $ids = array();
        foreach ($stmt as $item) {
            $ids = array_merge($ids,array_map('intval',explode('/',$item['path'])));
        }
        $ids = array_unique($ids);

        if (empty($ids)) {
            return;
        }

        /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $collection->getFirstItem()->getResource()->getAttribute('name');

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tableName = Mage_Catalog_Model_Category::ENTITY . '_entity_' . $attribute->getBackendType();

        $dbSelect1 = $connRead
            ->select()
            ->from(Mage::getSingleton('core/resource')->getTableName($tableName), new Zend_Db_Expr('MAX(`store_id`)'))
            ->where("`entity_id` = `ccev`.`entity_id`")
            ->where("`attribute_id` = `ccev`.`attribute_id`")
            ->where("`store_id` = 0 OR `store_id` = ?",$this->getStoreId());

        $dbSelect2 = $connRead
            ->select()
            ->from(
                array('ccev' => Mage::getSingleton('core/resource')->getTableName($tableName)),
                array('name' => 'value','category_id' => 'entity_id')
            )
            ->where('ccev.entity_id IN ('.implode(',',$ids).')')
            ->where('ccev.attribute_id = ?', $attribute->getAttributeId())
            ->where('ccev.entity_type_id = ?', $attribute->getEntityTypeId())
            ->where('ccev.store_id = ('.$dbSelect1->__toString().')');

        $cacheData = array();

        foreach ($connRead->fetchAll($dbSelect2) as $row) {
            $cacheData[$row['category_id']] = $row['name'];
        }
        $this->setData('categories_cache', $cacheData);
    }

    // ####################################

    public function callbackColumnMagentoCategory($value, $row, $column, $isExport)
    {
        $ids = explode('/',$row->getPath());

        $categoriesCache = $this->getData('categories_cache');
        $path = '';
        foreach ($ids as $id) {
            if (!isset($categoriesCache[$id])) {
                continue;
            }
            $path != '' && $path .= ' > ';
            $path .= $categoriesCache[$id];
        }

        return Mage::helper('M2ePro')->escapeHtml($path);
    }

    // ####################################

    public function getMultipleRows($item)
    {
        return false;
    }

    // ####################################
}