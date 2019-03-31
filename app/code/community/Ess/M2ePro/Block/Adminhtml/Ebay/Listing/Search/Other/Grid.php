<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Search_Other_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Search_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingSearchOtherGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort(false);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        $listingOtherCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $listingOtherCollection->getSelect()->distinct();

        $stockId = Mage::helper('M2ePro/Magento_Store')->getStockId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        $listingOtherCollection->getSelect()->joinLeft(
            array(
                'cisi' => Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('cataloginventory_stock_item')
            ),
            "(cisi.product_id = `main_table`.product_id AND cisi.stock_id = {$stockId})",
            array('is_in_stock' => 'is_in_stock')
        );

        $listingOtherCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingOtherCollection->getSelect()->columns(
            array(
                'store_id'              => new Zend_Db_Expr(0),
                'account_id'            => 'main_table.account_id',
                'marketplace_id'        => 'main_table.marketplace_id',
                'entity_id'             => 'main_table.product_id',
                'name'                  => 'second_table.title',
                'sku'                   => 'second_table.sku',
                'currency'              => 'second_table.currency',
                'item_id'               => 'second_table.item_id',
                'listing_product_id'    => new Zend_Db_Expr('NULL'),
                'listing_other_id'      => 'main_table.id',
                'additional_data'       => new Zend_Db_Expr('NULL'),
                'status'                => 'main_table.status',
                'online_sku'            => new Zend_Db_Expr('NULL'),
                'online_title'          => new Zend_Db_Expr('NULL'),
                'online_qty'            => new Zend_Db_Expr('(second_table.online_qty - second_table.online_qty_sold)'),
                'online_qty_sold'       => 'second_table.online_qty_sold',
                'online_bids'           => new Zend_Db_Expr('NULL'),
                'online_start_price'    => new Zend_Db_Expr('NULL'),
                'online_current_price'  => 'second_table.online_price',
                'online_reserve_price'  => new Zend_Db_Expr('NULL'),
                'online_buyitnow_price' => new Zend_Db_Expr('NULL'),
                'listing_id'            => new Zend_Db_Expr('NULL'),
                'listing_title'         => new Zend_Db_Expr('NULL'),
                'is_in_stock'           => 'cisi.is_in_stock'
            )
        );

        $accountId     = (int)$this->getRequest()->getParam('ebayAccount', false);
        $marketplaceId = (int)$this->getRequest()->getParam('ebayMarketplace', false);

        if ($accountId) {
            $listingOtherCollection->getSelect()->where('account_id = ?', $accountId);
        }

        if ($marketplaceId) {
            $listingOtherCollection->getSelect()->where('marketplace_id = ?', $marketplaceId);
        }

        $this->setCollection($listingOtherCollection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->getColumn('name')->setData('header', Mage::helper('M2ePro')->__('Product Title / Product SKU'));
    }

    //########################################

    protected function getColumnProductTitleAdditionalHtml($row)
    {
        $sku = $row->getData('sku');
        if (is_null($sku) && !is_null($row->getData('product_id'))) {

            $sku = Mage::getModel('M2ePro/Magento_Product')
                ->setProductId($row->getData('product_id'))
                ->getSku();
        }

        if (is_null($sku)) {
            $sku = '<i style="color:gray;">' . Mage::helper('M2ePro')->__('receiving') . '...</i>';
        } else if ($sku === '') {
            $sku = '<i style="color:gray;">' . Mage::helper('M2ePro')->__('none') . '</i>';
        } else {
            $sku = Mage::helper('M2ePro')->escapeHtml($sku);
        }

        return '<strong>' . Mage::helper('M2ePro')->__('SKU') . ':</strong>&nbsp;' . $sku;
    }

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if (is_null($row->getData('is_in_stock'))) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return parent::callbackColumnIsInStock($value, $row, $column, $isExport);
    }

    // ---------------------------------------

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle  = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Go to Listing'));
        $iconSrc   = $this->getSkinUrl('M2ePro/images/goto_listing.png');

        $manageUrl = $this->getUrl('*/adminhtml_ebay_listing_other/view/', array(
            'account'     => $row->getData('account_id'),
            'marketplace' => $row->getData('marketplace_id'),
            'filter'      => base64_encode(
                'item_id=' . $row->getData('item_id')
            )
        ));

        return <<<HTML
<div style="float:right; margin:5px 15px 0 0;">
    <a title="{$altTitle}" target="_blank" href="{$manageUrl}"><img src="{$iconSrc}" /></a>
</div>
HTML;
    }

    protected function getProcessingLocks($row)
    {
        $objectId = $row->getData('listing_other_id');
        $object   = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Other', $objectId);
        return $object->getProcessingLocks();
    }

    //########################################

    protected function callbackFilterProductId($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $collection->addFieldToFilter('main_table.product_id', $cond);
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('second_table.title LIKE ? OR second_table.sku LIKE ?', '%'.$value.'%');
    }

    protected function callbackFilterOnlineQty($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $collection->addFieldToFilter(new \Zend_Db_Expr(
            'second_table.online_qty - second_table.online_qty_sold'), $cond
        );
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $collection->addFieldToFilter('second_table.online_price', $cond);
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $collection->addFieldToFilter('main_table.status', $cond);
    }

    protected function callbackFilterItemId($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $collection->addFieldToFilter('second_table.item_id', $cond);
    }

    //########################################
}