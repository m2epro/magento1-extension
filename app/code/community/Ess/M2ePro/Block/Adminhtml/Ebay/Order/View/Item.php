<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Order_View_Item extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var $_order Ess_M2ePro_Model_Order */
    protected $_order;

    /** @var $_taxCalculator Mage_Tax_Model_Calculation */
    protected $_taxCalculator;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayOrderViewItem');
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

        $this->_order         = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $this->_taxCalculator = Mage::getSingleton('tax/calculation');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')
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
            'qty_sold', array(
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
            'ebay_collect_tax', array(
                'header'         => Mage::helper('M2ePro')->__('Collect and Remit taxes'),
                'align'          => 'left',
                'width'          => '80px',
                'filter'         => false,
                'sortable'       => false,
                'frame_callback' => array($this, 'callbackColumnEbayCollectTax')
            )
        );

        $this->addColumn(
            'row_total', array(
            'header'    => Mage::helper('M2ePro')->__('Row Total'),
            'align'     => 'left',
            'width'     => '80px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnRowTotal')
            )
        );

        return parent::_prepareColumns();
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
        $eBayOrderItem = $row->getChildObject();

        $variationHtml = '';
        $variation = $eBayOrderItem->getVariationOptions();
        if (!empty($variation)) {
            foreach ($variation as $optionName => $optionValue) {
                $variationHtml .= <<<HTML
<span style="font-weight: bold; font-style: italic; padding-left: 10px;">
{$dataHelper->escapeHtml($optionName)}:&nbsp;
</span>
{$dataHelper->escapeHtml($optionValue)}<br/>
HTML;
            }
        }

        $itemUrl = Mage::helper('M2ePro/Component_Ebay')->getItemUrl(
            $eBayOrderItem->getItemId(),
            $this->_order->getAccount()->getChildObject()->getMode(),
            $this->_order->getMarketplaceId()
        );
        $eBayLink = <<<HTML
<a href="{$itemUrl}" target="_blank">{$dataHelper->__('View on eBay')}</a>
HTML;

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

        $eBayLink && $productLink && $eBayLink .= '&nbsp;|&nbsp;';
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
        if ($eBayOrderItem->getParentObject()->getMagentoProduct() !== null &&
            $eBayOrderItem->getParentObject()->getMagentoProduct()->isGroupedType() &&
            $eBayOrderItem->getChannelItem() !== null) {
            $isPretendedToBeSimple = $eBayOrderItem->getChannelItem()->isGroupedProductModeSet();
        }

        if ($row->getProductId() && $row->getMagentoProduct()->isProductWithVariations() && !$isPretendedToBeSimple) {
            $editLink = sprintf($jsTemplate, 'edit', $dataHelper->__('Set Options')) . '&nbsp;|&nbsp;';
        }

        $discardLink = '';
        if ($row->getProductId()) {
            $discardLink = sprintf($jsTemplate, 'unassignProduct', $dataHelper->__('Unlink'));
        }

        return <<<HTML
<b>{$dataHelper->escapeHtml($eBayOrderItem->getTitle())}</b><br/>
{$variationHtml}
<div style="float: left;">{$eBayLink}{$productLink}</div>
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
        $formattedPrice = Mage::helper('M2ePro')->__('N/A');

        $product = $row->getProduct();

        if ($product) {
            /** @var Ess_M2ePro_Model_Magento_Product $magentoProduct */
            $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
            $magentoProduct->setProduct($product);

            if ($magentoProduct->isGroupedType()) {
                $associatedProducts = $row->getAssociatedProducts();
                $price = Mage::getModel('catalog/product')
                    ->load(array_shift($associatedProducts))
                    ->getPrice();

                $formattedPrice = $this->_order->getStore()->formatPrice($price);
            } else {
                $formattedPrice = $this->_order->getStore()->formatPrice($row->getProduct()->getPrice());
            }
        }

       return $formattedPrice;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $this->_order->getChildObject()->getCurrency(), $row->getData('price')
        );
    }

    public function callbackColumnTaxPercent($value, $row, $column, $isExport)
    {
        $taxDetails = $row->getData('tax_details');
        if (empty($taxDetails)) {
            return '0%';
        }

        $taxDetails = Mage::helper('M2ePro')->jsonDecode($taxDetails);
        if (empty($taxDetails)) {
            return '0%';
        }

        return sprintf('%s%%', $taxDetails['rate']);
    }

    public function callbackColumnEbayCollectTax($value, $row, $column, $isExport)
    {
        $collectTax = Mage::helper('M2ePro')->jsonDecode($row->getData('tax_details'));

        if (isset($collectTax['ebay_collect_taxes'])) {
            return Mage::getSingleton('M2ePro/Currency')->formatPrice(
                $this->_order->getChildObject()->getCurrency(), $collectTax['ebay_collect_taxes']
            );
        }

        return '0';
    }

    public function callbackColumnRowTotal($value, $row, $column, $isExport)
    {
        $total = $row->getData('qty_purchased') * $row->getData('price');

        $taxDetails = $row->getData('tax_details');
        if (!empty($taxDetails)) {
            $taxDetails = Mage::helper('M2ePro')->jsonDecode($row->getData('tax_details'));

            if (!empty($taxDetails['amount'])) {
                $total += $taxDetails['amount'];
            }
        }

        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $this->_order->getChildObject()->getCurrency(), $total
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
