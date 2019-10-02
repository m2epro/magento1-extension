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

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingSearchM2eProGrid');
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
            NULL,
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

    protected function getColumnProductTitleAdditionalHtml($row)
    {
        $listingWord  = Mage::helper('M2ePro')->__('Listing');
        $listingUrl   = $this->getUrl('*/adminhtml_ebay_listing/view', array('id' => $row->getData('listing_id')));

        $listingTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('listing_title'));
        strlen($listingTitle) > 50 && $listingTitle = substr($listingTitle, 0, 50) . '...';

        $html = <<<HTML
<strong> {$listingWord}:</strong>&nbsp;
<a href="{$listingUrl}" target="_blank">{$listingTitle}</a><br/>
HTML;

        $sku = $row->getData('sku');
        $onlineSku = $row->getData('online_sku');

        !empty($onlineSku) && $sku = $onlineSku;
        $sku = Mage::helper('M2ePro')->escapeHtml($sku);

        $skuWord = Mage::helper('M2ePro')->__('SKU');
        $html .= <<<HTML
<strong>{$skuWord}:</strong>&nbsp;{$sku}
HTML;

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProductId = $row->getData('listing_product_id');
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);

        if ($listingProduct->getChildObject()->isVariationsReady()) {
            $additionalData    = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));
            $productAttributes = array_keys($additionalData['variations_sets']);
            $productAttributes = implode(', ', $productAttributes);

            $html .= <<<HTML
<div style="font-size: 11px; font-weight: bold; color: grey; margin: 7px 0 0 7px">
    {$productAttributes}
</div>
HTML;
        }

        return $html;
    }

    //----------------------------------------

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle  = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro')->__('Go to Listing'));
        $iconSrc   = $this->getSkinUrl('M2ePro/images/goto_listing.png');

        $manageUrl = $this->getUrl(
            '*/adminhtml_ebay_listing/view/', array(
            'view_mode' => Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View::VIEW_MODE_EBAY,
            'id'        => $row->getData('listing_id'),
            'filter'    => base64_encode(
                'product_id[from]='.(int)$row->getData('entity_id')
                .'&product_id[to]='.(int)$row->getData('entity_id')
            )
            )
        );

        return <<<HTML
<div style="float:right; margin:5px 15px 0 0;">
    <a title="{$altTitle}" target="_blank" href="{$manageUrl}"><img src="{$iconSrc}" /></a>
</div>
HTML;
    }

    protected function getProcessingLocks($row)
    {
        $objectId = $row->getData('listing_product_id');
        $object = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $objectId);
        return $object->getProcessingLocks();
    }

    //########################################

    protected function callbackFilterProductId($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $collection->addFieldToFilter('entity_id', $cond);
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'online_sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%'),
                array('attribute'=>'online_title','like'=>'%'.$value.'%'),
                array('attribute'=>'listing_title','like'=>'%'.$value.'%'),
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
}
