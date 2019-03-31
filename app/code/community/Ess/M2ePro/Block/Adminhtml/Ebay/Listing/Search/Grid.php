<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    protected function _prepareColumns()
    {
        $helper = Mage::helper('M2ePro');

        $this->addColumn('product_id', array(
            'header'       => $helper->__('Product ID'),
            'align'        => 'right',
            'width'        => '100px',
            'type'         => 'number',
            'index'        => 'entity_id',
            'filter_index' => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnProductId'),
            'filter_condition_callback' => array($this, 'callbackFilterProductId')
        ));

        $this->addColumn('name', array(
            'header'         => $helper->__('Product Title / Listing / Product SKU'),
            'align'          => 'left',
            'type'           => 'text',
            'index'          => 'name',
            'filter_index'   => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('is_in_stock', array(
            'header'    => $helper->__('Stock Availability'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'is_in_stock',
            'filter_index' => 'is_in_stock',
            'options' => array(
                '1' => $helper->__('In Stock'),
                '0' => $helper->__('Out of Stock')
            ),
            'frame_callback' => array($this, 'callbackColumnIsInStock')
        ));

        $this->addColumn('item_id', array(
            'header'       => $helper->__('Item ID'),
            'align'        => 'left',
            'width'        => '100px',
            'type'         => 'text',
            'index'        => 'item_id',
            'filter_index' => 'item_id',
            'frame_callback' => array($this, 'callbackColumnEbayItemId'),
            'filter_condition_callback' => array($this, 'callbackFilterItemId')
        ));

        $this->addColumn('online_qty', array(
            'header'       => $helper->__('Available QTY'),
            'align'        => 'right',
            'width'        => '50px',
            'type'         => 'number',
            'index'        => 'online_qty',
            'filter_index' => 'online_qty',
            'frame_callback' => array($this, 'callbackColumnOnlineAvailableQty'),
            'filter_condition_callback' => array($this, 'callbackFilterOnlineQty')
        ));

        $this->addColumn('online_qty_sold', array(
            'header'       => $helper->__('Sold QTY'),
            'align'        => 'right',
            'width'        => '50px',
            'type'         => 'number',
            'index'        => 'online_qty_sold',
            'filter_index' => 'online_qty_sold',
            'frame_callback' => array($this, 'callbackColumnOnlineQtySold')
        ));

        $this->addColumn('price', array(
            'header'       => $helper->__('Price'),
            'align'        => 'right',
            'width'        => '50px',
            'type'         => 'number',
            'index'        => 'online_current_price',
            'filter_index' => 'online_current_price',
            'frame_callback' => array($this, 'callbackColumnPrice'),
            'filter_condition_callback' => array($this, 'callbackFilterPrice')
        ));

        $statusColumn = array(
            'header'       => $helper->__('Status'),
            'width'        => '100px',
            'index'        => 'status',
            'filter_index' => 'status',
            'type'         => 'options',
            'sortable'     => false,
            'options'      => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => $helper->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => $helper->__('Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN     => $helper->__('Listed (Hidden)'),
                Ess_M2ePro_Model_Listing_Product::STATUS_SOLD       => $helper->__('Sold'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED    => $helper->__('Stopped'),
                Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED   => $helper->__('Finished'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => $helper->__('Pending')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus'),
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        );

        $listingType = $this->getRequest()->getParam(
            'listing_type', Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_M2E_PRO
        );

        if (Mage::helper('M2ePro/View_Ebay')->isDuplicatesFilterShouldBeShown() &&
            $listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_M2E_PRO) {
            $statusColumn['filter'] = 'M2ePro/adminhtml_ebay_grid_column_filter_status';
        }

        if ($listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_LISTING_OTHER) {
            unset($statusColumn['options'][Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED]);
        }

        $this->addColumn('status', $statusColumn);

        $this->addColumn('goto_listing_item', array(
            'header'    => $helper->__('Manage'),
            'align'     => 'center',
            'width'     => '50px',
            'type'      => 'text',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        if (is_null($row->getData('entity_id'))) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $productId = (int)$row->getData('entity_id');
        $storeId   = (int)$row->getData('store_id');

        $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $productId));
        $withoutImageHtml = '<a href="'.$url.'" target="_blank">'.$productId.'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')
                                                ->getConfig()->getGroupValue('/view/','show_products_thumbnails');
        if (!$showProductsThumbnails) {
            return $withoutImageHtml;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($productId);
        $magentoProduct->setStoreId($storeId);

        $imageResized = $magentoProduct->getThumbnailImage();
        if (is_null($imageResized)) {
            return $withoutImageHtml;
        }

        $imageHtml = $productId.'<hr/><img style="max-width: 100px; max-height: 100px;" src="'.
            $imageResized->getUrl().'" />';
        $withImageHtml = str_replace('>'.$productId.'<','>'.$imageHtml.'<',$withoutImageHtml);

        return $withImageHtml;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        $title       = $row->getData('name');
        $onlineTitle = $row->getData('online_title');

        !empty($onlineTitle) && $title = $onlineTitle;
        $value = '<span>' . Mage::helper('M2ePro')->escapeHtml($title) . '</span>';

        $additionalHtml = $this->getColumnProductTitleAdditionalHtml($row);

        if (!empty($additionalHtml)) {
            $value .= '<br/><hr style="border: none; border-top: 1px solid silver; margin: 2px 0px;"/>' .
                      $additionalHtml;
        }

        return $value;
    }

    public function callbackColumnEbayItemId($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $url = $this->getUrl(
            '*/adminhtml_ebay_listing/gotoEbay/',
            array(
                'item_id'        => $row->getData('item_id'),
                'account_id'     => $row->getData('account_id'),
                'marketplace_id' => $row->getData('marketplace_id'),
            )
        );

        return '<a href="'. $url . '" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnOnlineAvailableQty($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        if ($row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
            return '<span style="color: gray; text-decoration: line-through;">' . $value . '</span>';
        }

        return $value;
    }

    public function callbackColumnOnlineQtySold($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $onlineStartPrice   = $row->getData('online_start_price');
        $onlineCurrentPrice = $row->getData('online_current_price');

        if (is_null($onlineCurrentPrice) || $onlineCurrentPrice === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$onlineCurrentPrice <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        $currency = $row->getCurrency();

        if (strpos($currency, ',') !== false) {
            $currency = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Marketplace',$row->getMarketplaceId())
                ->getChildObject()->getCurrency();
        }

        if (!empty($onlineStartPrice)) {

            $onlineReservePrice  = $row->getData('online_reserve_price');
            $onlineBuyItNowPrice = $row->getData('online_buyitnow_price');

            $onlineStartStr = Mage::app()->getLocale()->currency($currency)->toCurrency($onlineStartPrice);

            $startPriceText = Mage::helper('M2ePro')->__('Start Price');

            $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
            $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');
            $onlineCurrentPriceHtml  = '';
            $onlineReservePriceHtml  = '';
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

            return $resultHtml;
        }

        $noticeHtml = '';
        if ($listingProductId = $row->getData('listing_product_id')) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);
            if ($listingProduct->getChildObject()->isVariationsReady()) {

                $iconHelpPath    = $this->getSkinUrl('M2ePro/images/i_logo.png');
                $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');

                $noticeText = Mage::helper('M2ePro')->__(
                    'The value is calculated as minimum price of all Child Products.'
                );

                $noticeHtml = <<<HTML
<img class="tool-tip-image" style="vertical-align: middle;" src="{$toolTipIconPath}">
<span class="tool-tip-message" style="display:none; text-align: left; width: 110px; background: #E3E3E3;">
    <img src="{$iconHelpPath}">
    <span style="color:gray;">
        {$noticeText}
    </span>
</span>
&nbsp;
HTML;
            }
        }

        return $noticeHtml .
               '<span class="product-price-value">' .
                     Mage::app()->getLocale()->currency($currency)->toCurrency($onlineCurrentPrice) .
               '</span>';
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                $value = '<span style="color: brown;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                $value = '<span style="color: blue;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $value = '<span style="color: orange;">'.$value.'</span>';
                break;

            default:
                break;
        }

        $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));
        if ($row->getData('is_duplicate') && isset($additionalData['item_duplicate_action_required'])) {

            $linkContent = Mage::helper('M2ePro')->__('duplicate');

            $value .= <<<HTML
<div style="float: right; clear: both;">
   <span style="color: #ea7601;">{$linkContent}</span>
    &nbsp;
    <img style="vertical-align: middle;" src="{$this->getSkinUrl('M2ePro/images/warning.png')}">
</div>
<br>
HTML;
        }

        $scheduledActionsCollection = Mage::getResourceModel('M2ePro/Listing_Product_ScheduledAction_Collection');
        $scheduledActionsCollection->addFieldToFilter('listing_product_id', $row->getData('id'));

        /** @var Ess_M2ePro_Model_Listing_Product_ScheduledAction $scheduledAction */
        $scheduledAction = $scheduledActionsCollection->getFirstItem();

        switch ($scheduledAction->getActionType()) {

            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                $value .= '<br/><span style="color: #605fff">[List is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                $value .= '<br/><span style="color: #605fff">[Relist is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:

                $reviseParts = array();

                $additionalData = $scheduledAction->getAdditionalData();
                if (!empty($additionalData['configurator'])) {
                    $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
                    $configurator->setData($additionalData['configurator']);

                    if ($configurator->isIncludingMode()) {
                        if ($configurator->isQtyAllowed()) {
                            $reviseParts[] = 'Qty';
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
                    $value .= '<br/><span style="color: #605fff">[Revise of '.implode(', ', $reviseParts)
                              .' is Scheduled...]</span>';
                } else {
                    $value .= '<br/><span style="color: #605fff">[Revise is Scheduled...]</span>';
                }

                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                $value .= '<br/><span style="color: #605fff">[Stop is Scheduled...]</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                $value .= '<br/><span style="color: #605fff">[Delete is Scheduled...]</span>';
                break;

            default:
                break;

        }

        /** @var Ess_M2ePro_Model_Processing_Lock[] $processingLocks */
        $processingLocks = $this->getProcessingLocks($row);
        if (empty($processingLocks)) {
            return $value;
        }

        foreach ($processingLocks as $lock) {

            switch ($lock->getTag()) {

                case 'list_action':
                    $value .= '<br/><span style="color: #605fff">[List in Progress...]</span>';
                    break;

                case 'relist_action':
                    $value .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $value .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $value .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $value .= '<br/><span style="color: #605fff">[Stop And Remove in Progress...]</span>';
                    break;

                default:
                    break;
            }
        }

        return $value;
    }

    //----------------------------------------

    protected function getColumnProductTitleAdditionalHtml($row)
    {
        return '';
    }

    //########################################

    abstract function callbackColumnActions($value, $row, $column, $isExport);

    /** @return array() */
    protected abstract function getProcessingLocks($row);

    //########################################

    abstract protected function callbackFilterProductId($collection, $column);
    abstract protected function callbackFilterTitle($collection, $column);
    abstract protected function callbackFilterPrice($collection, $column);
    abstract protected function callbackFilterOnlineQty($collection, $column);
    abstract protected function callbackFilterStatus($collection, $column);
    abstract protected function callbackFilterItemId($collection, $column);

    //########################################

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/searchGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}