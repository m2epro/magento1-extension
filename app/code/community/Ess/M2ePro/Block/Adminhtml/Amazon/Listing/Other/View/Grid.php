<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Other_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    const ACTUAL_QTY_EXPRESSION =
        'IF(second_table.is_afn_channel = 1, second_table.online_afn_qty, second_table.online_qty)';

    public function __construct()
    {
        parent::__construct();

        /** @var $this->connRead Varien_Db_Adapter_Pdo_Mysql */
        $this->connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $this->setId('amazonListingOtherGrid');

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

        $collection->getSelect()->joinLeft(
            array('mp' => Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
            'mp.id = main_table.marketplace_id',
            array('marketplace_title' => 'mp.title')
        )->joinLeft(
            array('am' => Mage::getResourceModel('M2ePro/Amazon_Marketplace')->getMainTable()),
            'am.marketplace_id = main_table.marketplace_id',
            array('currency' => 'am.default_currency')
        );

        // Add Filter By Account
        if ($this->getRequest()->getParam('account')) {
            $collection->addFieldToFilter(
                'main_table.account_id',
                $this->getRequest()->getParam('account')
            );
        }

        // Add Filter By Marketplace
        if ($this->getRequest()->getParam('marketplace')) {
            $collection->addFieldToFilter(
                'main_table.marketplace_id',
                $this->getRequest()->getParam('marketplace')
            );
        }

        //@codingStandardsIgnoreLine
        $collection->getSelect()->columns(
            array('online_actual_qty' => self::ACTUAL_QTY_EXPRESSION)
        );

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Product ID'),
                'align'                     => 'left',
                'width'                     => '80px',
                'type'                      => 'number',
                'index'                     => 'product_id',
                'filter_index'              => 'product_id',
                'frame_callback'            => array($this, 'callbackColumnProductId'),
                'filter'                    => 'M2ePro/adminhtml_grid_column_filter_productId',
                'filter_condition_callback' => array($this, 'callbackFilterProductId')
            )
        );

        $this->addColumn(
            'title',
            array(
                'header'                    => Mage::helper('M2ePro')->__('Title / SKU'),
                'align'                     => 'left',
                'type'                      => 'text',
                'index'                     => 'title',
                'filter_index'              => 'second_table.title',
                'frame_callback'            => array($this, 'callbackColumnProductTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'general_id',
            array(
                'header'         => Mage::helper('M2ePro')->__('ASIN / ISBN'),
                'align'          => 'left',
                'width'          => '120px',
                'type'           => 'text',
                'index'          => 'general_id',
                'filter_index'   => 'general_id',
                'frame_callback' => array($this, 'callbackColumnGeneralId')
            )
        );

        $this->addColumn(
            'online_qty',
            array(
                'header'                    => Mage::helper('M2ePro')->__('QTY'),
                'align'                     => 'right',
                'width'                     => '110px',
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
            'width' => '100px',
            'type' => 'number',
            'index' => 'online_price',
            'filter_index' => 'online_price',
            'frame_callback' => array($this, 'callbackColumnPrice'),
            'filter_condition_callback' => array($this, 'callbackFilterPrice'),
            'filter' => 'M2ePro/adminhtml_amazon_grid_column_filter_price',
        );

        $this->addColumn('online_price', $priceColumn);

        $this->addColumn(
            'status',
            array(
                'header'         => Mage::helper('M2ePro')->__('Status'),
                'width'          => '100px',
                'index'          => 'status',
                'filter_index'   => 'main_table.status',
                'type'           => 'options',
                'sortable'       => false,
                'options'        => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN => Mage::helper('M2ePro')->__('Unknown'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED  => Mage::helper('M2ePro')->__('Active'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => Mage::helper('M2ePro')->__('Inactive'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED => Mage::helper('M2ePro')
                        ->__('Incomplete')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set mass-action identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $this->getMassactionBlock()->setGroups(
            array(
                'mapping' => Mage::helper('M2ePro')->__('Linking'),
                'other'   => Mage::helper('M2ePro')->__('Other')
            )
        );

        // Set mass-action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'autoMapping',
            array(
                'label'   => Mage::helper('M2ePro')->__('Link Item(s) Automatically'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'mapping'
        );
        $this->getMassactionBlock()->addItem(
            'moving',
            array(
                'label'   => Mage::helper('M2ePro')->__('Move Item(s) to Listing'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'other'
        );
        $this->getMassactionBlock()->addItem(
            'removing',
            array(
                'label'   => Mage::helper('M2ePro')->__('Remove Item(s)'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'other'
        );
        $this->getMassactionBlock()->addItem(
            'unmapping',
            array(
                'label'   => Mage::helper('M2ePro')->__('Unlink Item(s)'),
                'url'     => '',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ),
            'mapping'
        );

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('title'));
            $productTitle = Mage::helper('M2ePro')->escapeJs($productTitle);
            if (strlen($productTitle) > 60) {
                $productTitle = substr($productTitle, 0, 60) . '...';
            }

            $htmlValue = '&nbsp;<a href="javascript:void(0);"
                                    onclick="ListingOtherMappingObj.openPopUp(
                                    ' . (int)$row->getId() . ',
                                    \'' . $productTitle . '\'
                                    );">' . Mage::helper('M2ePro')->__('Link') . '</a>';

            return $htmlValue;
        }

        $htmlValue = '&nbsp<a href="'
            . $this->getUrl(
                'adminhtml/catalog_product/edit',
                array('id' => $row->getData('product_id'))
            )
            . '" target="_blank">'
            . $row->getData('product_id')
            . '</a>';

        $htmlValue .= '&nbsp&nbsp&nbsp<a href="javascript:void(0);"'
            . ' onclick="AmazonListingOtherGridObj.movingHandler.getGridHtml('
            . Mage::helper('M2ePro')->jsonEncode(array((int)$row->getData('id')))
            . ')">'
            . Mage::helper('M2ePro')->__('Move')
            . '</a>';

        return $htmlValue;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        if ($value === null) {
            $value = '<i style="color:gray;">receiving...</i>';
        } else {
            $value = '<span>' . Mage::helper('M2ePro')->escapeHtml($value) . '</span>';
        }

        $tempSku = $row->getData('sku');
        empty($tempSku) && $tempSku = Mage::helper('M2ePro')->__('N/A');

        $value .= '<br/><strong>'
            . Mage::helper('M2ePro')->__('SKU')
            . ':</strong> '
            . Mage::helper('M2ePro')->escapeHtml($tempSku);

        return $value;
    }

    public function callbackColumnGeneralId($value, $row, $column, $isExport)
    {
        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl($value, $row->getData('marketplace_id'));

        return '<a href="' . $url . '" target="_blank">' . $value . '</a>';
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($row->getData('is_afn_channel')) {
            $qty = $row->getData('online_afn_qty');
            $qty = $qty !== null ? $qty : Mage::helper('M2ePro')->__('N/A');
            return "AFN ($qty)";
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
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $html = '';

        if ((int)$row->getData('is_repricing') == 1) {
            $image = 'money';
            $text = Mage::helper('M2ePro')->__(
                'This Product is used by Amazon Repricing Tool, so its Price cannot be managed via M2E Pro. <br>
                 <strong>Please note</strong> that the Price value(s) shown in the grid might
                 be different from the actual one from Amazon. It is caused by the delay
                 in the values updating made via the Repricing Service'
            );

            if ((int)$row->getData('is_repricing_disabled') == 1 || (int)$row->getData('is_repricing_inactive') == 1) {
                $image = 'money_disabled';
                $text = Mage::helper('M2ePro')->__(
                    'This Item is disabled or unable to be repriced on Amazon Repricing Tool.
                    You can map it to Magento Product and Move into M2E Pro Listing for being updated via M2E Pro.'
                );
            }

            $html = <<<HTML
<span style="float:right; text-align: left;">&nbsp;
    <img class="tool-tip-image"
         style="vertical-align: middle; width: 16px;"
         src="{$this->getSkinUrl('M2ePro/images/' . $image . '.png')}">
    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
        <img src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
        <span>{$text}</span>
    </span>
</span>
HTML;
        }

        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A') . $html;
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>' . $html;
        }

        $currency = Mage::helper('M2ePro/Component_Amazon')
            ->getCachedObject('Marketplace', $row->getData('marketplace_id'))
            ->getChildObject()
            ->getDefaultCurrency();

        $priceValue = Mage::app()->getLocale()->currency($currency)->toCurrency($value);

        if ($row->getData('is_repricing') &&
            !$row->getData('is_repricing_disabled') &&
            $row->getData('is_repricing_inactive')
        ) {
            $accountId = $row->getData('account_id');
            $sku = $row->getData('sku');

            $priceValue = <<<HTML
<a id="m2epro_repricing_price_value_{$sku}"
   class="m2epro-repricing-price-value"
   sku="{$sku}"
   account_id="{$accountId}"
   href="javascript:void(0)"
   onclick="AmazonListingRepricingPriceObj.showRepricingPrice()">
    {$priceValue}</a>
HTML;
        }

        return $priceValue . $html;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN:
                $value = '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $value = '<span style="color: red;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $value = '<span style="color: orange; font-weight: bold;">' . $value . '</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    //########################################

    protected function callbackFilterProductId($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= 'product_id >= ' . (int)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= 'product_id <= ' . (int)$value['to'];
        }

        if (isset($value['is_mapped']) && $value['is_mapped'] !== '') {
            if (!empty($where)) {
                $where = '(' . $where . ') AND ';
            }

            if ($value['is_mapped']) {
                $where .= 'product_id IS NOT NULL';
            } else {
                $where .= 'product_id IS NULL';
            }
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('second_table.title LIKE ? OR second_table.sku LIKE ?', '%' . $value . '%');
    }

    protected function callbackFilterQty($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= self::ACTUAL_QTY_EXPRESSION . ' >= ' . (int)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= self::ACTUAL_QTY_EXPRESSION . ' <= ' . (int)$value['to'];
        }

        if (isset($value['afn']) && $value['afn'] !== '') {
            if (!empty($where)) {
                $where .= ' AND ';
            }

            $where .= 'is_afn_channel = ' . (int)$value['afn'];
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= 'online_price >= ' . (float)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= 'online_price <= ' . (float)$value['to'];
        }

        if (isset($value['is_repricing']) && $value['is_repricing'] !== '') {
            if (!empty($where)) {
                $where = '(' . $where . ') OR ';
            }

            $where .= 'is_repricing = ' . (int)$value['is_repricing'];
        }

        $collection->getSelect()->where($where);
    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof AmazonListingOtherGridObj != 'undefined') {
        AmazonListingOtherGridObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            AmazonListingOtherGridObj.afterInitPage();
        }, 350);
    });

</script>
HTML;

        return parent::_toHtml() . $javascriptsMain;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_listing_other/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}
