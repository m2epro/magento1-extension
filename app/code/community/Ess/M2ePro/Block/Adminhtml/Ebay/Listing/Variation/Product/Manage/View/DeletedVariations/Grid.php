<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Adminhtml_Ebay_Listing_Variation_Product_ManageController as ManageController;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Variation_Product_Manage_View_DeletedVariations_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct  */
    protected $_listingProduct;

    protected $_identifiers = array(
        'upc'  => 'UPC',
        'ean'  => 'EAN',
        'isbn' => 'ISBN',
        'mpn'  => 'MPN',
        'epid' => 'ePID'
    );

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('deletedMagentoVariationsGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    /**
     * @param mixed $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    //----------------------------------------

    protected function getListingProduct()
    {
        if (empty($this->_listingProduct)) {
            $this->_listingProduct = Mage::helper('M2ePro/Component_Ebay')
                                         ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    //########################################

    protected function _prepareCollection()
    {
        $data = $this->getListingProduct()->getSetting(
            'additional_data', 'variations_that_can_not_be_deleted', array()
        );

        $results = new Varien_Data_Collection();
        foreach ($data as $index => $item) {
            $temp = array(
                'id'        => $index,
                'qty'       => $item['qty'],
                'sku'       => $item['sku'],
                'price'     => $item['price'],
                'status'    => Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN,
                'specifics' => $item['specifics'],
                'details'   => !empty($item['details']) ? $item['details'] : array(),
            );

            $results->addItem(new Varien_Object($temp));
        }

        $this->setCollection($results);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'specifics', array(
            'header' => Mage::helper('M2ePro')->__('Specifics'),
            'align' => 'left',
            'width' => '210px',
            'sortable' => false,
            'index' => 'specifics',
            'frame_callback' => array($this, 'callbackColumnSpecifics'),
            )
        );

        $this->addColumn(
            'online_sku', array(
            'header'    => Mage::helper('M2ePro')->__('SKU'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'sku',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnOnlineSku')
            )
        );

        $this->addColumn(
            'qty', array(
            'header'    => Mage::helper('M2ePro')->__('Available QTY'),
            'align'     => 'right',
            'width'     => '40px',
            'index'     => 'qty',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnAvailableQty')
            )
        );

        $this->addColumn(
            'price', array(
            'header' => Mage::helper('M2ePro')->__('Price'),
            'align' => 'right',
            'width' => '40px',
            'index' => 'price',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnPrice'),
            )
        );

        $this->addColumn(
            'identifiers', array(
            'header' => Mage::helper('M2ePro')->__('eBay Catalog Identifiers'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'details',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnIdentifiers')
            )
        );

        $this->addColumn(
            'status', array(
            'header'=> Mage::helper('M2ePro')->__('Status'),
            'width' => '60px',
            'index' => 'status',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnStatus')
            )
        );
    }

    //########################################

    public function callbackColumnSpecifics($value, $row, $column, $isExport)
    {
        $html = '<div class="m2ePro-variation-attributes" style="margin-left: 5px;">';
        foreach ($row->getData('specifics') as $attribute => $option) {
            $optionHtml = '<b>' . Mage::helper('M2ePro')->escapeHtml($attribute) .
                '</b>:&nbsp;' . Mage::helper('M2ePro')->escapeHtml($option);

            $html .= $optionHtml . '<br/>';
        }

        $html .= '</div>';

        return $html;
    }

    public function callbackColumnOnlineSku($value, $row, $column, $isExport)
    {
        return $value;
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $currency = $this->getListingProduct()->getMarketplace()->getChildObject()->getCurrency();

        $priceStr = Mage::app()->getLocale()->currency($currency)->toCurrency($value);

        return $priceStr;
    }

    public function callbackColumnIdentifiers($value, $row, $column, $isExport)
    {
        $html = '';
        $formHtml = '';

        //Fake variation ID for variation, that can not be deleted
        $variationId = $this->_listingProduct->getId() . '##' . $row->getId();
        $details = $row->getData('details');

        $linkTitle   = Mage::helper('M2ePro')->__('Change');
        $linkContent = Mage::helper('M2ePro')->__('Change');

        $html .= '<div id="variation_identifiers_' . $variationId .
            '" style="font-size: 11px; color: grey; margin-left: 7px; width: 165px;">';

        if (!empty($details)) {
            foreach ($details as $identifier => $identifierValue) {
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

        foreach ($this->_identifiers as $optionKey => $optionVal) {
            $identifierValue = empty($details[$optionKey]) ? '' : $details[$optionKey];

            $formHtml .= <<<HTML
<div style="display: table-row;">
    <div style="display: table-cell; vertical-align: middle;">
        <div style="width: 40px;">
            {$optionVal}:&nbsp;
        </div>
    </div>
    <div style="display: table-cell;">
        <input type="text" name="product_details[{$optionKey}]"
               class="M2ePro-{$optionKey}"
               value="{$identifierValue}"
               style="width: 125px; margin: 5px 0;">
    </div>
</div>
HTML;
        }

        $manageMode = ManageController::MANAGE_VARIATION_THAT_CAN_NOT_BE_DELETED_MODE;
        $html .= <<<HTML
<div style="margin: 0 7px; width: 150px;">
<form action="javascript:void(0);" id="variation_identifiers_edit_{$variationId}" style="font-size:11px;display: none">
    {$formHtml}
    <div style="display: table-row;">
        <div style="display: table-cell;"></div>
        <div style="display: table-cell;">
            <input type="hidden" name="variation_id" value="{$variationId}">
            <input type="hidden" name="listing_product_id" value="{$this->_listingProduct->getId()}">
            <input type="hidden" name="manage_mode" value="{$manageMode}">
            <button class="scalable confirm-btn"
                    onclick="VariationsGridHandlerObj.confirmVariationIdentifiers(this, '{$variationId}')"
                    style="margin-top: 8px; float: right;">Confirm</button>
            <a href="javascript:void(0);" class="scalable"
                onclick="VariationsGridHandlerObj.cancelVariationIdentifiers('{$variationId}')"
                style="margin: 7px 8px; float: right;">Cancel</a>
        </div>
    </div>
</form>
<div style="text-align: left;">
    <a href="javascript:"
        id="edit_variations_{$variationId}"
        onclick="VariationsGridHandlerObj.editVariationIdentifiers(this, '{$variationId}')"
        title="{$linkTitle}">{$linkContent}</a>
</div>
</div>
HTML;

        return $html;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        return '<span style="color: red;">'.Mage::helper('M2ePro')->__('Inactive').'</span>';
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

        FrameHandlerObj = new FrameHandler();
        CommonHandler.prototype.scroll_page_to_top = function() { return; }

        VariationsGridHandlerObj = new EbayListingVariationProductManageVariationsGridHandler(
            'deletedMagentoVariationsGrid'
        );

        setTimeout(function() {
            VariationsGridHandlerObj.afterInitPage();
        }, 350);
    });

    if (typeof VariationsGridHandlerObj != 'undefined') {
        VariationsGridHandlerObj.afterInitPage();
    }

</script>
HTML;

        return $javascriptMain . parent::_toHtml();
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing_variation_product_manage/viewDeletedVariationsGridAjax', array(
            '_current' => true
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}
