<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Item
{
    // ########################################

    public function removeDeletedProduct($product, $component = null)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product
                        ? (int)$product->getId() : (int)$product;

        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');
        $existTables = Mage::helper('M2ePro/Magento')->getMySqlTables();

        if (is_null($component)) {
            $components = Mage::helper('M2ePro/Component')->getComponents();
        } else {
            $components = array($component);
        }

        foreach ($components as $component) {
            $itemTable = $resource->getTableName("m2epro_{$component}_item");
            if (!in_array($itemTable, $existTables)) {
                continue;
            }
            $connWrite->delete($itemTable, array('product_id = ?' => $productId));
        }
    }

    // ########################################
}