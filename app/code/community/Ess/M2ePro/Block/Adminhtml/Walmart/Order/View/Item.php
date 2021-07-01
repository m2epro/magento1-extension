<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Order_View_Item extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var $_order Ess_M2ePro_Model_Order */
    protected $_order = null;

    protected $_itemSkuToWalmartIds;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartOrderViewItem');
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

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Walmart')
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

    protected function _afterLoadCollection()
    {
        $cache = array();
        $skus = $this->getCollection()->getColumnValues('sku');

        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->joinListingTable();

        $collection->addFieldToFilter('sku', array('in' => $skus));
        $collection->addFieldToFilter('account_id', $this->_order->getAccountId());
        $collection->addFieldToFilter('marketplace_id', $this->_order->getMarketplaceId());

        foreach ($collection->getItems() as $item) {
            /**@var Ess_M2ePro_Model_Listing_Product $item */
            $sku    = (string)$item->getChildObject()->getSku();
            $itemId = (string)$item->getChildObject()->getItemId();
            $wpid   = (string)$item->getChildObject()->getWpid();

            $itemId && $cache[$sku]['item_id'] = $itemId;
            $wpid && $cache[$sku]['wpid']      = $wpid;
        }

        // ---------------------------------------

        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');

        $collection->addFieldToFilter('sku', array('in' => $skus));
        $collection->addFieldToFilter('account_id', $this->_order->getAccountId());
        $collection->addFieldToFilter('marketplace_id', $this->_order->getMarketplaceId());

        foreach ($collection->getItems() as $item) {
            /**@var Ess_M2ePro_Model_Listing_Other $item */
            $sku    = (string)$item->getChildObject()->getSku();
            $itemId = (string)$item->getChildObject()->getItemId();
            $wpid   = (string)$item->getChildObject()->getWpid();

            if (empty($cache[$sku])) {
                $itemId && $cache[$sku]['item_id'] = $itemId;
                $wpid && $cache[$sku]['wpid']      = $wpid;
            }
        }

        // ---------------------------------------

        $this->_itemSkuToWalmartIds = $cache;

        return parent::_afterLoadCollection();
    }

    //########################################

    /**
     * @param string $value
     * @param Ess_M2ePro_Model_Order_Item $row
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param bool $isExport
     *
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function callbackColumnProduct($value, $row, $column, $isExport)
    {
        $dataHelper = Mage::helper('M2ePro');
        $walmartOrderItem = $row->getChildObject();

        $skuHtml = '';
        if ($walmartOrderItem->getSku()) {
            $skuHtml = <<<HTML
<b>{$dataHelper->__('SKU')}:</b> {$dataHelper->escapeHtml($walmartOrderItem->getSku())}<br/>
HTML;
        }

        $walmartLink = '';
        $marketplaceId = $this->_order->getMarketplaceId();
        $walmartHelper = Mage::helper('M2ePro/Component_Walmart');
        $idForLink = $walmartHelper->getIdentifierForItemUrl($marketplaceId);
        if (!empty($this->_itemSkuToWalmartIds[$walmartOrderItem->getSku()][$idForLink])) {
            $itemUrl = $walmartHelper->getItemUrl(
                $this->_itemSkuToWalmartIds[$walmartOrderItem->getSku()][$idForLink],
                $marketplaceId
            );
            $walmartLink = <<<HTML
<a href="{$itemUrl}" target="_blank">{$dataHelper->__('View on Walmart')}</a>
HTML;
        }

        $productLink = '';
        if ($row->getProductId()) {
            $productUrl = $this->getUrl('adminhtml/catalog_product/edit', array(
                'id'    => $row->getProductId(),
                'store' => $row->getOrder()->getStoreId()
            ));
            $productLink = <<<HTML
<a href="{$productUrl}" target="_blank">{$dataHelper->__('View')}</a>
HTML;
        }

        $walmartLink && $productLink && $walmartLink .= '&nbsp;|&nbsp;';
        $jsTemplate = <<<HTML
<a class="gray" href="javascript:void(0);" onclick="
{OrderEditItemObj.%s('{$this->getId()}', {$row->getId()});}
">%s</a>
HTML;

        $editLink = '';
        if (!$row->getProductId()) {
            $editLink = sprintf($jsTemplate, 'edit', $dataHelper->__('Link to Magento Product'));
        }

        $isPretendedToBeSimple = false;
        if ($walmartOrderItem->getParentObject()->getMagentoProduct() !== null &&
            $walmartOrderItem->getParentObject()->getMagentoProduct()->isGroupedType() &&
            $walmartOrderItem->getChannelItem() !== null) {
            $isPretendedToBeSimple = $walmartOrderItem->getChannelItem()->isGroupedProductModeSet();
        }

        if ($row->getProductId() && $row->getMagentoProduct()->isProductWithVariations() && !$isPretendedToBeSimple) {
            $editLink = sprintf($jsTemplate, 'edit', $dataHelper->__('Set Options')) . '&nbsp;|&nbsp;';
        }

        $discardLink = '';
        if ($row->getProductId()) {
            $discardLink = sprintf($jsTemplate, 'unassignProduct', $dataHelper->__('Unlink'));
        }

        return <<<HTML
<b>{$dataHelper->escapeHtml($walmartOrderItem->getTitle())}</b><br/>
<div style="padding-left: 10px;">
    {$skuHtml}
</div>
<div style="float: left;">{$walmartLink}{$productLink}</div>
<div style="float: right;">{$editLink}{$discardLink}</div>
HTML;
    }

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
            $currency = $this->_order->getMarketplace()->getChildObject()->getDefaultCurrency();
        }

        return Mage::getSingleton('M2ePro/Currency')->formatPrice($currency, $row->getData('price'));
    }

    public function callbackColumnTaxPercent($value, $row, $column, $isExport)
    {
        $rate = $this->_order->getChildObject()->getProductPriceTaxRate();
        if (empty($rate)) {
            return '0%';
        }

        return sprintf('%s%%', $rate);
    }

    public function callbackColumnRowTotal($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Order_Item $row */
        /** @var Ess_M2ePro_Model_Walmart_Order_Item $aOrderItem */
        $aOrderItem = $row->getChildObject();

        $currency = $row->getData('currency');
        if (empty($currency)) {
            $currency = $this->_order->getMarketplace()->getChildObject()->getDefaultCurrency();
        }

        $price = $aOrderItem->getPrice();

        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $currency, $price * $aOrderItem->getQtyPurchased()
        );
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
