<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Listing_Product as WalmartListingProduct;

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Search_Other_Grid
    extends Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Search_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingSearchOtherGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort(false);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->getColumn('name')->setData('header', Mage::helper('M2ePro')->__('Product Title / Product SKU'));
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');
        $collection->getSelect()->distinct();

        $stockId = Mage::helper('M2ePro/Magento_Store')->getStockId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        $collection->getSelect()->joinLeft(
            array(
                'cisi' => Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('cataloginventory_stock_item')
            ),
            "(cisi.product_id = `main_table`.product_id AND cisi.stock_id = {$stockId})",
            array('is_in_stock' => 'is_in_stock')
        );

        $collection->getSelect()->joinLeft(
            array(
                'cpe' => Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('catalog_product_entity')
            ),
            '(cpe.entity_id = `main_table`.product_id)',
            array('sku' => 'sku')
        );

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'sku'                          => 'cpe.sku',
                'name'                         => 'second_table.title',
                'listing_title'                => new Zend_Db_Expr('NULL'),
                'store_id'                     => new Zend_Db_Expr(0),
                'account_id'                   => 'main_table.account_id',
                'marketplace_id'               => 'main_table.marketplace_id',
                'listing_product_id'           => new Zend_Db_Expr('NULL'),
                'listing_other_id'             => 'second_table.listing_other_id',
                'entity_id'                    => 'main_table.product_id',
                'listing_id'                   => new Zend_Db_Expr('NULL'),
                'status'                       => 'main_table.status',
                'is_variation_parent'          => new Zend_Db_Expr('NULL'),
                'variation_child_statuses'     => new Zend_Db_Expr('NULL'),
                'online_sku'                   => 'second_table.sku',
                'gtin'                         => 'second_table.gtin',
                'upc'                          => 'second_table.upc',
                'ean'                          => new Zend_Db_Expr('NULL'),
                'isbn'                         => new Zend_Db_Expr('NULL'),
                'wpid'                         => 'second_table.wpid',
                'channel_url'                  => 'second_table.channel_url',
                'item_id'                      => 'second_table.item_id',
                'online_title'                 => new Zend_Db_Expr('NULL'),
                'online_qty'                   => 'second_table.online_qty',
                'online_price'                 => 'second_table.online_price',
                'online_sale_price'            => new Zend_Db_Expr('NULL'),
                'online_sale_price_start_date' => new Zend_Db_Expr('NULL'),
                'online_sale_price_end_date'   => new Zend_Db_Expr('NULL'),
                'is_in_stock'                  => 'cisi.is_in_stock',
                'is_online_price_invalid'      => 'second_table.is_online_price_invalid',
            )
        );

        $accountId = (int)$this->getRequest()->getParam('walmartAccount', false);
        $marketplaceId = (int)$this->getRequest()->getParam('walmartMarketplace', false);

        if ($accountId) {
            $collection->getSelect()->where('account_id = ?', $accountId);
        }

        if ($marketplaceId) {
            $collection->getSelect()->where('marketplace_id = ?', $marketplaceId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    //########################################

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        $title = $row->getData('name');

        if ($title === null || $title === '') {
            $value = '<i style="color:gray;">receiving...</i>';
        } else {
            $value = '<span>' .Mage::helper('M2ePro')->escapeHtml($title). '</span>';
        }

        $sku = $row->getData('sku');
        if (!empty($sku)) {
            $sku = Mage::helper('M2ePro')->escapeHtml($sku);
            $skuWord = Mage::helper('M2ePro')->__('SKU');

            $value .= <<<HTML
<br/><strong>{$skuWord}:</strong>&nbsp;
{$sku}
HTML;
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Listing_Other $listingOther */
        $listingOther = Mage::helper('M2ePro/Component_Walmart')
            ->getObject('Listing_Other', $row->getData('listing_other_id'));

        $statusChangeReasons = $listingOther->getChildObject()->getStatusChangeReasons();

        return $this->getProductStatus($row, $row->getData('status')) .
               $this->getStatusChangeReasons($statusChangeReasons);
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Go to Listing'));
        $iconSrc = $this->getSkinUrl('M2ePro/images/goto_listing.png');

        $manageUrl = $this->getUrl(
            '*/adminhtml_walmart_listing_other/view/', array(
            'account'     => $row->getData('account_id'),
            'marketplace' => $row->getData('marketplace_id'),
            'filter'      => base64_encode(
                'title=' . $row->getData('online_sku')
            )
            )
        );

        $html = <<<HTML
<div style="float:right; margin:5px 15px 0 0;">
    <a title="{$altTitle}" target="_blank" href="{$manageUrl}"><img src="{$iconSrc}" alt="{$altTitle}" /></a>
</div>
HTML;

        return $html;
    }

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if ($row->getData('is_in_stock') === null) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return parent::callbackColumnIsInStock($value, $row, $column, $isExport);
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '' ||
            ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED &&
             !$row->getData('is_online_price_invalid')))
        {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        $currency = Mage::helper('M2ePro/Component_Walmart')
            ->getCachedObject('Marketplace', $row->getData('marketplace_id'))
            ->getChildObject()
            ->getDefaultCurrency();

        $priceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($value);

        return $priceValue;
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

        $collection->getSelect()->where('second_table.title LIKE ? OR cpe.sku LIKE ?', '%'.$value.'%');
    }

    protected function callbackFilterOnlineSku($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('second_table.sku LIKE ?', '%'.$value.'%');
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $condition = '';

        if (isset($value['from']) || isset($value['to'])) {
            if (isset($value['from']) && $value['from'] != '') {
                $condition = 'second_table.online_price >= \'' . (float)$value['from'] . '\'';
            }

            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }

                $condition .= 'second_table.online_price <= \'' . (float)$value['to'] . '\'';
            }
        }

        $collection->getSelect()->where($condition);
    }

    protected function callbackFilterGtin($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = <<<SQL
second_table.gtin LIKE '%{$value}%' OR
second_table.upc LIKE '%{$value}%' OR
second_table.wpid LIKE '%{$value}%' OR
second_table.item_id LIKE '%{$value}%'
SQL;

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterQty($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $quoted = $collection->getConnection()->quote($value['from']);
            $where .= 'second_table.online_qty >= ' . $quoted;
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $quoted = $collection->getConnection()->quote($value['to']);
            $where .= 'second_table.online_qty <= ' . $quoted;
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('status = ?', $value);
    }

    //########################################
}
