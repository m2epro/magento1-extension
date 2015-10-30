<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Listing
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing', 'listing_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function updateStatisticColumns()
    {
        $this->updateProductsSoldCount();
        $this->updateItemsActiveCount();
        $this->updateItemsSoldCount();
    }

    // ---------------------------------------

    private function updateProductsSoldCount()
    {
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from($listingProductTable,new Zend_Db_Expr('COUNT(*)'))
                       ->where("`listing_id` = `{$this->getMainTable()}`.`listing_id`")
                       ->where("`status` = ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_SOLD);

        $query = "UPDATE `{$this->getMainTable()}`
                  SET `products_sold_count` =  (".$select->__toString().")";

        $this->_getWriteAdapter()->query($query);
    }

    private function updateItemsActiveCount()
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
        $ebayListingProductTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable();

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(
                          array('lp' => $listingProductTable),
                          new Zend_Db_Expr('SUM(`online_qty` - `online_qty_sold`)')
                       )
                       ->join(
                          array('elp' => $ebayListingProductTable),
                          'lp.id = elp.listing_product_id',
                          array()
                       )
                       ->where("`listing_id` = `{$listingTable}`.`id`")
                       ->where("`status` = ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $query = "UPDATE `{$listingTable}`
                  SET `items_active_count` =  IFNULL((".$select->__toString()."),0)
                  WHERE `component_mode` = '".Ess_M2ePro_Helper_Component_Ebay::NICK."'";

        $this->_getWriteAdapter()->query($query);
    }

    private function updateItemsSoldCount()
    {
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
        $ebayListingProductTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable();

        $select = $this->_getReadAdapter()
                       ->select()
                       ->from(
                            array('lp' => $listingProductTable),
                            new Zend_Db_Expr('SUM(`online_qty_sold`)')
                       )
                       ->join(
                            array('elp' => $ebayListingProductTable),
                            'lp.id = elp.listing_product_id',
                            array()
                       )
                       ->where("`listing_id` = `{$this->getMainTable()}`.`listing_id`");

        $query = "UPDATE `{$this->getMainTable()}`
                  SET `items_sold_count` =  (".$select->__toString().")";

        $this->_getWriteAdapter()->query($query);
    }

    //########################################

    public function getProductCollection($listingId)
    {
        $collection = Mage::getResourceModel('catalog/product_collection');

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array('id' => 'id'),
            '{{table}}.listing_id='.(int)$listingId
        );

        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array('listing_product_id' => 'listing_product_id')
        );

        return $collection;
    }

    public function updateMotorsAttributesData($listingId,
                                               array $listingProductIds,
                                               $attribute,
                                               $data,
                                               $overwrite = false) {
        if (count($listingProductIds) == 0) {
            return;
        }

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        $storeId = (int)$listing->getStoreId();

        $listingProductsCollection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $listingProductsCollection->addFieldToFilter('id', array('in' => $listingProductIds));
        $listingProductsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductsCollection->getSelect()->columns(array('product_id'));

        $productIds = $listingProductsCollection->getColumnValues('product_id');

        if ($overwrite) {
            Mage::getSingleton('catalog/product_action')->updateAttributes(
                $productIds,
                array($attribute => $data),
                $storeId
            );
            return;
        }

        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->setStoreId($storeId);
        $productCollection->addFieldToFilter('entity_id', array('in' => $productIds));
        $productCollection->addAttributeToSelect($attribute);

        foreach ($productCollection->getItems() as $itemId => $item) {

            $currentAttributeValue = $item->getData($attribute);
            $newAttributeValue = $data;

            if (!empty($currentAttributeValue)) {
                $newAttributeValue = $currentAttributeValue . ',' . $data;
            }

            Mage::getSingleton('catalog/product_action')->updateAttributes(
                array($itemId),
                array($attribute => $newAttributeValue),
                $storeId
            );
        }
    }

    //########################################
}