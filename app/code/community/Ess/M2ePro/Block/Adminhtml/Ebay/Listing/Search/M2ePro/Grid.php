<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Search_M2ePro_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Search_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingSearchM2eProGrid');

        $this->setDefaultSort(false);
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection->getSelect()->distinct();
        $collection->setListingProductModeOn();

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $collection->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
        $collection->joinStockItem(
            array(
            'is_in_stock' => 'is_in_stock'
            )
        );

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'              => 'id',
                'status'          => 'status',
                'component_mode'  => 'component_mode',
                'listing_id'      => 'listing_id',
                'additional_data' => 'additional_data',
            )
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id'    => 'listing_product_id',
                'ebay_item_id'          => 'ebay_item_id',
                'online_title'          => 'online_title',
                'online_sku'            => 'online_sku',
                'online_qty'            => new Zend_Db_Expr('(elp.online_qty - elp.online_qty_sold)'),
                'online_qty_sold'       => 'online_qty_sold',
                'online_bids'           => 'online_bids',
                'online_start_price'    => 'online_start_price',
                'online_current_price'  => 'online_current_price',
                'online_reserve_price'  => 'online_reserve_price',
                'online_buyitnow_price' => 'online_buyitnow_price',

                'is_duplicate'          => 'is_duplicate',
            )
        );
        $collection->joinTable(
            array('l' => 'M2ePro/Listing'),
            'id=listing_id',
            array(
                'store_id'              => 'store_id',
                'account_id'            => 'account_id',
                'marketplace_id'        => 'marketplace_id',
                'listing_title'         => 'title',
            )
        );
        $collection->joinTable(
            array('em' => 'M2ePro/Ebay_Marketplace'),
            'marketplace_id=marketplace_id',
            array(
                'currency' => 'currency',
            )
        );
        $collection->joinTable(
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array(
                'item_id' => 'item_id',
            ),
            null,
            'left'
        );

        $accountId = (int)$this->getRequest()->getParam('ebayAccount', false);
        $marketplaceId = (int)$this->getRequest()->getParam('ebayMarketplace', false);

        if ($accountId) {
            $collection->getSelect()->where('l.account_id = ?', $accountId);
        }

        if ($marketplaceId) {
            $collection->getSelect()->where('l.marketplace_id = ?', $marketplaceId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    //########################################

    /**
     * @param Mage_Catalog_Model_Product $row
     *
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getColumnProductTitleAdditionalHtml($row)
    {
        /** @var Ess_M2ePro_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('M2ePro');

        /** @var Ess_M2ePro_Helper_Component_Walmart $eBayHelper */
        $eBayHelper = Mage::helper('M2ePro/Component_Ebay');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = $eBayHelper->getObject('Account', $row->getData('account_id'));

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = $eBayHelper->getObject('Marketplace', $row->getData('marketplace_id'));

        $listingTitle = $dataHelper->escapeHtml($row->getData('listing_title'));
        strlen($listingTitle) > 50 && $listingTitle = substr($listingTitle, 0, 50) . '...';
        $listingUrl = $this->getUrl('*/adminhtml_ebay_listing/view', array('id' => $row->getData('listing_id')));

        $value = <<<HTML
<strong>{$dataHelper->__('Listing')}:</strong>&nbsp;<a href="{$listingUrl}" target="_blank">{$listingTitle}</a>
<br/><strong>{$dataHelper->__('Account')}:</strong>&nbsp;{$dataHelper->escapeHtml($account->getTitle())}
<br/><strong>{$dataHelper->__('Marketplace')}:</strong>&nbsp;{$dataHelper->escapeHtml($marketplace->getTitle())}
<br/><strong>{$dataHelper->__('SKU')}:</strong>&nbsp;{$dataHelper->escapeHtml($row->getSku())}
HTML;

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = $eBayHelper->getObject('Listing_Product', (int)$row->getData('listing_product_id'));
        if ($listingProduct->getChildObject()->isVariationsReady()) {
            $additionalData    = (array)$dataHelper->jsonDecode($row->getData('additional_data'));
            $productAttributes = array_keys($additionalData['variations_sets']);
            $productAttributes = implode(', ', $productAttributes);

            $value .= <<<HTML
<div style="font-size: 11px; font-weight: bold; color: grey; margin: 7px 0 0 7px">
    {$productAttributes}
</div>
HTML;
        }

        return $value;
    }

    //----------------------------------------

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $helper    = Mage::helper('M2ePro');
        $productId = (int)$row->getData('entity_id');

        $urlData = array(
            'id'        => $row->getData('listing_id'),
            'view_mode' => Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View::VIEW_MODE_EBAY,
            'filter'    => base64_encode("product_id[from]={$productId}&product_id[to]={$productId}")
        );

        $searchedChildHtml = '';
        if ($this->wasFoundByChild($row)) {
            $urlData['child_variation_ids'] = $this->getChildVariationIds($row);

            $searchedChildHtml = <<<HTML
<img class="tool-tip-image"
style="vertical-align: middle; margin-top: 4px; margin-left: 10px;"
src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
<span class="tool-tip-message tip-left" style="display:none; text-align: left; min-width: 140px;">
    <img src="{$this->getSkinUrl('M2ePro/images/i_logo.png')}">
    <span style="color:gray;">{$helper->__(
                'A Product you are searching for is found as part of a Multi-Variational Product.' .
                ' Click on the arrow icon to manage it individually.'
            )}</span>
</span>
HTML;
        }

        $manageUrl = $this->getUrl('*/adminhtml_ebay_listing/view/', $urlData);
        $html = <<<HTML
<div style="float:right; margin:5px 15px 0 0;">
    <a title="{$helper->escapeHtml(Mage::helper('M2ePro')->__('Go to Listing'))}" target="_blank" href="{$manageUrl}">
    <img src="{$this->getSkinUrl('M2ePro/images/goto_listing.png')}" /></a>
</div>
HTML;

        return $searchedChildHtml . $html;
    }

    //########################################

    protected function callbackFilterProductId($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $childCollection = $this->getMagentoChildProductsCollection();
        $childCollection->addFieldToFilter('product_id', $cond);

        $collection->joinTable(
            array('product_id_subQuery' => $childCollection->getSelect()),
            'listing_product_id=id',
            array(
                'product_id_child_variation_ids' => 'child_variation_ids',
                'product_id_searched_by_child'   => 'searched_by_child'
            ),
            null,
            'left'
        );

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'entity_id', $cond),
                array('attribute' => 'product_id_searched_by_child', 1)
            )
        );
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $childCollection = $this->getMagentoChildProductsCollection();
        $childCollection->getSelect()->joinLeft(
            array('cpe' => Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('catalog_product_entity')),
            'cpe.entity_id=main_table.product_id',
            array()
        );

        $childCollection->addFieldToFilter('cpe.sku', array('like' => '%'.$value.'%'));

        $collection->joinTable(
            array('product_sku_subQuery' => $childCollection->getSelect()),
            'listing_product_id=id',
            array(
                'product_sku_child_variation_ids' => 'child_variation_ids',
                'product_sku_searched_by_child'   => 'searched_by_child'
            ),
            null,
            'left'
        );

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'sku','like'=>'%'.$value.'%'),
                array('attribute' => 'online_sku','like'=>'%'.$value.'%'),
                array('attribute' => 'name', 'like'=>'%'.$value.'%'),
                array('attribute' => 'online_title','like'=>'%'.$value.'%'),
                array('attribute' => 'listing_title','like'=>'%'.$value.'%'),
                array('attribute' => 'product_sku_searched_by_child', 1)
            )
        );
    }

    protected function callbackFilterOnlineQty($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $where = '';
        $onlineQty = 'elp.online_qty - elp.online_qty_sold';

        if (isset($cond['from']) || isset($cond['to'])) {
            if (isset($cond['from']) && $cond['from'] != '') {
                $value = $collection->getConnection()->quote($cond['from']);
                $where .= "{$onlineQty} >= {$value}";
            }

            if (isset($cond['to']) && $cond['to'] != '') {
                if (isset($cond['from']) && $cond['from'] != '') {
                    $where .= ' AND ';
                }

                $value = $collection->getConnection()->quote($cond['to']);
                $where .= "{$onlineQty} <= {$value}";
            }
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $collection->addFieldToFilter('online_current_price', $cond);
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $index = $column->getIndex();

        if ($value == null) {
            return;
        }

        if (is_array($value) && isset($value['value'])) {
            $collection->addFieldToFilter($index, (int)$value['value']);
        } elseif (!is_array($value) && $value !== null) {
            $collection->addFieldToFilter($index, (int)$value);
        }

        if (is_array($value) && isset($value['is_duplicate'])) {
            $collection->addFieldToFilter('is_duplicate', 1);
        }
    }

    protected function callbackFilterItemId($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $collection->addFieldToFilter('item_id', $cond);
    }

    //########################################

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();

            if ($columnIndex == 'online_qty') {
                $collection->getSelect()->order(
                    '(elp.online_qty - elp.online_qty_sold) ' . strtoupper($column->getDir())
                );
            } else {
                $collection->setOrder($columnIndex, strtoupper($column->getDir()));
            }
        }

        return $this;
    }

    //########################################

    private function getMagentoChildProductsCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Variation_Option_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Product_Variation_Option')->getCollection()
            ->addFieldToSelect('listing_product_variation_id')
            ->addFieldToFilter('main_table.component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);

        $collection->getSelect()->joinLeft(
            array('lpv' => Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable()),
            'lpv.id=main_table.listing_product_variation_id',
            array('listing_product_id')
        );

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'child_variation_ids' => new Zend_Db_Expr('GROUP_CONCAT(lpv.id)'),
                'listing_product_id'  => 'lpv.listing_product_id',
                'searched_by_child'   => new Zend_Db_Expr(1)
            )
        );

        $collection->getSelect()->group("lpv.listing_product_id");

        return $collection;
    }

    //########################################

    protected function wasFoundByChild($row)
    {
        foreach (array('product_id', 'product_sku') as $item) {
            $searchedByChild = $row->getData("{$item}_searched_by_child");
            if (!empty($searchedByChild)) {
                return true;
            }
        }

        return false;
    }

    protected function getChildVariationIds($row)
    {
        $ids = array();

        foreach (array('product_id', 'product_sku') as $item) {
            $itemIds = $row->getData("{$item}_child_variation_ids");
            if (empty($itemIds)) {
                continue;
            }

            foreach (explode(',', $itemIds) as $itemId) {
                !isset($ids[$itemId]) && $ids[$itemId] = 0;
                $ids[$itemId]++;
            }
        }

        $maxCount = max($ids);
        foreach ($ids as $id => $count) {
            if ($count < $maxCount) {
                unset($ids[$id]);
            }
        }

        return implode(',', array_keys($ids));
    }

    //########################################
}
