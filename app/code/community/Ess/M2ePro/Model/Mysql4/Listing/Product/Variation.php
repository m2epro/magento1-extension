<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Listing_Product_Variation
    extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    private $variationsProductsIds = array();

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Product_Variation', 'id');
    }

    //########################################

    public function isAllStatusesEnabled($listingProductId, $storeId)
    {
        $variationsProductsIds = $this->getVariationsProductsIds($listingProductId);

        if (count($variationsProductsIds) <= 0) {
            return NULL;
        }

        $statuses = $this->getVariationsStatuses($variationsProductsIds, $storeId);

        return (int)max($statuses) == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
    }

    public function isAllStatusesDisabled($listingProductId, $storeId)
    {
        $variationsProductsIds = $this->getVariationsProductsIds($listingProductId);

        if (count($variationsProductsIds) <= 0) {
            return NULL;
        }

        $statuses = $this->getVariationsStatuses($variationsProductsIds, $storeId);

        return (int)min($statuses) == Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
    }

    // ---------------------------------------

    public function isAllHaveStockAvailabilities($listingProductId)
    {
        $variationsProductsIds = $this->getVariationsProductsIds($listingProductId);

        if (count($variationsProductsIds) <= 0) {
            return NULL;
        }

        $stocks = $this->getVariationsStockAvailabilities($variationsProductsIds);

        return (int)min($stocks);
    }

    public function isAllDoNotHaveStockAvailabilities($listingProductId)
    {
        $variationsProductsIds = $this->getVariationsProductsIds($listingProductId);

        if (count($variationsProductsIds) <= 0) {
            return NULL;
        }

        $stocks = $this->getVariationsStockAvailabilities($variationsProductsIds);

        return !(int)max($stocks);
    }

    //########################################

    private function getVariationsProductsIds($listingProductId)
    {
        if (isset($this->variationsProductsIds[$listingProductId])) {
            return $this->variationsProductsIds[$listingProductId];
        }

        $optionTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable();

        $select = $this->_getReadAdapter()
                        ->select()
                        ->from(
                            array('lpv' => $this->getMainTable()),
                            array('variation_id' => 'id')
                        )
                        ->join(
                            array('lpvo' => $optionTable),
                            '`lpv`.`id` = `lpvo`.`listing_product_variation_id`',
                            array('product_id')
                        )
                        ->where('`lpv`.`listing_product_id` = ?',(int)$listingProductId);

        $result = array();

        foreach ($select->query()->fetchAll() as $value) {
            if (empty($value['product_id'])) {
                continue;
            }

            $result[$value['variation_id']][] = $value['product_id'];
        }

        return $this->variationsProductsIds[$listingProductId] = $result;
    }

    // ---------------------------------------

    private function getVariationsStatuses(array $variationsProductsIds, $storeId)
    {
        $productsIds = array();

        foreach ($variationsProductsIds as $variationProductsIds) {
            foreach ($variationProductsIds as $variationProductId) {
                $productsIds[] = $variationProductId;
            }
        }

        $productsIds = array_values(array_unique($productsIds));
        $statuses = Mage::getSingleton('M2ePro/Magento_Product_Status')->getProductStatus($productsIds, $storeId);

        $variationsProductsStatuses = array();
        foreach ($variationsProductsIds as $key => $variationProductsIds) {
            foreach ($variationProductsIds as $variationProductId) {
                $variationsProductsStatuses[$key][] = $statuses[$variationProductId];
            }
        }

        $variationsStatuses = array();
        foreach ($variationsProductsStatuses as $key => $variationProductsStatuses) {
            $variationsStatuses[$key] = max($variationProductsStatuses);
        }

        return $variationsStatuses;
    }

    private function getVariationsStockAvailabilities(array $variationsProductsIds)
    {
        $productsIds = array();

        foreach ($variationsProductsIds as $variationProductsIds) {
            foreach ($variationProductsIds as $variationProductId) {
                $productsIds[] = $variationProductId;
            }
        }

        $productsIds = array_values(array_unique($productsIds));
        $catalogInventoryTable = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(
                            array('cisi' => $catalogInventoryTable),
                            array('product_id','is_in_stock', 'manage_stock', 'use_config_manage_stock')
                       )
                       ->where('cisi.product_id IN ('.implode(',',$productsIds).')');

        $stocks = $select->query()->fetchAll();

        $variationsProductsStocks = array();
        foreach ($variationsProductsIds as $key => $variationProductsIds) {
            foreach ($variationProductsIds as $id) {
                $count = count($stocks);
                for ($i = 0; $i < $count; $i++) {
                    if ($stocks[$i]['product_id'] == $id) {
                        $stockAvailability = Ess_M2ePro_Model_Magento_Product::calculateStockAvailability(
                            $stocks[$i]['is_in_stock'],
                            $stocks[$i]['manage_stock'],
                            $stocks[$i]['use_config_manage_stock']
                        );
                        $variationsProductsStocks[$key][] = $stockAvailability;
                        break;
                    }
                }
            }
        }

        $variationsStocks = array();
        foreach ($variationsProductsStocks as $key => $variationProductsStocks) {
            $variationsStocks[$key] = min($variationProductsStocks);
        }

        return $variationsStocks;
    }

    //########################################
}