<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product as AmazonListingProduct;

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Search_Other_Grid
    extends Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Search_Grid
{
    const ACTUAL_QTY_EXPRESSION =
        'IF(second_table.is_afn_channel = 1, second_table.online_afn_qty, second_table.online_qty)';

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingSearchOtherGrid');

        $this->setDefaultSort(false);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->getColumn('name')->setData('header', Mage::helper('M2ePro')->__('Product Title / Product SKU'));
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
        $collection->getSelect()->distinct();

        $collection->getSelect()->joinLeft(
            array(
                'cpe' => Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('catalog_product_entity')
            ),
            '(cpe.entity_id = `main_table`.product_id)',
            array('sku' => 'sku')
        );

        $stockId = Mage::helper('M2ePro/Magento_Store')->getStockId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        $collection->getSelect()->joinLeft(
            array(
                'cisi' => Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('cataloginventory_stock_item')
            ),
            "(cisi.product_id = `main_table`.product_id AND cisi.stock_id = {$stockId})",
            array('is_in_stock' => 'is_in_stock')
        );

        $afnStateAllNo  = AmazonListingProduct::VARIATION_PARENT_IS_AFN_STATE_ALL_NO;
        $afnStateAllYes = AmazonListingProduct::VARIATION_PARENT_IS_AFN_STATE_ALL_YES;
        $repricingStateAllNo = AmazonListingProduct::VARIATION_PARENT_IS_REPRICING_STATE_ALL_NO;
        $repricingStateAllYes = AmazonListingProduct::VARIATION_PARENT_IS_REPRICING_STATE_ALL_YES;

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
                'entity_id'                    => 'main_table.product_id',
                'listing_id'                   => new Zend_Db_Expr('NULL'),
                'status'                       => 'main_table.status',
                'is_general_id_owner'          => new Zend_Db_Expr('NULL'),
                'general_id'                   => 'second_table.general_id',
                'is_afn_channel'               => 'second_table.is_afn_channel',
                'is_variation_parent'          => new Zend_Db_Expr('NULL'),
                'variation_child_statuses'     => new Zend_Db_Expr('NULL'),
                'online_sku'                   => 'second_table.sku',
                'online_title'                 => new Zend_Db_Expr('NULL'),
                'online_qty'                   => 'second_table.online_qty',
                'online_afn_qty'               => 'second_table.online_afn_qty',
                'online_actual_qty'            => self::ACTUAL_QTY_EXPRESSION,
                'online_price'                 => 'second_table.online_price',
                'online_sale_price'            => new Zend_Db_Expr('NULL'),
                'online_sale_price_start_date' => new Zend_Db_Expr('NULL'),
                'online_sale_price_end_date'   => new Zend_Db_Expr('NULL'),

                'online_business_price'        => new Zend_Db_Expr('NULL'),

                'online_current_price'         => 'second_table.online_price',
                'online_regular_price'         => 'second_table.online_price',

                'is_repricing'                 => 'second_table.is_repricing',
                'is_repricing_disabled'        => 'second_table.is_repricing_disabled',

                'variation_parent_afn_state' => new \Zend_Db_Expr(
                    "IF(
                    second_table.is_afn_channel = 1,
                    {$afnStateAllYes},
                    {$afnStateAllNo}
                )"
                ),
                'variation_parent_repricing_state' => new \Zend_Db_Expr(
                    "IF(
                    second_table.is_repricing = 1,
                    {$repricingStateAllYes},
                    {$repricingStateAllNo}
                )"
                ),

                'is_in_stock' => 'cisi.is_in_stock'
            )
        );

        $accountId = (int)$this->getRequest()->getParam('amazonAccount', false);
        $marketplaceId = (int)$this->getRequest()->getParam('amazonMarketplace', false);

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

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if ($row->getData('is_in_stock') === null) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return parent::callbackColumnIsInStock($value, $row, $column, $isExport);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        return $this->getProductStatus($row->getData('status'));
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Go to Listing'));
        $iconSrc = $this->getSkinUrl('M2ePro/images/goto_listing.png');

        $manageUrl = $this->getUrl(
            '*/adminhtml_amazon_listing_other/view/', array(
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

    protected function callbackFilterAsinIsbn($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter('general_id', array('like' => '%'.$value.'%'));
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

        if (isset($value['is_repricing']) && $value['is_repricing'] !== '') {
            if (!empty($condition)) {
                $condition = '(' . $condition . ') OR ';
            }

            $condition .= 'second_table.is_repricing = ' . (int)$value['is_repricing'];
        }

        $collection->getSelect()->where($condition);
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
            $where .= self::ACTUAL_QTY_EXPRESSION . ' >= ' . $quoted;
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $quoted = $collection->getConnection()->quote($value['to']);
            $where .= self::ACTUAL_QTY_EXPRESSION . ' <= ' . $quoted;
        }

        if (isset($value['afn']) && $value['afn'] !== '') {
            if (!empty($where)) {
                $where .= ' AND ';
            }

            $where .= 'second_table.is_afn_channel = ' . (int)$value['afn'];
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
