<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Ebay_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var $_sellingFormatTemplate Ess_M2ePro_Model_Ebay_Template_SellingFormat */
    protected $_sellingFormatTemplate = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort(false);

        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingViewGridEbay'.$listing->getId());
        // ---------------------------------------

        $this->_sellingFormatTemplate            = $listing->getChildObject()->getSellingFormatTemplate();
        $this->_showAdvancedFilterProductsOption = false;
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
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getData();

        // ---------------------------------------
        // Get collection
        // ---------------------------------------
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );
        $collection->setListingProductModeOn();
        $collection->setListing($listingData['id']);
        $collection->setStoreId($listingData['store_id']);

        if ($this->isFilterOrSortByPriceIsUsed('price', 'ebay_online_current_price')) {
            $collection->setIsNeedToUseIndexerParent(true);
        }

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        // Join listing product tables
        // ---------------------------------------
        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id',
                'ebay_status' => 'status',
                'component_mode' => 'component_mode',
                'additional_data' => 'additional_data'
            ),
            '{{table}}.listing_id='.(int)$listingData['id']
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
                'template_category_id'  => 'template_category_id',
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

        if ($collection->isNeedUseIndexerParent()) {
            $collection->joinIndexerParent();
        } else {
            $collection->setIsNeedToInjectPrices(true);
        }

        $this->setCollection($collection);
        $result = parent::_prepareCollection();

        return $result;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnListingProductId'),
            )
        );

        $this->addColumn(
            'name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU / eBay Category'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'online_title',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'ebay_item_id', array(
            'header'    => Mage::helper('M2ePro')->__('Item ID'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'text',
            'index'     => 'item_id',
            'frame_callback' => array($this, 'callbackColumnEbayItemId')
            )
        );

        $this->addColumn(
            'available_qty', array(
            'header'    => Mage::helper('M2ePro')->__('Available QTY'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'available_qty',
            'sortable'  => true,
            'filter'    => false,
            'frame_callback' => array($this, 'callbackColumnOnlineAvailableQty')
            )
        );

        $this->addColumn(
            'online_qty_sold', array(
            'header'    => Mage::helper('M2ePro')->__('Sold QTY'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'online_qty_sold',
            'frame_callback' => array($this, 'callbackColumnOnlineQtySold')
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
            'header'    => Mage::helper('M2ePro')->__('Price'),
            'align'     =>'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => $priceSortField,
            'filter_index' => $priceSortField,
            'frame_callback' => array($this, 'callbackColumnPrice'),
            'filter_condition_callback' => array($this, 'callbackFilterPrice')
            )
        );

        $this->addColumn(
            'end_date', array(
            'header'    => Mage::helper('M2ePro')->__('End Date'),
            'align'     => 'right',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'end_date',
            'frame_callback' => array($this, 'callbackColumnEndTime')
            )
        );

        $statusColumn = array(
            'header'       => Mage::helper('M2ePro')->__('Status'),
            'width'        => '100px',
            'index'        => 'ebay_status',
            'filter_index' => 'ebay_status',
            'type'         => 'options',
            'sortable'     => false,
            'options'      => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN     => Mage::helper('M2ePro')->__('Listed (Hidden)'),
                Ess_M2ePro_Model_Listing_Product::STATUS_SOLD       => Mage::helper('M2ePro')->__('Sold'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED    => Mage::helper('M2ePro')->__('Stopped'),
                Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED   => Mage::helper('M2ePro')->__('Finished'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Pending')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus'),
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        );

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getData();
        if (Mage::helper('M2ePro/View_Ebay')->isDuplicatesFilterShouldBeShown((int)$listingData['id'])) {
            $statusColumn['filter'] = 'M2ePro/adminhtml_ebay_grid_column_filter_status';
        }

        $this->addColumn('status', $statusColumn);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Configure groups
        // ---------------------------------------

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

        $title = Mage::helper('M2ePro')->escapeHtml($title);

        $valueHtml = '<span class="product-title-value">' . $title . '</span>';

        $sku = $row->getData('sku');
        if ($sku === null) {
            $sku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $onlineSku = $row->getData('online_sku');
        !empty($onlineSku) && $sku = $onlineSku;

        $valueHtml .= '<br/>' .
                      '<strong>' . Mage::helper('M2ePro')->__('SKU') . ':</strong>&nbsp;' .
                      Mage::helper('M2ePro')->escapeHtml($sku);

        if ($category = $row->getData('online_main_category')) {
            $valueHtml .= '<br/><br/>' .
                          '<strong>' . Mage::helper('M2ePro')->__('Category') . ':</strong>&nbsp;'.
                          Mage::helper('M2ePro')->escapeHtml($category);
        }

        $valueHtml .= '<br/>' .
                      '<strong>' . Mage::helper('M2ePro')->__('eBay Fee') . ':</strong>&nbsp;' .
                      $this->getItemFeeHtml($row);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')
            ->getObject('Listing_Product', $row->getData('listing_product_id'));

        if (!$listingProduct->getChildObject()->isVariationsReady()) {
            return $valueHtml;
        }

        $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

        $productAttributes = isset($additionalData['variations_sets'])
            ? array_keys($additionalData['variations_sets']) : array();

        $valueHtml .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin: 7px 0 0 7px">';
        $valueHtml .= implode(', ', $productAttributes);
        $valueHtml .= '</div>';

        $linkContent = Mage::helper('M2ePro')->__('Manage Variations');
        $vpmt = Mage::helper('M2ePro')->__('Manage Variations of &quot;%s&quot; ', $title);
        $vpmt = addslashes($vpmt);

        $itemId = $this->getData('item_id');

        if (!empty($itemId)) {
            $vpmt .= '('. $itemId .')';
        }

        $linkTitle = Mage::helper('M2ePro')->__('Open Manage Variations Tool');
        $listingProductId = (int)$row->getData('listing_product_id');

        $valueHtml .= <<<HTML
<div style="float: left; margin: 0 0 0 7px">
<a href="javascript:"
onclick="EbayListingEbayGridHandlerObj.variationProductManageHandler.openPopUp({$listingProductId}, '{$vpmt}')"
title="{$linkTitle}">{$linkContent}</a>&nbsp;
</div>
HTML;

        return $valueHtml;
    }

    protected function getItemFeeHtml($row)
    {
        /** @var $listing Ess_M2ePro_Model_Listing */
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if ($row->getData('ebay_status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ||
            $row->getData('ebay_status') == Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
            $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

            if (empty($additionalData['ebay_item_fees']['listing_fee']['fee'])) {
                return Mage::getSingleton('M2ePro/Currency')->formatPrice(
                    $listing->getMarketplace()->getChildObject()->getCurrency(),
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
    onclick="EbayListingEbayGridHandlerObj.getEstimatedFees({$listingProductId});">{$label}</a>]
HTML;

    }

    public function callbackColumnEbayItemId($value, $row, $column, $isExport)
    {
        if ($row->getData('ebay_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getData();

        $url = $this->getUrl(
            '*/adminhtml_ebay_listing/gotoEbay/',
            array(
                'item_id' => $value,
                'account_id' => $listingData['account_id'],
                'marketplace_id' => $listingData['marketplace_id']
            )
        );

        return '<a href="' . $url . '" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnOnlineAvailableQty($value, $row, $column, $isExport)
    {
        if ($row->getData('ebay_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        if ($row->getData('ebay_status') != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
            return '<span style="color: gray; text-decoration: line-through;">' . $value . '</span>';
        }

        return $value;
    }

    public function callbackColumnOnlineQtySold($value, $row, $column, $isExport)
    {
        if ($row->getData('ebay_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('ebay_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $onlineMinPrice = $row->getData('min_online_price');
        $onlineMaxPrice = $row->getData('max_online_price');
        $onlineStartPrice = $row->getData('online_start_price');
        $onlineCurrentPrice = $row->getData('online_current_price');

        if ($onlineMinPrice === null || $onlineMinPrice === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$onlineMinPrice <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        /** @var $listing Ess_M2ePro_Model_Listing */
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $currency = $listing->getMarketplace()->getChildObject()->getCurrency();

        if (!empty($onlineStartPrice)) {
            $onlineReservePrice = $row->getData('online_reserve_price');
            $onlineBuyItNowPrice = $row->getData('online_buyitnow_price');

            $onlineStartStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineStartPrice);

            $startPriceText = Mage::helper('M2ePro')->__('Start Price');

            $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
            $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');
            $onlineCurrentPriceHtml = '';
            $onlineReservePriceHtml = '';
            $onlineBuyItNowPriceHtml = '';

            if ($row->getData('online_bids') > 0 || $onlineCurrentPrice > $onlineStartPrice) {
                $currentPriceText = Mage::helper('M2ePro')->__('Current Price');
                $onlineCurrentStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineCurrentPrice);
                $onlineCurrentPriceHtml = '<strong>'.$currentPriceText.':</strong> '.$onlineCurrentStr.'<br/><br/>';
            }

            if ($onlineReservePrice > 0) {
                $reservePriceText = Mage::helper('M2ePro')->__('Reserve Price');
                $onlineReserveStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineReservePrice);
                $onlineReservePriceHtml = '<strong>'.$reservePriceText.':</strong> '.$onlineReserveStr.'<br/>';
            }

            if ($onlineBuyItNowPrice > 0) {
                $buyItNowText = Mage::helper('M2ePro')->__('Buy It Now Price');
                $onlineBuyItNowStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineBuyItNowPrice);
                $onlineBuyItNowPriceHtml = '<strong>'.$buyItNowText.':</strong> '.$onlineBuyItNowStr;
            }

            $intervalHtml = <<<HTML
<img class="tool-tip-image"
     style="vertical-align: middle;"
     src="{$toolTipIconPath}"><span class="tool-tip-message" style="display:none; text-align: left; min-width: 140px;">
    <img src="{$iconHelpPath}"><span style="color:gray;">
        {$onlineCurrentPriceHtml}
        <strong>{$startPriceText}:</strong> {$onlineStartStr}<br/>
        {$onlineReservePriceHtml}
        {$onlineBuyItNowPriceHtml}
    </span>
</span>
HTML;

            if ($onlineCurrentPrice > $onlineStartPrice) {
                $resultHtml = '<span style="color: grey; text-decoration: line-through;">'.$onlineStartStr.'</span>';
                $resultHtml .= '<br/>'.$intervalHtml.'&nbsp;'.
                    '<span class="product-price-value">'.$onlineCurrentStr.'</span>';
            } else {
                $resultHtml = $intervalHtml.'&nbsp;'.'<span class="product-price-value">'.$onlineStartStr.'</span>';
            }
        } else {
            $onlineMinPriceStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineMinPrice);
            $onlineMaxPriceStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineMaxPrice);

            $resultHtml = '<span class="product-price-value">' . $onlineMinPriceStr . '</span>' .
                (($onlineMinPrice != $onlineMaxPrice) ? ' - ' . $onlineMaxPriceStr :  '');
        }

        $listingProductId = (int)$row->getData('listing_product_id');
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);
        $onlineBids = $listingProduct->getChildObject()->getOnlineBids();

        if ($onlineBids) {
            $title = $row->getName();

            $onlineTitle = $row->getData('online_title');
            !empty($onlineTitle) && $title = $onlineTitle;

            $title = Mage::helper('M2ePro')->escapeHtml($title);

            $bidsPopupTitle = Mage::helper('M2ePro')->__('Bids of &quot;%s&quot;', $title);
            $bidsPopupTitle = addslashes($bidsPopupTitle);

            $bidsTitle = Mage::helper('M2ePro')->__('Show bids list');
            $bidsText = Mage::helper('M2ePro')->__('Bid(s)');

            if ($listingProduct->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED) {
                $resultHtml .= '<br/><br/><span style="font-size: 10px; color: gray;">' .
                    $onlineBids. ' ' . $bidsText . '</span>';
            } else {
                $resultHtml .= <<<HTML
<br/>
<br/>
<a class="m2ePro-ebay-auction-bids-link"
    href="javascript:void(0)"
    title="{$bidsTitle}"
    onclick="EbayListingEbayGridHandlerObj
        .listingProductBidsHandler.openPopUp({$listingProductId},'{$bidsPopupTitle}')"
>{$onlineBids} {$bidsText}</a>
HTML;
            }
        }

        return $resultHtml;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $listingProductId = (int)$row->getData('listing_product_id');

        $html = $this->getViewLogIconHtml($listingProductId);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);

        $synchNote = $listingProduct->getSetting('additional_data', 'synch_template_list_rules_note');
        if (!empty($synchNote)) {
            $synchNote = Mage::helper('M2ePro/View')->getModifiedLogMessage($synchNote);

            if (empty($html)) {
                $html = <<<HTML
<span style="float:right;">
    <img id="map_link_error_icon_{$row->getId()}"
         class="tool-tip-image"
         style="vertical-align: middle;"
         src="{$this->getSkinUrl('M2ePro/images/warning.png')}"><span
         class="tool-tip-message tool-tip-warning tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_notice.gif')}">
        <span>{$synchNote}</span>
    </span>
</span>
HTML;
            } else {
                $html .= <<<HTML
&nbsp;<div id="synch_template_list_rules_note_{$listingProductId}" style="display: none">{$synchNote}</div>
HTML;
            }
        }

        switch ($row->getData('ebay_status')) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $html .= '<span style="color: gray;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $html .= '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                $html .= '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                $html .= '<span style="color: brown;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $html .= '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                $html .= '<span style="color: blue;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $html .= '<span style="color: orange;">'.$value.'</span>';
                break;

            default:
                break;
        }

        $duplicateMark = $listingProduct->getSetting('additional_data', 'item_duplicate_action_required');
        if ($row->getData('is_duplicate') && $duplicateMark) {
            $linkContent = Mage::helper('M2ePro')->__('duplicate');

            $html .= <<<HTML
<div style="float: right; clear: both;">
    <a href="javascript:" onclick="EbayListingEbayGridHandlerObj.openItemDuplicatePopUp({$listingProductId});"
    >{$linkContent}</a>
    &nbsp;
    <img style="vertical-align: middle;" src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
</div>
<br>
HTML;
        }

        $html .= $this->getScheduledTag($row) . $this->getLockedTag($row);

        return $html;
    }

    public function callbackColumnEndTime($value, $row, $column, $isExport)
    {
        if ($row->getData('ebay_status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    // ---------------------------------------

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
                array('attribute'=>'online_main_category', 'like'=>'%'.$value.'%')
            )
        );
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

    public function getViewLogIconHtml($listingProductId)
    {
        $listingProductId = (int)$listingProductId;

        // Get last messages
        // ---------------------------------------
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
                array('action_id','action','type','description','create_date','initiator')
            )
            ->where('`listing_product_id` = ?', $listingProductId)
            ->where('`action_id` IS NOT NULL')
            ->where('`action` IN (?)', $this->getAvailableActions())
            ->order(array('id DESC'))
            ->limit(30);

        $logRows = $connRead->fetchAll($dbSelect);
        // ---------------------------------------

        // Get grouped messages by action_id
        // ---------------------------------------
        $actionsRows = array();
        $tempActionRows = array();
        $lastActionId = false;

        foreach ($logRows as $row) {
            $row['description'] = Mage::helper('M2ePro/View')->getModifiedLogMessage($row['description']);

            if ($row['action_id'] !== $lastActionId) {
                if (!empty($tempActionRows)) {
                    $actionsRows[] = array(
                        'type' => $this->getMainTypeForActionId($tempActionRows),
                        'date' => $this->getMainDateForActionId($tempActionRows),
                        'action' => $this->getActionForAction($tempActionRows[0]),
                        'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                        'items' => $tempActionRows
                    );
                    $tempActionRows = array();
                }

                $lastActionId = $row['action_id'];
            }

            $tempActionRows[] = $row;
        }

        if (!empty($tempActionRows)) {
            $actionsRows[] = array(
                'type' => $this->getMainTypeForActionId($tempActionRows),
                'date' => $this->getMainDateForActionId($tempActionRows),
                'action' => $this->getActionForAction($tempActionRows[0]),
                'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                'items' => $tempActionRows
            );
        }

        if (empty($actionsRows)) {
            return '';
        }

        foreach ($actionsRows as &$actionsRow) {
            usort(
                $actionsRow['items'], function($a, $b)
                {
                $sortOrder = array(
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 1,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 2,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 3,
                );

                return $sortOrder[$a["type"]] > $sortOrder[$b["type"]];
                }
            );
        }

        $tips = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'Last Action was completed successfully.',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'Last Action was completed with error(s).',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'Last Action was completed with warning(s).'
        );

        $icons = array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => 'normal',
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => 'error',
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => 'warning'
        );

        $summary = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_log_grid_summary', '', array(
            'entity_id' => $listingProductId,
            'rows' => $actionsRows,
            'tips' => $tips,
            'icons' => $icons,
            'view_help_handler' => 'EbayListingEbayGridHandlerObj.viewItemHelp',
            'hide_help_handler' => 'EbayListingEbayGridHandlerObj.hideItemHelp',
            )
        );

        return $summary->toHtml();
    }

    protected function getAvailableActions()
    {
        return array(
            Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT,
            Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT,
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE
        );
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['action']) {
            case Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('List');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Relist');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Revise');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT:
                $string = Mage::helper('M2ePro')->__('Stop');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Stop on Channel / Remove from Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_LISTING:
                $string = Mage::helper('M2ePro')->__('Remove from Listing');
                break;
            case Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE:
                $string = Mage::helper('M2ePro')->__('Channel Change');
                break;
        }

        return $string;
    }

    public function getInitiatorForAction($actionRows)
    {
        $string = '';

        switch ((int)$actionRows['initiator']) {
            case Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN:
                $string = '';
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_USER:
                $string = Mage::helper('M2ePro')->__('Manual');
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                $string = Mage::helper('M2ePro')->__('Automatic');
                break;
        }

        return $string;
    }

    public function getMainTypeForActionId($actionRows)
    {
        $type = Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;

        foreach ($actionRows as $row) {
            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                break;
            }

            if ($row['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING) {
                $type = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
            }
        }

        return $type;
    }

    public function getMainDateForActionId($actionRows)
    {
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        return Mage::app()->getLocale()->date(strtotime($actionRows[0]['create_date']))->toString($format);
    }

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
    EbayListingEbayGridHandlerObj.afterInitPage();
</script>

HTML;
            return parent::_toHtml() . $javascriptsMain;
        }

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

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
            'listing_id' => $listingData['id']
            )
        );

        $path = 'adminhtml_ebay_listing/getCategoryChooserHtml';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
            'listing_id' => $listingData['id']
            )
        );

        $path = 'adminhtml_ebay_listing/getCategorySpecificHtml';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
            'listing_id' => $listingData['id']
            )
        );

        $path = 'adminhtml_ebay_listing/saveCategoryTemplate';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
            'listing_id' => $listingData['id']
            )
        );

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);

        $temp = Mage::helper('M2ePro/Data_Session')->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = $component . 'ListingViewGrid' . $listingData['id'];
        $ignoreListings = Mage::helper('M2ePro')->jsonEncode(array($listingData['id']));

        $logViewUrl = $this->getUrl(
            '*/adminhtml_ebay_log/listing', array(
            'id'=>$listingData['id'],
            'back'=>$helper->makeBackUrlParam('*/adminhtml_ebay_listing/view', array('id'=>$listingData['id']))
            )
        );
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_ebay_listing/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_ebay_listing/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_ebay_listing/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_ebay_listing/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_ebay_listing/runStopAndRemoveProducts');
        $previewItems = $this->getUrl('*/adminhtml_ebay_listing/previewItems');

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = $helper->escapeJs(
            $helper->__('"%task_title%" Task was successfully submitted to be processed.')
        );
        $taskRealtimeCompletedSuccessMessage = $helper->escapeJs(
            $helper->__('"%task_title%" Task was completed successfully.')
        );

        $tempString = '"%task_title%" task was completed with warnings. ';
        $tempString .= '<a target="_blank" href="%url%">View Log</a> for the details.';
        $taskCompletedWarningMessage = $helper->escapeJs($helper->__($tempString));

        $tempString = '"%task_title%" task was completed with errors. ';
        $tempString .= '<a target="_blank" href="%url%">View Log</a> for the details.';
        $taskCompletedErrorMessage = $helper->escapeJs($helper->__($tempString));

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

        $selectItemsMessage = $helper->escapeJs(
            $helper->__('Please select the Products you want to perform the Action on.')
        );
        $selectActionMessage = $helper->escapeJs($helper->__('Please select Action.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $popupTitle = $helper->escapeJs($helper->__('Moving eBay Items'));

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'eBay Categories' => Mage::helper('M2ePro')->__('eBay Categories'),
            'of Product' => Mage::helper('M2ePro')->__('of Product'),
            'Specifics' => Mage::helper('M2ePro')->__('Specifics'),
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

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_realtime_completed_success_message = '{$taskRealtimeCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

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

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.select_action_message = '{$selectActionMessage}';

    M2ePro.text.success_word = '{$successWord}';
    M2ePro.text.notice_word = '{$noticeWord}';
    M2ePro.text.warning_word = '{$warningWord}';
    M2ePro.text.error_word = '{$errorWord}';
    M2ePro.text.close_word = '{$closeWord}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    Event.observe(window, 'load', function() {

        EbayListingEbayGridHandlerObj = new EbayListingEbayGridHandler(
            '{$this->getId()}',
            {$listingData['id']}
        );
        EbayListingEbayGridHandlerObj.afterInitPage();

        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        if (M2ePro.productsIdsForList) {
            EbayListingEbayGridHandlerObj.getGridMassActionObj().checkedString = M2ePro.productsIdsForList;
            EbayListingEbayGridHandlerObj.actionHandler.listAction();
        }

        if ({$showAutoAction}) {
            ListingAutoActionHandlerObj.loadAutoActionHtml();
        }

    });

</script>

HTML;

        return parent::_toHtml() . $javascriptsMain;
    }

    protected function getLockedTag($row)
    {
        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', (int)$row['id']);
        $processingLocks = $listingProduct->getProcessingLocks();

        $html = '';

        foreach ($processingLocks as $processingLock) {
            switch ($processingLock->getTag()) {
                case 'list_action':
                    $html .= '<br/><span style="color: #605fff">[List in Progress...]</span>';
                    break;

                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Stop And Remove in Progress...]</span>';
                    break;

                default:
                    break;
            }
        }

        return $html;
    }

    protected function getScheduledTag($row)
    {
        $html = '';

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('listing_product_id', $row['id']);

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction */
        $scheduledAction = $scheduledActionsCollection->getFirstItem();

        if (!$scheduledAction->getId()) {
            return $html;
        }

        switch ($scheduledAction->getActionType()) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                $html .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $html .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:

                $reviseParts = array();

                $additionalData = $scheduledAction->getAdditionalData();
                if (!empty($additionalData['configurator'])) {
                    $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
                    $configurator->setData($additionalData['configurator']);

                    if ($configurator->isIncludingMode()) {
                        if ($configurator->isQtyAllowed()) {
                            $reviseParts[] = 'QTY';
                        }

                        if ($configurator->isPriceAllowed()) {
                            $reviseParts[] = 'Price';
                        }

                        if ($configurator->isTitleAllowed()) {
                            $reviseParts[] = 'Title';
                        }

                        if ($configurator->isSubtitleAllowed()) {
                            $reviseParts[] = 'Subtitle';
                        }

                        if ($configurator->isDescriptionAllowed()) {
                            $reviseParts[] = 'Description';
                        }

                        if ($configurator->isImagesAllowed()) {
                            $reviseParts[] = 'Images';
                        }

                        if ($configurator->isCategoriesAllowed()) {
                            $reviseParts[] = 'Categories / Specifics';
                        }

                        if ($configurator->isShippingAllowed()) {
                            $reviseParts[] = 'Shipping';
                        }

                        if ($configurator->isPaymentAllowed()) {
                            $reviseParts[] = 'Payment';
                        }

                        if ($configurator->isReturnAllowed()) {
                            $reviseParts[] = 'Return';
                        }

                        if ($configurator->isOtherAllowed()) {
                            $reviseParts[] = 'Other';
                        }
                    }
                }

                if (!empty($reviseParts)) {
                    $html .= '<br/><span style="color: #605fff">[Revise of '.implode(', ', $reviseParts)
                             .' is Scheduled...]</span>';
                } else {
                    $html .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $html .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                $html .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;
        }

        return $html;
    }

    //########################################
}
