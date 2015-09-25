<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Product_Status
{
    protected $_productAttributes  = array();

    // ########################################

    protected function _getProductAttribute($attribute)
    {
        if (empty($this->_productAttributes[$attribute])) {
            $this->_productAttributes[$attribute] = Mage::getSingleton('catalog/product')
                                                        ->getResource()->getAttribute($attribute);
        }
        return $this->_productAttributes[$attribute];
    }

    protected function _getReadAdapter()
    {
        return Mage::getResourceModel('core/config')->getReadConnection();
    }

    // ########################################

    public function getProductStatus($productIds, $storeId = null)
    {
        $statuses = array();

        $attribute      = $this->_getProductAttribute('status');
        $attributeTable = $attribute->getBackend()->getTable();
        $adapter        = $this->_getReadAdapter();

        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        if ($storeId === null || $storeId == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
            $select = $adapter->select()
                ->from($attributeTable, array('entity_id', 'value'))
                ->where('entity_id IN (?)', $productIds)
                ->where('attribute_id = ?', $attribute->getAttributeId())
                ->where('store_id = ?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);

            $rows = $adapter->fetchPairs($select);
        } else {
            $select = $adapter->select()
                ->from(
                    array('t1' => $attributeTable),
                    array('entity_id', 'IF(t2.value_id>0, t2.value, t1.value) as value'))
                ->joinLeft(
                    array('t2' => $attributeTable),
                    't1.entity_id = t2.entity_id AND t1.attribute_id = t2.attribute_id AND t2.store_id = '.
                        (int)$storeId,
                    array('t1.entity_id')
                )
                ->where('t1.store_id = ?', Mage_Core_Model_App::ADMIN_STORE_ID)
                ->where('t1.attribute_id = ?', $attribute->getAttributeId())
                ->where('t1.entity_id IN(?)', $productIds);
            $rows = $adapter->fetchPairs($select);
        }

        foreach ($productIds as $productId) {
            if (isset($rows[$productId])) {
                $statuses[$productId] = $rows[$productId];
            } else {
                $statuses[$productId] = -1;
            }
        }

        return $statuses;
    }

    // ########################################
}