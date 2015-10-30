<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Order_View_Item extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var $order Ess_M2ePro_Model_Order */
    protected $order = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderViewItem');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
        $this->_defaultLimit = 200;
        // ---------------------------------------

        $this->order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Order_Item')
            ->addFieldToFilter('order_id', $this->order->getId());

        $collection->getSelect()->joinLeft(
            array('cisi' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')),
            '(cisi.product_id = `main_table`.product_id AND cisi.stock_id = 1)',
            array('is_in_stock')
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product'),
            'align'     => 'left',
            'width'     => '*',
            'index'     => 'product_id',
            'frame_callback' => array($this, 'callbackColumnProduct')
        ));

        $this->addColumn('stock_availability', array(
            'header'=> Mage::helper('M2ePro')->__('Stock Availability'),
            'width' => '100px',
            'index' => 'is_in_stock',
            'filter_index' => 'cisi.is_in_stock',
            'type'  => 'options',
            'sortable'  => false,
            'options' => array(
                1 => Mage::helper('M2ePro')->__('In Stock'),
                0 => Mage::helper('M2ePro')->__('Out of Stock')
            ),
            'frame_callback' => array($this, 'callbackColumnStockAvailability')
        ));

        $this->addColumn('original_price', array(
            'header'    => Mage::helper('M2ePro')->__('Original Price'),
            'align'     => 'left',
            'width'     => '80px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnOriginalPrice')
        ));

        $this->addColumn('qty_purchased', array(
            'header'    => Mage::helper('M2ePro')->__('QTY'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'qty_purchased'
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('M2ePro')->__('Price'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'price',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('discount_amount', array(
            'header'    => Mage::helper('M2ePro')->__('Promotions'),
            'align'     => 'left',
            'width'     => '80px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnDiscountAmount')
        ));

        if (Mage::getResourceModel('M2ePro/Amazon_Order')->hasGifts($this->order->getId())) {
            $this->addColumn('gift_price', array(
                'header'    => Mage::helper('M2ePro')->__('Gift Wrap Price'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'gift_price',
                'frame_callback' => array($this, 'callbackColumnGiftPrice')
            ));

            $this->addColumn('gift_options', array(
                'header'    => Mage::helper('M2ePro')->__('Gift Options'),
                'align'     => 'left',
                'width'     => '250px',
                'filter'    => false,
                'sortable'  => false,
                'frame_callback' => array($this, 'callbackColumnGiftOptions')
            ));
        }

        $this->addColumn('row_total', array(
            'header'    => Mage::helper('M2ePro')->__('Row Total'),
            'align'     => 'left',
            'width'     => '80px',
            'frame_callback' => array($this, 'callbackColumnRowTotal')
        ));

        return parent::_prepareColumns();
    }

    //########################################

    /**
     * @param $value
     * @param $row Ess_M2ePro_Model_Order_Item
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnProduct($value, $row, $column, $isExport)
    {
        $skuHtml = '';
        if ($row->getSku()) {
            $skuLabel = Mage::helper('M2ePro')->__('SKU');
            $sku = Mage::helper('M2ePro')->escapeHtml($row->getSku());

            $skuHtml = <<<HTML
<b>{$skuLabel}:</b> {$sku}<br/>
HTML;
        }

        $generalIdLabel = Mage::helper('M2ePro')->__($row->getIsIsbnGeneralId() ? 'ISBN' : 'ASIN');
        $generalId = Mage::helper('M2ePro')->escapeHtml($row->getGeneralId());

        $generalIdHtml = <<<HTML
<b>{$generalIdLabel}:</b> {$generalId}<br/>
HTML;

        if ($row->getIsIsbnGeneralId() && !Mage::helper('M2ePro')->isISBN($row->getGeneralId())) {
            $amazonLink = '';
        } else {
            $itemLinkText = Mage::helper('M2ePro')->__('View on Amazon');
            $itemUrl = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
                $row->getGeneralId(), $this->order->getData('marketplace_id')
            );

            $amazonLink = <<<HTML
<a href="{$itemUrl}" target="_blank">{$itemLinkText}</a>
HTML;
        }

        $productLink = '';
        if ($productId = $row->getData('product_id')) {
            $productUrl = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $productId));
            $productLink = ' | <a href="'.$productUrl.'" target="_blank">'.Mage::helper('M2ePro')->__('View').'</a>';
        }

        $orderItemId = (int)$row->getId();
        $gridId = $this->getId();

        $editLink = '';
        if (!$row->getProductId() || $row->getMagentoProduct()->hasRequiredOptions()) {

            if (!$row->getProductId()) {
                $action = Mage::helper('M2ePro')->__('Map to Magento Product');
            } else {
                $action = Mage::helper('M2ePro')->__('Set Options');
            }

            $class = 'class="gray"';

            $js = "{OrderEditItemHandlerObj.edit('{$gridId}', {$orderItemId});}";
            $editLink = '<a href="javascript:void(0);" onclick="'.$js.'" '.$class.'>'.$action.'</a>';
        }

        $discardLink = '';
        if ($row->getProductId()) {
            $action = Mage::helper('M2ePro')->__('Unmap');

            $js = "{OrderEditItemHandlerObj.unassignProduct('{$gridId}', {$orderItemId});}";
            $discardLink = '<a href="javascript:void(0);" onclick="'.$js.'" class="gray">'.$action.'</a>';

            if ($editLink) {
                $discardLink = '&nbsp;|&nbsp;' . $discardLink;
            }
        }

        $itemTitle = Mage::helper('M2ePro')->escapeHtml($row->getTitle());

        return <<<HTML
<b>{$itemTitle}</b><br/>
<div style="padding-left: 10px;">
    {$skuHtml}
    {$generalIdHtml}
</div>
<div style="float: left;">{$amazonLink}{$productLink}</div>
<div style="float: right;">{$editLink}{$discardLink}</div>
HTML;
    }

    public function callbackColumnStockAvailability($value, $row, $column, $isExport)
    {
        if (is_null($row->getData('is_in_stock'))) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnOriginalPrice($value, $row, $column, $isExport)
    {
        $productId = $row->getData('product_id');
        $formattedPrice = Mage::helper('M2ePro')->__('N/A');

        if ($productId && $product = Mage::getModel('catalog/product')->load($productId)) {
            $formattedPrice = $product->getFormatedPrice();
        }

        return $formattedPrice;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $currency = $row->getData('currency');
        if (empty($currency)) {
            $currency = $this->order->getMarketplace()->getChildObject()->getDefaultCurrency();
        }

        return Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, $row->getData('price'));
    }

    public function callbackColumnGiftPrice($value, $row, $column, $isExport)
    {
        $currency = $row->getData('currency');
        if (empty($currency)) {
            $currency = $this->order->getMarketplace()->getChildObject()->getDefaultCurrency();
        }

        return Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, $row->getData('gift_price'));
    }

    public function callbackColumnDiscountAmount($value, $row, $column, $isExport)
    {
        $currency = $row->getData('currency');
        if (empty($currency)) {
            $currency = $this->order->getMarketplace()->getChildObject()->getDefaultCurrency();
        }

        $discountDetails = $row->getData('discount_details');
        if (empty($discountDetails)) {
            Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, 0);
        }

        $discountDetails = @json_decode($row->getData('discount_details'), true);
        if (empty($discountDetails['promotion']['value'])) {
            Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, 0);
        }

        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $currency, (int)$discountDetails['promotion']['value']
        );
    }

    public function callbackColumnGiftOptions($value, $row, $column, $isExport)
    {
        if ($row->getData('gift_type') == '' && $row->getData('gift_message') == '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $giftType = Mage::helper('M2ePro')->escapeHtml($row->getData('gift_type'));
        $giftTypeLabel = Mage::helper('M2ePro')->__('Gift Wrap Type');

        $giftMessage = Mage::helper('M2ePro')->escapeHtml($row->getData('gift_message'));
        $giftMessageLabel = Mage::helper('M2ePro')->__('Gift Message');

        $resultHtml = '';
        if (!empty($giftType)) {
            $resultHtml .= "<strong>{$giftTypeLabel}: </strong>{$giftType}<br/>";
        }

        $resultHtml .= "<strong>{$giftMessageLabel}: </strong>{$giftMessage}";

        return $resultHtml;
    }

    public function callbackColumnRowTotal($value, $row, $column, $isExport)
    {
        $currency = $row->getData('currency');
        if (empty($currency)) {
            $currency = $this->order->getMarketplace()->getChildObject()->getDefaultCurrency();
        }

        $price = (float)$row->getData('price') + (float)$row->getData('gift_price');
        return Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, $price * $row->getData('qty_purchased'));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderItemGrid', array('_current' => true));
    }

    //########################################
}