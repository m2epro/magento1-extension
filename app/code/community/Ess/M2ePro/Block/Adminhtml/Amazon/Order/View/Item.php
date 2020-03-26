<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_View_Item extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Ess_M2ePro_Model_Order $_order */
    protected $_order = null;

    //########################################

    /**
     * {@inheritDoc}
     */
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

        $this->_order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
    }

    /**
     * {@inheritDoc}
     */
    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Order_Item')
            ->addFieldToFilter('order_id', $this->_order->getId());

        $stockId = Mage::helper('M2ePro/Magento_Store')->getStockId($this->_order->getStore());

        $collection->getSelect()->joinLeft(
            array(
                'cisi' => Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('cataloginventory_stock_item')
            ),
            "(cisi.product_id = `main_table`.product_id AND cisi.stock_id = {$stockId})",
            array('is_in_stock')
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product'),
            'align'     => 'left',
            'width'     => '*',
            'index'     => 'product_id',
            'frame_callback' => array($this, 'callbackColumnProduct')
            )
        );

        $this->addColumn(
            'stock_availability', array(
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
            )
        );

        $this->addColumn(
            'original_price', array(
            'header'    => Mage::helper('M2ePro')->__('Original Price'),
            'align'     => 'left',
            'width'     => '80px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnOriginalPrice')
            )
        );

        $this->addColumn(
            'qty_purchased', array(
            'header'    => Mage::helper('M2ePro')->__('QTY'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'qty_purchased'
            )
        );

        $this->addColumn(
            'price', array(
            'header'    => Mage::helper('M2ePro')->__('Price'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'price',
            'frame_callback' => array($this, 'callbackColumnPrice')
            )
        );

        $this->addColumn(
            'discount_amount', array(
            'header'    => Mage::helper('M2ePro')->__('Promotions'),
            'align'     => 'left',
            'width'     => '80px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnDiscountAmount')
            )
        );

        if (Mage::getResourceModel('M2ePro/Amazon_Order')->hasGifts($this->_order->getId())) {
            $this->addColumn(
                'gift_price', array(
                'header'    => Mage::helper('M2ePro')->__('Gift Wrap Price'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'gift_price',
                'frame_callback' => array($this, 'callbackColumnGiftPrice')
                )
            );

            $this->addColumn(
                'gift_options', array(
                'header'    => Mage::helper('M2ePro')->__('Gift Options'),
                'align'     => 'left',
                'width'     => '250px',
                'filter'    => false,
                'sortable'  => false,
                'frame_callback' => array($this, 'callbackColumnGiftOptions')
                )
            );
        }

        $this->addColumn(
            'tax_percent', array(
            'header'         => Mage::helper('M2ePro')->__('Tax Percent'),
            'align'          => 'left',
            'width'          => '80px',
            'filter'         => false,
            'sortable'       => false,
            'frame_callback' => array($this, 'callbackColumnTaxPercent')
            )
        );

        $this->addColumn(
            'row_total', array(
            'header'    => Mage::helper('M2ePro')->__('Row Total'),
            'align'     => 'left',
            'width'     => '80px',
            'frame_callback' => array($this, 'callbackColumnRowTotal')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    /**
     * @param string                                  $value
     * @param Ess_M2ePro_Model_Order_Item             $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function callbackColumnProduct($value, $row, $column, $isExport)
    {
        $dataHelper = Mage::helper('M2ePro');

        $skuHtml = '';
        if ($row->getChildObject()->getSku()) {
            $skuHtml = <<<HTML
<b>{$dataHelper->__('SKU')}:</b> {$dataHelper->escapeHtml($row->getChildObject()->getSku())}&nbsp;
HTML;
        }

        $generalIdLabel = $dataHelper->__($row->getChildObject()->getIsIsbnGeneralId() ? 'ISBN' : 'ASIN');
        $generalIdHtml = <<<HTML
<b>{$generalIdLabel}:</b> {$dataHelper->escapeHtml($row->getChildObject()->getGeneralId())}<br/>
HTML;

        $afnWarehouseHtml = '';
        if ($row->getOrder()->getChildObject()->isFulfilledByAmazon()) {
            $fulfillmentCenterId = $row->getChildObject()->getFulfillmentCenterId();
            $fulfillmentCenterId = empty($fulfillmentCenterId) ? 'Pending' : $fulfillmentCenterId;
            $afnWarehouseHtml = <<<HTML
<b>{$dataHelper->__('AFN Warehouse')}:</b> {$dataHelper->escapeHtml($fulfillmentCenterId)}<br/>
HTML;
        }

        if ($row->getChildObject()->getIsIsbnGeneralId() &&
            !$dataHelper->isISBN($row->getChildObject()->getGeneralId())) {
            $amazonLink = '';
        } else {
            $itemUrl = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
                $row->getChildObject()->getGeneralId(),
                $this->_order->getData('marketplace_id')
            );

            $amazonLink = <<<HTML
<a href="{$itemUrl}" target="_blank">{$dataHelper->__('View on Amazon')}</a>&nbsp;|&nbsp;
HTML;
        }

        $productLink = '';
        if ($productId = $row->getData('product_id')) {
            $productUrl = $this->getUrl('adminhtml/catalog_product/edit', array(
                'id'    => $productId,
                'store' => $row->getOrder()->getStoreId()
            ));
            $productLink = <<<HTML
<a href="{$productUrl}" target="_blank">{$dataHelper->__('View')}</a>
HTML;
        }

        $orderItemId = (int)$row->getId();
        $gridId = $this->getId();

        $editLink = '';
        if (!$row->getProductId() || $row->getMagentoProduct()->isProductWithVariations()) {
            if (!$row->getProductId()) {
                $action = $dataHelper->__('Map to Magento Product');
            } else {
                $action = $dataHelper->__('Set Options');
            }

            $class = 'class="gray"';

            $js = "{OrderEditItemHandlerObj.edit('{$gridId}', {$orderItemId});}";
            $editLink = '<a href="javascript:void(0);" onclick="'.$js.'" '.$class.'>'.$action.'</a>';
        }

        $discardLink = '';
        if ($row->getProductId()) {
            $action = $dataHelper->__('Unmap');

            $js = "{OrderEditItemHandlerObj.unassignProduct('{$gridId}', {$orderItemId});}";
            $discardLink = '<a href="javascript:void(0);" onclick="'.$js.'" class="gray">'.$action.'</a>';

            if ($editLink) {
                $discardLink = '&nbsp;|&nbsp;' . $discardLink;
            }
        }

        return <<<HTML
<b>{$dataHelper->escapeHtml($row->getChildObject()->getTitle())}</b><br/>
<div style="padding-left: 10px;">
    {$skuHtml}
    {$generalIdHtml}
    {$afnWarehouseHtml}
</div>
<div style="float: left;">{$amazonLink}{$productLink}</div>
<div style="float: right;">{$editLink}{$discardLink}</div>
HTML;
    }

    /**
     * @param string                                  $value
     * @param Ess_M2ePro_Model_Order_Item             $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     */
    public function callbackColumnStockAvailability($value, $row, $column, $isExport)
    {
        if ($row->getData('is_in_stock') === null) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    /**
     * @param string                                  $value
     * @param Ess_M2ePro_Model_Order_Item             $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     */
    public function callbackColumnOriginalPrice($value, $row, $column, $isExport)
    {
        $productId = $row->getData('product_id');
        $formattedPrice = Mage::helper('M2ePro')->__('N/A');

        if ($productId && $product = Mage::getModel('catalog/product')->load($productId)) {
            $formattedPrice = $product->getFormatedPrice();
        }

        return $formattedPrice;
    }

    /**
     * @param string                                  $value
     * @param Ess_M2ePro_Model_Order_Item             $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $currency = $row->getData('currency');
        if (empty($currency)) {
            $currency = $this->_order->getMarketplace()->getChildObject()->getDefaultCurrency();
        }

        return Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, $row->getData('price'));
    }

    /**
     * @param string                                  $value
     * @param Ess_M2ePro_Model_Order_Item             $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function callbackColumnGiftPrice($value, $row, $column, $isExport)
    {
        $currency = $row->getData('currency');
        if (empty($currency)) {
            $currency = $this->_order->getMarketplace()->getChildObject()->getDefaultCurrency();
        }

        return Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, $row->getData('gift_price'));
    }

    /**
     * @param string                                  $value
     * @param Ess_M2ePro_Model_Order_Item             $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function callbackColumnDiscountAmount($value, $row, $column, $isExport)
    {
        $currency = $row->getData('currency');
        if (empty($currency)) {
            $currency = $this->_order->getMarketplace()->getChildObject()->getDefaultCurrency();
        }

        $discountDetails = $row->getData('discount_details');
        if (empty($discountDetails)) {
            Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, 0);
        }

        $discountDetails = Mage::helper('M2ePro')->jsonDecode($row->getData('discount_details'));
        if (empty($discountDetails['promotion']['value'])) {
            Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, 0);
        }

        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $currency, $discountDetails['promotion']['value']
        );
    }

    /**
     * @param string                                  $value
     * @param Ess_M2ePro_Model_Order_Item             $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     */
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

    /**
     * @param string                                  $value
     * @param Ess_M2ePro_Model_Order_Item             $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function callbackColumnTaxPercent($value, $row, $column, $isExport)
    {
        $rate = $this->_order->getChildObject()->getProductPriceTaxRate();
        if (empty($rate)) {
            return '0%';
        }

        return sprintf('%s%%', $rate);
    }

    /**
     * @param string                                  $value
     * @param Ess_M2ePro_Model_Order_Item             $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool                                    $isExport
     *
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function callbackColumnRowTotal($value, $row, $column, $isExport)
    {
        $aOrderItem = $row->getChildObject();

        $currency = $row->getData('currency');
        if (empty($currency)) {
            $currency = $this->_order->getMarketplace()->getChildObject()->getDefaultCurrency();
        }

        $price = $aOrderItem->getPrice() + $aOrderItem->getGiftPrice() + $aOrderItem->getTaxAmount();
        $price = $price - $aOrderItem->getDiscountAmount();

        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $currency, $price * $aOrderItem->getQtyPurchased()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderItemGrid', array('_current' => true));
    }

    //########################################
}
