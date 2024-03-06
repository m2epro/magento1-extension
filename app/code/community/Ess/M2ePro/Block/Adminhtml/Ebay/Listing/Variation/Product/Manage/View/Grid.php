<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Adminhtml_Ebay_Listing_Variation_Product_ManageController as ManageController;
use Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_Qty as OnlineQty;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Variation_Product_Manage_View_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_variationAttributes;

    protected $_listingProductId;

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    protected $_identifiers = array(
        'upc'  => 'UPC',
        'ean'  => 'EAN',
        'isbn' => 'ISBN',
        'mpn'  => 'MPN',
        'epid' => 'ePID'
    );

    //########################################

    /**
     * @param mixed $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->_listingProductId = $listingProductId;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->_listingProductId;
    }

    // ---------------------------------------

    protected function getListingProduct()
    {
        if (empty($this->_listingProduct)) {
            $this->_listingProduct = Mage::helper('M2ePro/Component_Ebay')
                                         ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayVariationProductManageGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Ebay_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product_Variation');
        $collection->getSelect()->where('main_table.listing_product_id = ?', (int)$this->getListingProductId());
        $collection->getSelect()->group('main_table.id');

        $collection->getSelect()->join(
            array('mlpvo' => Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable()),
            '`mlpvo`.`listing_product_variation_id`=`main_table`.`id`',
            array()
        );

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'id'                 => 'main_table.id',
                'listing_product_id' => 'main_table.listing_product_id',
                'additional_data'    => 'main_table.additional_data',
                'add'                => 'second_table.add',
                'delete'             => 'second_table.delete',
                'online_price'       => 'second_table.online_price',
                'online_sku'         => 'second_table.online_sku',
                'available_qty'      => new Zend_Db_Expr('(second_table.online_qty - second_table.online_qty_sold)'),
                'online_qty_sold'    => 'second_table.online_qty_sold',
                'status'             => 'second_table.status',
                'attributes'       => 'GROUP_CONCAT(`mlpvo`.`attribute`, \'==\', `mlpvo`.`option` SEPARATOR \'||\')',
                'products_ids'     => 'GROUP_CONCAT(`mlpvo`.`attribute`, \'==\', `mlpvo`.`product_id` SEPARATOR \'||\')'
            )
        );

        $resultCollection = new Varien_Data_Collection_Db(Mage::getResourceModel('core/config')->getReadConnection());
        $resultCollection->getSelect()->reset()->from(
            array('main_table' => $collection->getSelect()),
            array(
                'id',
                'listing_product_id',
                'additional_data',
                'add',
                'delete',
                'online_price',
                'available_qty',
                'online_sku',
                'online_qty_sold',
                'status',
                'attributes',
                'products_ids'
            )
        );

        if ($this->getParam($this->getVarNameFilter()) == 'searched_by_child'){
            $collection->addFieldToFilter(
                'main_table.id',
                array('in' => explode(',', $this->getRequest()->getParam('variation_id_filter')))
            );
        }

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'variation', array(
            'header'   => Mage::helper('M2ePro')->__('Magento Variation'),
            'align'    => 'left',
            'width'    => '210px',
            'sortable' => false,
            'index'    => 'attributes',
            'filter_index'   => 'attributes',
            'frame_callback' => array($this, 'callbackColumnVariations'),
            'filter'  => 'M2ePro/adminhtml_grid_column_filter_attributesOptions',
            'options' => $this->getVariationsAttributes(),
            'filter_condition_callback' => array($this, 'callbackFilterVariations')
            )
        );

        $this->addColumn(
            'online_sku', array(
                'header'    => Mage::helper('M2ePro')->__('SKU'),
                'align'     => 'left',
                'width'     => '150px',
                'index'     => 'online_sku',
                'filter_index'   => 'online_sku',
                'frame_callback' => array($this, 'callbackColumnOnlineSku')
            )
        );

        $this->addColumn(
            'available_qty', array(
                'header'   => Mage::helper('M2ePro')->__('Available QTY'),
                'align'    => 'right',
                'width'    => '40px',
                'type'     => 'number',
                'index'    => 'available_qty',
                'sortable' => true,
                'filter'   => false,
                'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_qty',
                'render_online_qty' => OnlineQty::ONLINE_AVAILABLE_QTY
            )
        );

        $this->addColumn(
            'online_qty_sold', array(
                'header'   => Mage::helper('M2ePro')->__('Sold QTY'),
                'align'    => 'right',
                'width'    => '40px',
                'type'     => 'number',
                'index'    => 'online_qty_sold',
                'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_qty',
            )
        );

        $this->addColumn(
            'price', array(
                'header' => Mage::helper('M2ePro')->__('Price'),
                'align'  => 'right',
                'width'  => '40px',
                'type'   => 'number',
                'index'  => 'online_price',
                'filter_index'   => 'online_price',
                'frame_callback' => array($this, 'callbackColumnPrice'),
            )
        );

        $this->addColumn(
            'identifiers', array(
                'header'   => Mage::helper('M2ePro')->__('eBay Catalog Identifiers'),
                'align'    => 'left',
                'width'    => '150px',
                'sortable' => false,
                'index'    => 'additional_data',
                'filter_index' => 'additional_data',
                'filter'       => 'M2ePro/adminhtml_grid_column_filter_attributesOptions',
                'options'      => $this->_identifiers,
                'frame_callback' => array($this, 'callbackColumnIdentifiers'),
                'filter_condition_callback' => array($this, 'callbackFilterIdentifiers')
            )
        );

        $this->addColumn(
            'status', array(
            'header'=> Mage::helper('M2ePro')->__('Status'),
            'width' => '60px',
            'index' => 'status',
            'filter_index' => 'status',
            'type'     => 'options',
            'sortable' => false,
            'options'  => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN     => Mage::helper('M2ePro')->__('Hidden'),
                Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE   => Mage::helper('M2ePro')->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Pending')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnVariations($value, $row, $column, $isExport)
    {
        $attributes = $this->parseGroupedData($row->getData('attributes'));
        $productsIds = $this->parseGroupedData($row->getData('products_ids'));
        $uniqueProductsIds = count(array_unique($productsIds)) > 1;

        $html = '<div class="m2ePro-variation-attributes" style="margin-left: 5px;">';
        if (!$uniqueProductsIds) {
            $data['id'] = reset($productsIds);
            if ($this->getListingProduct()->getListing()->getStoreId() !== null) {
                $data['store'] = $this->getListingProduct()->getListing()->getStoreId();
            }
            $url = $this->getUrl('adminhtml/catalog_product/edit', $data);
            $html .= '<a href="' . $url . '" target="_blank">';
        }

        foreach ($attributes as $attribute => $option) {
            $optionHtml = '<b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option);

            if ($uniqueProductsIds) {
                $data['id'] = $productsIds[$attribute];
                if ($this->getListingProduct()->getListing()->getStoreId() !== null) {
                    $data['store'] = $this->getListingProduct()->getListing()->getStoreId();
                }
                $url = $this->getUrl('adminhtml/catalog_product/edit', $data);
                $html .= '<a href="' . $url . '" target="_blank">' . $optionHtml . '</a><br/>';
            } else {
                $html .= $optionHtml . '<br/>';
            }
        }

        if (!$uniqueProductsIds) {
            $html .= '</a>';
        }

        $html .= '</div>';

        return $html;
    }

    public function callbackColumnOnlineSku($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED &&
            ($value === null || $value === '')) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED &&
            ($value === null || $value === '')) {
            return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';
        }

        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        $currency = $this->getListingProduct()->getMarketplace()->getChildObject()->getCurrency();

        return Mage::app()->getLocale()->currency($currency)->toCurrency($value);
    }

    public function callbackColumnIdentifiers($value, $row, $column, $isExport)
    {
        $html = '';
        $formHtml = '';

        $variationId = $row->getData('id');
        $additionalData = Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

        $linkTitle   = Mage::helper('M2ePro')->__('Change');
        $linkContent = Mage::helper('M2ePro')->__('Change');

        $html .= '<div id="variation_identifiers_' . $variationId .
            '" style="font-size: 11px; color: grey; margin-left: 7px; width: 165px;">';

        if (!empty($additionalData['product_details'])) {
            foreach ($additionalData['product_details'] as $identifier => $identifierValue) {
                $identifier = isset($this->_identifiers[$identifier])
                    ? Mage::helper('M2ePro')->escapeHtml($this->_identifiers[$identifier])
                    : Mage::helper('M2ePro')->escapeHtml($identifier);

                $identifierValue = $identifierValue ? Mage::helper('M2ePro')->escapeHtml($identifierValue)
                                                    : '--';
                $html .= <<<HTML
<span>
    <span>
        <strong>{$identifier}</strong>
    </span>:&nbsp;
    <span class="value">
        {$identifierValue}
    </span>
</span>
<br/>
HTML;
            }
        } else {
            $linkTitle = Mage::helper('M2ePro')->__('Set');
            $linkContent = Mage::helper('M2ePro')->__('Set');
        }

        $html .= '</div>';

        $options = $column->getOptions();
        foreach ($options as $optionKey => $optionVal) {
            $identifierValue = empty($additionalData['product_details'][$optionKey]) ?
                '' : $additionalData['product_details'][$optionKey];

            $formHtml .= <<<HTML
<div style="display: table-row;">
    <div style="display: table-cell; vertical-align: middle;">
        <div style="width: 40px;">
        {$optionVal}:&nbsp;
        </div>
    </div>
    <div style="display: table-cell;">
        <input type="text" name="product_details[{$optionKey}]" class="M2ePro-{$optionKey}" value="{$identifierValue}"
            style="width: 125px; margin: 5px 0px;">
    </div>
</div>
HTML;
        }

        $manageMode = ManageController::MANAGE_VARIATION_MODE;
        $html .= <<<HTML
<div style="margin: 0px 7px; width: 150px;">
<form action="javascript:void(0);" id="variation_identifiers_edit_{$variationId}" style="font-size:11px;display: none">
    {$formHtml}
    <div style="display: table-row;">
        <div style="display: table-cell;"></div>
        <div style="display: table-cell;">
            <input type="hidden" name="variation_id" value="{$variationId}">
            <input type="hidden" name="listing_product_id" value="{$this->getListingProduct()->getId()}">
            <input type="hidden" name="manage_mode" value="{$manageMode}">
            <button class="scalable confirm-btn"
                    onclick="VariationsGridObj.confirmVariationIdentifiers(this, '{$variationId}')"
                    style="margin-top: 8px; float: right;">Confirm</button>
            <a href="javascript:void(0);" class="scalable"
                onclick="VariationsGridObj.cancelVariationIdentifiers('{$variationId}')"
                style="margin: 7px 8px; float: right;">Cancel</a>
        </div>
    </div>
</form>
<div style="text-align: left;">
<a href="javascript:"
    id="edit_variations_{$variationId}"
    onclick="VariationsGridObj.editVariationIdentifiers(this, '{$variationId}')"
    title="{$linkTitle}">{$linkContent}</a>
</div>
</div>
HTML;

        return $html;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $html = '';

        switch ($row->getData('status')) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                $html = '<span style="color: gray;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $html = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE:
            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                $html = '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $html = '<span style="color: orange;">'.$value.'</span>';
                break;

            default:
                break;
        }

        if ($row->getData('add')) {
            $html .= '<br/><span style="color: gray; font-size: 10px;">will be added</span>';
        }

        if ($row->getData('delete')) {
            $html .= '<br/><span style="color: gray; font-size: 10px;">will be deleted</span>';
        }

        return $html;
    }

    //########################################

    public function callbackFilterVariations($collection, $column)
    {
        $values = $column->getFilter()->getValue();

        if ($values == null && !is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if (is_array($value) && !empty($value['value'])) {
                $collection->addFieldToFilter(
                    'attributes',
                    array('regexp'=>
                          '[[:space:]]*'.$value['attr'].'[[:space:]]*==[[:space:]]*'.$value['value'].'[[:space:]]*'
                    )
                );
            }
        }
    }

    public function callbackFilterIdentifiers($collection, $column)
    {
        $values = $column->getFilter()->getValue();

        if ($values == null && !is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if (is_array($value) && !empty($value['value'])) {
                $collection->addFieldToFilter(
                    'additional_data',
                    array('regexp'=> '"product_details":[^}]*'.$value['attr'].'[[:space:]]*":"[[:space:]]*' .
                        // trying to screen slashes that in json
                        addslashes(addslashes($value['value']).'[[:space:]]*'))
                );
            }
        }
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing_variation_product_manage/viewVariationsGridAjax', array(
            '_current' => true
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function _toHtml()
    {
        $urls = Mage::helper('M2ePro')->jsonEncode(
            array(
            'adminhtml_ebay_listing_variation_product_manage/setIdentifiers' => $this->getUrl(
                '*/adminhtml_ebay_listing_variation_product_manage/setIdentifiers'
            )
            )
        );

        $javascriptMain = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});

    Event.observe(window, 'load', function() {

        GridFrameObj = new GridFrame();
        Common.prototype.scroll_page_to_top = function() { return; }

        VariationsGridObj = new EbayListingVariationProductManageVariationsGrid(
            'ebayVariationProductManageGrid'
        );

        setTimeout(function() {
            VariationsGridObj.afterInitPage();
        }, 350);
    });

    if (typeof VariationsGridObj != 'undefined') {
        VariationsGridObj.afterInitPage();
    }

</script>
HTML;

        return $javascriptMain . parent::_toHtml();
    }

    //########################################

    protected function getVariationsAttributes()
    {
        if ($this->_variationAttributes === null) {
            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
            $tableVariation = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_listing_product_variation');
            $tableOption = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_listing_product_variation_option');

            $select = $connRead->select();
            $select->from(array('mlpv' => $tableVariation), array())
                ->join(
                    array('mlpvo' => $tableOption),
                    'mlpvo.listing_product_variation_id = mlpv.id',
                    array('attribute')
                )
                ->where('listing_product_id = ?', (int)$this->getListingProductId());

            $attributes = Mage::getResourceModel('core/config')->getReadConnection()->fetchCol($select);

            $this->_variationAttributes = array_unique($attributes);
        }

        return $this->_variationAttributes;
    }

    protected function parseGroupedData($data)
    {
        $result = array();

        $variationData = explode('||', $data);
        foreach ($variationData as $variationAttribute) {
            $value = explode('==', $variationAttribute);
            $result[$value[0]] = $value[1];
        }

        return $result;
    }

    //########################################
}
