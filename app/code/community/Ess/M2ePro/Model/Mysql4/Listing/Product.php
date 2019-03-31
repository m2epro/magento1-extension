<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Listing_Product
    extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Product', 'id');
    }

    //########################################

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
        $cacheKey   = __METHOD__.$productId.sha1(Mage::helper('M2ePro')->jsonEncode($filters));
        $cacheValue = Mage::helper('M2ePro/Data_Cache_Session')->getValue($cacheKey);

        if (!is_null($cacheValue)) {
            return $cacheValue;
        }

        $simpleProductsSelect = $this->_getReadAdapter()
            ->select()
            ->from(
                $this->getMainTable(),
                array('id','component_mode','option_id' => new Zend_Db_Expr('NULL'))
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
                array('option_id' => 'id')
            )
            ->where("`lpvo`.`product_id` = ?",(int)$productId)
            ->where("`lpvo`.`product_type` != ?", "simple");

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
        $foundOptionsIds = array();

        foreach ($unionSelect->query()->fetchAll() as $item) {
            $tempListingProductId = $item['id'];

            if (!empty($item['option_id'])) {
                $foundOptionsIds[$tempListingProductId][] = $item['option_id'];
            }

            if (!empty($result[$tempListingProductId])) {
                continue;
            }

            $result[$tempListingProductId] = Mage::helper('M2ePro/Component')->getComponentObject(
                $item['component_mode'], 'Listing_Product', (int)$tempListingProductId
            );
        }

        foreach ($foundOptionsIds as $listingProductId => $optionsIds) {
            if (empty($result[$listingProductId]) || empty($optionsIds)) {
                continue;
            }

            $result[$listingProductId]->setData('found_options_ids', $optionsIds);
        }

        Mage::helper('M2ePro/Data_Cache_Session')->setValue($cacheKey, $result);

        return array_values($result);
    }

    //########################################
}