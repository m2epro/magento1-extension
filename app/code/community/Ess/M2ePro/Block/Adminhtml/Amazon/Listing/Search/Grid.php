<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id', array(
                'header'                    => Mage::helper('M2ePro')->__('Product ID'),
                'align'                     => 'right',
                'width'                     => '100px',
                'type'                      => 'number',
                'index'                     => 'entity_id',
                'filter_index'              => 'entity_id',
                'renderer'                  => 'M2ePro/adminhtml_grid_column_renderer_productId',
                'filter_condition_callback' => array($this, 'callbackFilterProductId')
            )
        );

        $this->addColumn(
            'name', array(
                'header'                    => Mage::helper('M2ePro')->__('Product Title / Listing / Product SKU'),
                'align'                     => 'left',
                'type'                      => 'text',
                'index'                     => 'name',
                'filter_index'              => 'name',
                'frame_callback'            => array($this, 'callbackColumnProductTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'is_in_stock', array(
                'header'         => Mage::helper('M2ePro')->__('Stock Availability'),
                'align'          => 'left',
                'width'          => '90px',
                'type'           => 'options',
                'sortable'       => false,
                'index'          => 'is_in_stock',
                'filter_index'   => 'is_in_stock',
                'options'        => array(
                    '1' => Mage::helper('M2ePro')->__('In Stock'),
                    '0' => Mage::helper('M2ePro')->__('Out of Stock')
                ),
                'frame_callback' => array($this, 'callbackColumnIsInStock')
            )
        );

        $this->addColumn(
            'online_sku', array(
                'header'                    => Mage::helper('M2ePro')->__('SKU'),
                'align'                     => 'left',
                'width'                     => '150px',
                'type'                      => 'text',
                'index'                     => 'online_sku',
                'filter_index'              => 'online_sku',
                'show_defected_messages'    => false,
                'renderer'                  => 'M2ePro/adminhtml_amazon_grid_column_renderer_sku',
                'filter_condition_callback' => array($this, 'callbackFilterOnlineSku')
            )
        );

        $this->addColumn(
            'general_id', array(
                'header'         => Mage::helper('M2ePro')->__('ASIN / ISBN'),
                'align'          => 'left',
                'width'          => '100px',
                'type'           => 'text',
                'index'          => 'general_id',
                'filter_index'   => 'general_id',
                'frame_callback' => array($this, 'callbackColumnGeneralId'),
                'filter_condition_callback' => array($this, 'callbackFilterAsinIsbn')
            )
        );

        $this->addColumn(
            'online_qty', array(
                'header'                    => Mage::helper('M2ePro')->__('QTY'),
                'align'                     => 'right',
                'width'                     => '70px',
                'type'                      => 'number',
                'index'                     => 'online_actual_qty',
                'filter_index'              => 'online_actual_qty',
                'frame_callback'            => array($this, 'callbackColumnAvailableQty'),
                'filter'                    => 'M2ePro/adminhtml_amazon_grid_column_filter_qty',
                'filter_condition_callback' => array($this, 'callbackFilterQty')
            )
        );

        $priceColumn = array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '110px',
            'type' => 'number',
            'index' => 'online_current_price',
            'filter_index' => 'online_current_price',
            'frame_callback' => array($this, 'callbackColumnPrice'),
            'filter_condition_callback' => array($this, 'callbackFilterPrice'),
            'filter' => 'M2ePro/adminhtml_amazon_grid_column_filter_price',
        );

        $this->addColumn('online_price', $priceColumn);

        $statusColumn = array(
            'header'       => Mage::helper('M2ePro')->__('Status'),
            'width'        => '125px',
            'index'        => 'status',
            'filter_index' => 'status',
            'type'         => 'options',
            'sortable'     => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN    => Mage::helper('M2ePro')->__('Unknown'),
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED    => Mage::helper('M2ePro')->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Incomplete')
            ),
            'frame_callback'            => array($this, 'callbackColumnStatus'),
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        );

        $listingType = $this->getRequest()->getParam(
            'listing_type', Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_M2E_PRO
        );

        if ($listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_LISTING_OTHER) {
            unset($statusColumn['options'][Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED]);
        }

        $this->addColumn('status', $statusColumn);

        $this->addColumn(
            'goto_listing_item', array(
                'header'         => Mage::helper('M2ePro')->__('Manage'),
                'align'          => 'center',
                'width'          => '80px',
                'type'           => 'text',
                'filter'         => false,
                'sortable'       => false,
                'frame_callback' => array($this, 'callbackColumnActions')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnGeneralId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            if ((int)$row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                return '<i style="color:gray;">'.Mage::helper('M2ePro')->__('receiving...').'</i>';
            }

            if ($row->getData('is_general_id_owner')) {
                return Mage::helper('M2ePro')->__('New ASIN/ISBN');
            }

            return Mage::helper('M2ePro')->__('N/A');
        }

        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl($value, $row->getData('marketplace_id'));
        return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if ($row->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if (!$row->getData('is_variation_parent')) {
            if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
            }

            if ($row->getData('is_afn_channel')) {
                $qty = $row->getData('online_afn_qty');
                $qty = $qty !== null ? $qty : Mage::helper('M2ePro')->__('N/A');
                return "AFN ($qty)";
            }

            if ($value === null || $value === '') {
                return '<i style="color:gray;">receiving...</i>';
            }

            if ($value <= 0) {
                return '<span style="color: red;">0</span>';
            }

            return $value;
        }

        if ($row->getData('general_id') == '') {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $variationChildStatuses = Mage::helper('M2ePro')->jsonDecode($row->getData('variation_child_statuses'));

        if (empty($variationChildStatuses)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $activeChildrenCount = 0;
        foreach ($variationChildStatuses as $childStatus => $count) {
            if ($childStatus == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                continue;
            }

            $activeChildrenCount += (int)$count;
        }

        if ($activeChildrenCount == 0) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if (!(bool)$row->getData('is_afn_channel')) {
            return $value;
        }

        $resultValue = Mage::helper('M2ePro')->__('AFN');
        $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

        if (!empty($additionalData['afn_count'])) {
            $resultValue = $resultValue."&nbsp;[".$additionalData['afn_count']."]";
        }

        return <<<HTML
<div>{$value}</div>
<div>{$resultValue}</div>
HTML;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((!$row->getData('is_variation_parent') &&
            $row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) ||
            ($row->getData('is_variation_parent') && $row->getData('general_id') == '')) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        $repricingHtml = '';

        if ($row->getData('is_repricing')) {
            if ($row->getData('is_variation_parent')) {
                $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

                $repricingManagedCount = isset($additionalData['repricing_managed_count'])
                    ? $additionalData['repricing_managed_count'] : null;

                $repricingNotManagedCount = isset($additionalData['repricing_not_managed_count'])
                    ? $additionalData['repricing_not_managed_count'] : null;

                if ($repricingManagedCount && $repricingNotManagedCount) {
                    $image = 'money_mixed';
                    $countHtml = '['.$repricingManagedCount.'/'.$repricingNotManagedCount.']';
                    $text = Mage::helper('M2ePro')->__(
                        'Some Child Products of this Parent ASIN are disabled or unable to be repriced
                        on Amazon Repricing Tool.<br>
                        <strong>Note:</strong> the Price values shown in the grid may differ from Amazon ones.
                        It is caused by some delay in data synchronization between M2E Pro and Repricing Tool.'
                    );
                } elseif ($repricingManagedCount) {
                    $image = 'money';
                    $countHtml = '['.$repricingManagedCount.']';
                    $text = Mage::helper('M2ePro')->__(
                        'All Child Products of this Parent are Enabled for dynamic repricing. <br>
                        <strong>Please note</strong> that the Price value(s) shown in the grid might be different
                        from the actual one from Amazon. It is caused by the delay in the values updating
                        made via the Repricing Service.'
                    );
                } elseif ($repricingNotManagedCount) {
                    $image = 'money_disabled';
                    $countHtml = '['.$repricingNotManagedCount.']';
                    $text = Mage::helper('M2ePro')->__(
                        'The Child Products of this Parent ASIN are disabled or unable
                        to be repriced on Amazon Repricing Tool.'
                    );
                } else {
                    $image = 'money';
                    $countHtml = Mage::helper('M2ePro')->__('[-/-]');
                    $text = Mage::helper('M2ePro')->__(
                        'Some Child Products of this Parent are managed by the Repricing Service. <br>
                        <strong>Please note</strong> that the Price value(s) shown in the grid might be
                        different from the actual one from Amazon. It is caused by the delay in the
                        values updating made via the Repricing Service.'
                    );
                }

                $repricingHtml = <<<HTML
<br/><span style="float:right; text-align: left;">
    <img class="tool-tip-image"
         style="vertical-align: middle; width: 16px;"
         src="{$this->getSkinUrl('M2ePro/images/'.$image.'.png')}">
    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
        <span>{$text}</span>
    </span>
    &nbsp;$countHtml&nbsp;
</span>
HTML;
            } elseif (!$row->getData('is_variation_parent')) {
                $image = 'money';
                $text = Mage::helper('M2ePro')->__(
                    'This Product is used by Amazon Repricing Tool, so its Price cannot be managed via M2E Pro.<br>
                    <strong>Please note</strong> that the Price value shown in the grid might be different
                    from the actual one from Amazon. It is caused by the delay in the values
                    updating made via the Repricing Service.'
                );

                if ((int)$row->getData('is_repricing_disabled') == 1 ||
                    (int)$row->getData('is_repricing_inactive') == 1
                ) {
                    $image = 'money_disabled';

                    if ($this->getId() == 'amazonListingSearchOtherGrid') {
                        $text = Mage::helper('M2ePro')->__(
                            'This product is disabled on Amazon Repricing Tool. <br>
                            You can map it to Magento Product and Move into M2E Pro Listing to make the
                            Price being updated via M2E Pro.'
                        );
                    } else {
                        $text = Mage::helper('M2ePro')->__(
                            'This product is disabled on Amazon Repricing Tool.
                            The Price is updated through the M2E Pro.'
                        );
                    }
                }

                $repricingHtml = <<<HTML
<span style="float:right; text-align: left;">&nbsp;
    <img class="tool-tip-image"
         style="vertical-align: middle; width: 16px;"
         src="{$this->getSkinUrl('M2ePro/images/'.$image.'.png')}">
    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
        <span>{$text}</span>
    </span>
</span>
HTML;
            }
        }

        $currentOnlinePrice = (float)$row->getData('online_current_price');
        $onlineBusinessPrice = (float)$row->getData('online_business_price');

        if (empty($currentOnlinePrice) && empty($onlineBusinessPrice)) {
            if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED ||
                $row->getData('is_variation_parent')
            ) {
                return Mage::helper('M2ePro')->__('N/A') . $repricingHtml;
            } else {
                return '<i style="color:gray;">receiving...</i>' . $repricingHtml;
            }
        }

        $marketplaceId = $row->getData('marketplace_id');
        $currency = Mage::helper('M2ePro/Component_Amazon')
            ->getCachedObject('Marketplace', $marketplaceId)
            ->getChildObject()
            ->getDefaultCurrency();

        if ($row->getData('is_variation_parent')) {
            $iconHelpPath    = $this->getSkinUrl('M2ePro/images/i_logo.png');
            $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');

            $noticeText = Mage::helper('M2ePro')->__('The value is calculated as minimum price of all Child Products.');

            $priceHtml = <<<HTML
<img class="tool-tip-image" style="vertical-align: middle;" src="{$toolTipIconPath}"
    >&nbsp;<span class="tool-tip-message" style="display:none; text-align: left; width: 110px; background: #E3E3E3;">
    <img src="{$iconHelpPath}">
    <span style="color:gray;">
        {$noticeText}
    </span>
</span>
HTML;

            if (!empty($currentOnlinePrice)) {
                $currentOnlinePrice = Mage::app()->getLocale()->currency($currency)->toCurrency($currentOnlinePrice);
                $priceHtml .= "<span>{$currentOnlinePrice}</span><br />";
            }

            if (!empty($onlineBusinessPrice)) {
                $priceHtml .= '<strong>B2B:</strong> '
                              .Mage::app()->getLocale()->currency($currency)->toCurrency($onlineBusinessPrice);
            }

            return $priceHtml .
                   $repricingHtml;
        }

        $onlinePrice = $row->getData('online_regular_price');
        if ((float)$onlinePrice <= 0) {
            $priceValue = '<span style="color: #f00;">0</span>';
        } else {
            $priceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($onlinePrice);
        }

        if ($row->getData('is_repricing') &&
            !$row->getData('is_repricing_disabled') &&
            !$row->getData('is_repricing_inactive') &&
            !$row->getData('is_variation_parent')
        ) {
            $accountId = $row->getData('account_id');
            $sku = $row->getData('online_sku');

            $priceValue =<<<HTML
<a id="m2epro_repricing_price_value_{$sku}"
   class="m2epro-repricing-price-value"
   sku="{$sku}"
   account_id="{$accountId}"
   href="javascript:void(0)"
   onclick="AmazonListingRepricingPriceObj.showRepricingPrice()">
    {$priceValue}</a>
HTML;
        }

        $resultHtml = '';

        $salePrice = $row->getData('online_regular_sale_price');
        if (!$row->getData('is_variation_parent') && (float)$salePrice > 0 && !$row->getData('is_repricing')) {
            /** @var Ess_M2ePro_Helper_Data $helper */
            $helper = Mage::helper('M2ePro');
            $currentTimestamp = (int)$helper->createGmtDateTime(
                $helper->getCurrentGmtDate(false, 'Y-m-d 00:00:00')
            )->format('U');

            $startDateTimestamp = (int)$helper->createGmtDateTime($row->getData('online_regular_sale_price_start_date'))
                ->format('U');
            $endDateTimestamp = (int)$helper->createGmtDateTime($row->getData('online_regular_sale_price_end_date'))
                ->format('U');

            if ($currentTimestamp <= $endDateTimestamp) {
                $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
                $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');

                $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

                $fromDate = Mage::app()->getLocale()->date(
                    $row->getData('online_regular_sale_price_start_date'), $dateFormat
                )->toString($dateFormat);
                $toDate = Mage::app()->getLocale()->date(
                    $row->getData('online_regular_sale_price_end_date'), $dateFormat
                )->toString($dateFormat);

                $intervalHtml = '<img class="tool-tip-image"
                                 style="vertical-align: middle;"
                                 src="'.$toolTipIconPath.'"><span class="tool-tip-message" style="display:none;
                                                                  text-align: left;
                                                                  width: 110px;
                                                                  background: #E3E3E3;">
                                <img src="'.$iconHelpPath.'">
                                <span style="color:gray;">
                                    <strong>From:</strong> '.$fromDate.'<br/>
                                    <strong>To:</strong> '.$toDate.'
                                </span>
                            </span>';

                $salePriceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($salePrice);

                if ($currentTimestamp >= $startDateTimestamp &&
                    $currentTimestamp <= $endDateTimestamp &&
                    $salePrice < (float)$onlinePrice
                ) {
                    $resultHtml .= '<span style="color: grey; text-decoration: line-through;">'.$priceValue.'</span>' .
                                    $repricingHtml;
                    $resultHtml .= '<br/>'.$intervalHtml.'&nbsp;'.$salePriceValue;
                } else {
                    $resultHtml .= $priceValue . $repricingHtml;
                    $resultHtml .= '<br/>'.$intervalHtml.
                        '<span style="color:gray;">'.'&nbsp;'.$salePriceValue.'</span>';
                }
            }
        }

        if (empty($resultHtml)) {
            $resultHtml = $priceValue . $repricingHtml;
        }

        if ((float)$onlineBusinessPrice > 0) {
            $businessPriceValue = '<strong>B2B:</strong> '
                                  .Mage::app()->getLocale()->currency($currency)->toCurrency($onlineBusinessPrice);

            $businessDiscounts = $row->getData('online_business_discounts');
            if (!empty($businessDiscounts) && $businessDiscounts = json_decode($businessDiscounts, true)) {
                $iconHelpPath = $this->getSkinUrl('M2ePro/images/i_logo.png');
                $toolTipIconPath = $this->getSkinUrl('M2ePro/images/i_icon.png');

                $discountsHtml = '';

                foreach ($businessDiscounts as $qty => $price) {
                    $price = Mage::app()->getLocale()->currency($currency)->toCurrency($price);
                    $discountsHtml .= 'QTY >= '.(int)$qty.', price '.$price.'<br />';
                }

                $discountsHtml = '<img class="tool-tip-image"
                                 style="vertical-align: middle;"
                                 src="'.$toolTipIconPath.'"><span class="tool-tip-message" style="display:none;
                                                                  text-align: left;
                                                                  width: 150px;
                                                                  background: #E3E3E3;">
                                <img src="'.$iconHelpPath.'">
                                <span style="color:gray;">
                                    '.$discountsHtml.'
                                </span>
                            </span>';

                $businessPriceValue .= $discountsHtml;
            }

            if (!empty($resultHtml)) {
                $businessPriceValue = '<br />'.$businessPriceValue;
            }

            $resultHtml .= $businessPriceValue;
        }

        return $resultHtml;
    }

    //----------------------------------------

    abstract public function callbackColumnProductTitle($value, $row, $column, $isExport);
    abstract public function callbackColumnStatus($value, $row, $column, $isExport);
    abstract public function callbackColumnActions($value, $row, $column, $isExport);

    //----------------------------------------

    protected function getProductStatus($status)
    {
        switch ($status) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Unknown') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                return '<span style="color: green;">' . Mage::helper('M2ePro')->__('Active') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                return'<span style="color: red;">' . Mage::helper('M2ePro')->__('Inactive') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                return'<span style="color: orange; font-weight: bold;">' .
                Mage::helper('M2ePro')->__('Incomplete') . '</span>';
        }

        return '';
    }

    //########################################

    abstract protected function callbackFilterProductId($collection, $column);
    abstract protected function callbackFilterTitle($collection, $column);
    abstract protected function callbackFilterOnlineSku($collection, $column);
    abstract protected function callbackFilterAsinIsbn($collection, $column);
    abstract protected function callbackFilterQty($collection, $column);
    abstract protected function callbackFilterPrice($collection, $column);
    abstract protected function callbackFilterStatus($collection, $column);

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
        return $this->getUrl('*/adminhtml_amazon_listing/searchGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * @param string $value
     * @return string
     */
    public function getValueForSubQuery($value)
    {
        // Mage/Eav/Model/Entity/Collection/Abstract.php:765
        if (empty($value) || strpos($value, '/') === false) {
            return $value;
        }

        return substr($value, 0, strpos($value, '/'));
    }

    //########################################

    protected function _toHtml()
    {
        $getUpdatedRepricingPriceBySkus = $this->getUrl(
            '*/adminhtml_amazon_listing_repricing/getUpdatedPriceBySkus'
        );

        $js = <<<HTML
<script type="text/javascript">
    M2ePro.url.getUpdatedRepricingPriceBySkus = '{$getUpdatedRepricingPriceBySkus}';

    AmazonListingRepricingPriceObj = new AmazonListingRepricingPrice();
</script>
HTML;

        return parent::_toHtml() . $js;
    }

    protected function isFilterOrSortByPriceIsUsed($filterName = null, $advancedFilterName = null)
    {
        if ($filterName) {
            $filters = $this->getParam($this->getVarNameFilter());
            is_string($filters) && $filters = $this->helper('adminhtml')->prepareFilterString($filters);

            if (is_array($filters) && array_key_exists($filterName, $filters)) {
                return true;
            }

            $sort = $this->getParam($this->getVarNameSort());
            if ($sort == $filterName) {
                return true;
            }
        }

        return false;
    }

    //########################################
}
