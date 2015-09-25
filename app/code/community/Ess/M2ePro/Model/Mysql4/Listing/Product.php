<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Listing_Product
    extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Product', 'id');
    }

    // ########################################

    public function getProductIds(array $listingProductIds)
    {
        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(array('lp' => $this->getMainTable()))
                       ->reset(Zend_Db_Select::COLUMNS)
                       ->columns(array('product_id'))
                       ->where('id IN (?)', $listingProductIds);

        return $select->query()->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getItemsByProductId($productId, array $filters = array())
    {
        $cacheKey   = __METHOD__.$productId.sha1(json_encode($filters));
        $cacheValue = Mage::helper('M2ePro/Data_Cache_Session')->getValue($cacheKey);

        if (!is_null($cacheValue)) {
            return $cacheValue;
        }

        $simpleProductsSelect = $this->_getReadAdapter()
            ->select()
            ->from(
                $this->getMainTable(),
                array('id','component_mode')
            )
            ->where("`product_id` = ?",(int)$productId);

        if (!empty($filters)) {
            foreach ($filters as $column => $value) {
                $simpleProductsSelect->where('`'.$column.'` = ?', $value);
            }
        }

        $variationTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable();
        $optionTable    = Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable();

        $variationsProductsSelect = $this->_getReadAdapter()
            ->select()
            ->from(
                array('lp' => $this->getMainTable()),
                array('id','component_mode')
            )
            ->join(
                array('lpv' => $variationTable),
                '`lp`.`id` = `lpv`.`listing_product_id`',
                array()
            )
            ->join(
                array('lpvo' => $optionTable),
                '`lpv`.`id` = `lpvo`.`listing_product_variation_id`',
                array()
            )
            ->where("`lpvo`.`product_id` = ?",(int)$productId);

        if (!empty($filters)) {
            foreach ($filters as $column => $value) {
                $variationsProductsSelect->where('`lp`.`'.$column.'` = ?', $value);
            }
        }

        $unionSelect = $this->_getReadAdapter()->select()->union(array(
            $simpleProductsSelect,
            $variationsProductsSelect
        ));

        $result = array();

        foreach ($unionSelect->query()->fetchAll() as $item) {

            $result[] = Mage::helper('M2ePro/Component')->getComponentObject(
                $item['component_mode'], 'Listing_Product', (int)$item['id']
            );
        }

        Mage::helper('M2ePro/Data_Cache_Session')->setValue($cacheKey, $result);

        return $result;
    }

    // ########################################

    public function getChangedItems(array $attributes,
                                    $componentMode = NULL,
                                    $withStoreFilter = false)
    {
        $resultsByListingProduct = $this->getChangedItemsByListingProduct($attributes,
                                                                          $componentMode,
                                                                          $withStoreFilter);

        $resultsByVariationOption = $this->getChangedItemsByVariationOption($attributes,
                                                                            $componentMode,
                                                                            $withStoreFilter);

        $results = array();

        foreach ($resultsByListingProduct as $item) {
            if (isset($results[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $results[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        foreach ($resultsByVariationOption as $item) {
            if (isset($results[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $results[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($results);
    }

    // ---------------------------------------

    public function getChangedItemsByListingProduct(array $attributes,
                                                    $componentMode = NULL,
                                                    $withStoreFilter = false)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $listingsTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductChange')->getMainTable();

        $limit = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/product_change/', 'max_count_per_one_time'
        );

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from($productsChangesTable,'*')
                       ->order(array('id ASC'))
                       ->limit($limit);

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(
                          array('pc' => $select),
                          array(
                              'changed_attribute'=>'attribute',
                              'changed_to_value'=>'value_new',
                              'change_initiators'=>'initiators',
                          )
                       )
                       ->join(
                          array('lp' => $this->getMainTable()),
                          '`pc`.`product_id` = `lp`.`product_id`',
                          'id'
                       )
                       ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductChange::ACTION_UPDATE)
                       ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        if ($withStoreFilter) {
            $select->join(array('l' => $listingsTable),'`lp`.`listing_id` = `l`.`id`',array());
            $select->where("`l`.`store_id` = `pc`.`store_id`");
        }

        !is_null($componentMode) && $select->where("`lp`.`component_mode` = ?",(string)$componentMode);

        $results = array();

        foreach ($select->query()->fetchAll() as $item) {
            if (isset($results[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $results[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($results);
    }

    public function getChangedItemsByVariationOption(array $attributes,
                                                     $componentMode = NULL,
                                                     $withStoreFilter = false)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $listingsTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $variationsTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable();
        $optionsTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable();
        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductChange')->getMainTable();

        $limit = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/product_change/', 'max_count_per_one_time'
        );

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from($productsChangesTable,'*')
                       ->order(array('id ASC'))
                       ->limit($limit);

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(
                            array('pc' => $select),
                            array(
                                'changed_attribute'=>'attribute',
                                'changed_to_value'=>'value_new',
                                'change_initiators'=>'initiators',
                            )
                     )
                     ->join(
                        array('lpvo' => $optionsTable),
                        '`pc`.`product_id` = `lpvo`.`product_id`',
                        array()
                     )
                     ->join(
                        array('lpv' => $variationsTable),
                        '`lpvo`.`listing_product_variation_id` = `lpv`.`id`',
                        array()
                     )
                     ->join(
                        array('lp' => $this->getMainTable()),
                        '`lpv`.`listing_product_id` = `lp`.`id`',
                        array('id')
                     )
                     ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductChange::ACTION_UPDATE)
                     ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        if ($withStoreFilter) {
            $select->join(array('l' => $listingsTable),'`lp`.`listing_id` = `l`.`id`',array());
            $select->where("`l`.`store_id` = `pc`.`store_id`");
        }

        !is_null($componentMode) && $select->where("`lpvo`.`component_mode` = ?",(string)$componentMode);

        $results = array();

        foreach ($select->query()->fetchAll() as $item) {
            if (isset($results[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $results[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($results);
    }

    // ########################################
}