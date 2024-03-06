<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_Qty as OnlineQty;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Ebay_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort(false);

        $this->_showAdvancedFilterProductsOption = false;
        $this->_listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $this->setId('ebayListingViewGrid'. $this->_listing->getId());
    }

    //########################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_modeSwitcher');
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    //########################################

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ?
                $column->getFilterIndex() : $column->getIndex();
            $collection->getSelect()->order($columnIndex.' '.strtoupper($column->getDir()));
        }

        return $this;
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );
        $collection->setListingProductModeOn();
        $collection->setListing($this->_listing->getId());
        $collection->setStoreId($this->_listing->getStoreId());

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id'     => 'id',
                'status' => 'status',
                'component_mode'  => 'component_mode',
                'additional_data' => 'additional_data'
            ),
            '{{table}}.listing_id='.(int)$this->_listing->getId()
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id'    => 'listing_product_id',
                'end_date'              => 'end_date',
                'start_date'            => 'start_date',
                'online_title'          => 'online_title',
                'online_sku'            => 'online_sku',
                'available_qty'         => new Zend_Db_Expr('(elp.online_qty - elp.online_qty_sold)'),
                'ebay_item_id'          => 'ebay_item_id',
                'online_main_category'  => 'online_main_category',
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
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array(
                'item_id' => 'item_id',
            ),
            null,
            'left'
        );

        if ($this->isFilterOrSortByPriceIsUsed('price', 'ebay_online_current_price')) {
            $collection->joinIndexerParent();
        } else {
            $collection->setIsNeedToInjectPrices(true);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'   => Mage::helper('M2ePro')->__('Product ID'),
                'align'    => 'right',
                'width'    => '100px',
                'type'     => 'number',
                'index'    => 'entity_id',
                'store_id' => $this->_listing->getStoreId(),
                'renderer' => 'M2ePro/adminhtml_grid_column_renderer_productId'
            )
        );

        $this->addColumn(
            'name', array(
                'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU / eBay Category'),
                'align'     => 'left',
                'type'      => 'text',
                'index'     => 'online_title',
                'frame_callback' => array($this, 'callbackColumnTitle'),
                'filter'    => 'M2ePro/adminhtml_ebay_listing_view_columnFilter',
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'ebay_item_id', array(
                'header'         => Mage::helper('M2ePro')->__('Item ID'),
                'align'          => 'left',
                'width'          => '100px',
                'type'           => 'text',
                'index'          => 'item_id',
                'account_id'     => $this->_listing->getAccountId(),
                'marketplace_id' => $this->_listing->getMarketplaceId(),
                'renderer'       => 'M2ePro/adminhtml_ebay_grid_column_renderer_itemId'
            )
        );

        $this->addColumn(
            'available_qty', array(
                'header'   => Mage::helper('M2ePro')->__('Available QTY'),
                'align'    => 'right',
                'width'    => '50px',
                'type'     => 'number',
                'index'    => 'available_qty',
                'sortable' => true,
                'filter_index' => 'online_qty',
                'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_qty',
                'render_online_qty' => OnlineQty::ONLINE_AVAILABLE_QTY,
                'filter_condition_callback' => array($this, 'callbackFilterAvailableQty')
            )
        );

        $this->addColumn(
            'online_qty_sold', array(
                'header'   => Mage::helper('M2ePro')->__('Sold QTY'),
                'align'    => 'right',
                'width'    => '50px',
                'type'     => 'number',
                'index'    => 'online_qty_sold',
                'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_qty'
            )
        );

        $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);

        if ($dir == 'desc') {
            $priceSortField = 'max_online_price';
        } else {
            $priceSortField = 'min_online_price';
        }

        $this->addColumn(
            'price', array(
                'header'       => Mage::helper('M2ePro')->__('Price'),
                'align'        =>'right',
                'width'        => '50px',
                'type'         => 'number',
                'currency'     => $this->_listing->getMarketplace()->getChildObject()->getCurrency(),
                'index'        => $priceSortField,
                'filter_index' => $priceSortField,
                'renderer'     => 'M2ePro/adminhtml_ebay_grid_column_renderer_minMaxPrice',
                'filter_condition_callback' => array($this, 'callbackFilterPrice')
            )
        );

        $this->addColumn(
            'end_date', array(
                'header'   => Mage::helper('M2ePro')->__('End Date'),
                'align'    => 'right',
                'width'    => '150px',
                'type'     => 'datetime',
                'format'   => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'index'    => 'end_date',
                'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_dateTime'
            )
        );

        $statusColumn = array(
            'header'       => Mage::helper('M2ePro')->__('Status'),
            'width'        => '100px',
            'index'        => 'status',
            'filter_index' => 'status',
            'type'         => 'options',
            'sortable'     => false,
            'options'      => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN     => Mage::helper('M2ePro')->__('Listed (Hidden)'),
                Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE   => Mage::helper('M2ePro')->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Pending')
            ),
            'showLogIcon'    => true,
            'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_status',
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        );

        if (Mage::helper('M2ePro/View_Ebay')->isDuplicatesFilterShouldBeShown((int)$this->_listing->getId())) {
            $statusColumn['filter'] = 'M2ePro/adminhtml_ebay_grid_column_filter_status';
        }

        $this->addColumn('status', $statusColumn);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $groups = array(
            'actions' => Mage::helper('M2ePro')->__('Actions'),
            'other' =>   Mage::helper('M2ePro')->__('Other'),
        );

        $this->getMassactionBlock()->setGroups($groups);

        // Set mass-action
        // ---------------------------------------
        $data = array(
            'label'    => Mage::helper('M2ePro')->__('List Item(s) on eBay'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?'),
        );

        $this->getMassactionBlock()->addItem('list', $data, 'actions');

        $this->getMassactionBlock()->addItem(
            'revise', array(
            'label'    => Mage::helper('M2ePro')->__('Revise Item(s) on eBay'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'relist', array(
            'label'    => Mage::helper('M2ePro')->__('Relist Item(s) on eBay'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'stop', array(
            'label'    => Mage::helper('M2ePro')->__('Stop Item(s) on eBay'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'stopAndRemove', array(
            'label'    => Mage::helper('M2ePro')->__('Stop on eBay / Remove From Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'actions'
        );

        $this->getMassactionBlock()->addItem(
            'previewItems', array(
            'label'    => Mage::helper('M2ePro')->__('Preview Items'),
            'url'      => '',
            'confirm'  => ''
            ), 'other'
        );

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title = $row->getName();

        $onlineTitle = $row->getData('online_title');
        !empty($onlineTitle) && $title = $onlineTitle;

        $helper = Mage::helper('M2ePro');

        $title = $helper->escapeHtml($title);

        $valueHtml = '<span class="product-title-value">' . $title . '</span>';

        $sku = $row->getData('sku');
        if ($sku === null) {
            $sku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $onlineSku = $row->getData('online_sku');
        !empty($onlineSku) && $sku = $onlineSku;

        $valueHtml .= '<br/>' . '<strong>' . $helper->__('SKU') . ':</strong>&nbsp;' . $helper->escapeHtml($sku);

        if ($category = $row->getData('online_main_category')) {
            $valueHtml .= '<br/><br/>' .
                          '<strong>' . $helper->__('Category') . ':</strong>&nbsp;'.
                           $helper->escapeHtml($category);
        }

        $valueHtml .= '<br/><strong>' . $helper->__('eBay Fee') . ':</strong>&nbsp;' . $this->getItemFeeHtml($row);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')
            ->getObject('Listing_Product', $row->getData('listing_product_id'));

        if (!$listingProduct->getChildObject()->isVariationsReady()) {
            return $valueHtml;
        }

        $additionalData = (array)$helper->jsonDecode($row->getData('additional_data'));

        $productAttributes = isset($additionalData['variations_sets'])
            ? array_keys($additionalData['variations_sets']) : array();

        $valueHtml .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin: 7px 0 0 7px">';
        $valueHtml .= implode(', ', $productAttributes);
        $valueHtml .= '</div>';

        $linkContent = $helper->__('Manage Variations');
        $vpmt = $helper->escapeJs(
            $helper->__('Manage Variations of "%s" ', $title)
        );

        $itemId = $this->getData('item_id');

        if (!empty($itemId)) {
            $vpmt .= '('. $itemId .')';
        }

        $linkTitle = $helper->__('Open Manage Variations Tool');
        $listingProductId = (int)$row->getData('listing_product_id');

        $valueHtml .= <<<HTML
<div style="float: left; margin: 0 0 0 7px">
<a href="javascript:"
onclick="EbayListingEbayGridObj.variationProductManageHandler.openPopUp(
        {$listingProductId}, '{$helper->escapeHtml($vpmt)}'
    )"
title="{$linkTitle}">{$linkContent}</a>&nbsp;
</div>
HTML;

        if ($childVariationIds = $this->getRequest()->getParam('child_variation_ids')) {

            $valueHtml .= <<<HTML
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            EbayListingEbayGridObj.variationProductManageHandler.openPopUp(
                    {$listingProductId}, '{$vpmt}', 'searched_by_child', '{$childVariationIds}'
                )
        }, 350);
    });

</script>
HTML;
        }

        return $valueHtml;
    }

    protected function getItemFeeHtml($row)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ||
            $row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
            $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

            if (empty($additionalData['ebay_item_fees']['listing_fee']['fee'])) {
                return Mage::getSingleton('M2ePro/Currency')->formatPrice(
                    $this->_listing->getMarketplace()->getChildObject()->getCurrency(),
                    0
                );
            }

            $fee = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_product');
            $fee->setData('fees', $additionalData['ebay_item_fees']);
            $fee->setData('product_name', $row->getData('name'));

            return $fee->toHtml();
        }

        $listingProductId = (int)$row->getData('listing_product_id');
        $label = Mage::helper('M2ePro')->__('estimate');

        return <<<HTML
[<a href="javascript:void(0);"
    onclick="EbayListingEbayGridObj.getEstimatedFees({$listingProductId});">{$label}</a>]
HTML;

    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        if (is_array($value) && isset($value['input'])) {
            $value = $value['input'];
        }

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'online_sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%'),
                array('attribute'=>'online_title','like'=>'%'.$value.'%'),
                array('attribute'=>'online_main_category', 'like'=>'%'.$value.'%')
            )
        );
    }

    protected function callbackFilterAvailableQty($collection, $column)
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
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $condition = '';

        if (isset($value['from']) && $value['from'] != '') {
            $condition = 'min_online_price >= \''.(float)$value['from'].'\'';
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $condition .= ' AND ';
            }

            $condition .= 'min_online_price <= \''.(float)$value['to'].'\'';
        }

        $condition = '(' . $condition . ') OR (';

        if (isset($value['from']) && $value['from'] != '') {
            $condition .= 'max_online_price >= \''.(float)$value['from'].'\'';
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $condition .= ' AND ';
            }

            $condition .= 'max_online_price <= \''.(float)$value['to'].'\'';
        }

        $condition .= ')';

        $collection->getSelect()->having($condition);
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

    // ---------------------------------------

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascriptsMain = <<<HTML

<script type="text/javascript">
    EbayListingEbayGridObj.afterInitPage();
</script>

HTML;
            return parent::_toHtml() . $javascriptsMain;
        }

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');
        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;

        // static routes
        $urls = array(
            'adminhtml_ebay_log/listingProduct' => $this->getUrl(
                '*/adminhtml_ebay_log/listingProduct'
            )
        );

        $path = 'adminhtml_ebay_listing/getEstimatedFees';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
                'listing_id' => $this->_listing->getId()
            )
        );

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);

        $temp = Mage::helper('M2ePro/Data_Session')->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = $component . 'ListingViewGrid' . $this->_listing->getId();
        $ignoreListings = Mage::helper('M2ePro')->jsonEncode(array($this->_listing->getId()));

        $logViewUrl = $this->getUrl(
            '*/adminhtml_ebay_log/listing', array(
                'listing_id' => $this->_listing->getId(),
                'back' => $helper->makeBackUrlParam(
                    '*/adminhtml_ebay_listing/view',
                    array('id' => $this->_listing->getId())
                )
            )
        );
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_ebay_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_ebay_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_ebay_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_ebay_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_ebay_listing/runStopAndRemoveProducts');
        $previewItems = $this->getUrl('*/adminhtml_ebay_listing/previewItems');

        $sendingDataToEbayMessage = $helper->escapeJs($helper->__('Sending %product_title% Product(s) data on eBay.'));
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('The Listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Listing is empty.')
        );
        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Listing All Items On eBay')
        );
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Listing Selected Items On eBay')
        );
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Revising Selected Items On eBay')
        );
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Relisting Selected Items On eBay')
        );
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Stopping Selected Items On eBay')
        );
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Stopping On eBay And Removing From Listing Selected Items')
        );
        $removingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__('Removing From Listing Selected Items')
        );

        $popupTitle = $helper->escapeJs($helper->__('Moving eBay Items'));

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'Estimated Fee Details' => Mage::helper('M2ePro')->__('Estimated Fee Details'),
                'Ebay Item Duplicate' => Mage::helper('M2ePro')->__('eBay Item Duplicate'),
            )
        );

        $showAutoAction   = Mage::helper('M2ePro')->jsonEncode((bool)$this->getRequest()->getParam('auto_actions'));

        $javascriptsMain = <<<HTML

<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    M2ePro.productsIdsForList = '{$productsIdsForList}';

    M2ePro.url.logViewUrl = '{$logViewUrl}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.url.runListProducts = '{$runListProducts}';
    M2ePro.url.runReviseProducts = '{$runReviseProducts}';
    M2ePro.url.runRelistProducts = '{$runRelistProducts}';
    M2ePro.url.runStopProducts = '{$runStopProducts}';
    M2ePro.url.runStopAndRemoveProducts = '{$runStopAndRemoveProducts}';
    M2ePro.url.previewItems = '{$previewItems}';

    M2ePro.text.popup_title = '{$popupTitle}';

    M2ePro.text.sending_data_message = '{$sendingDataToEbayMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
    M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
    M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
    M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
    M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
    M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';
    M2ePro.text.removing_selected_items_message = '{$removingSelectedItemsMessage}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    Event.observe(window, 'load', function() {

        EbayListingEbayGridObj = new EbayListingEbayGrid(
            '{$this->getId()}',
            {$this->_listing->getId()}
        );
        EbayListingEbayGridObj.afterInitPage();

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        if (M2ePro.productsIdsForList) {
            EbayListingEbayGridObj.getGridMassActionObj().checkedString = M2ePro.productsIdsForList;
            EbayListingEbayGridObj.actionHandler.listAction();
        }

        if ({$showAutoAction}) {
            ListingAutoActionObj.loadAutoActionHtml();
        }

    });

</script>

HTML;

        return parent::_toHtml() . $javascriptsMain;
    }

    //########################################
}
