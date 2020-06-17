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
     * @param $value
     * @param $row Ess_M2ePro_Model_Order_Item
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnProduct($value, $row, $column, $isExport)
    {
        $html = '<b>'.Mage::helper('M2ePro')->escapeHtml($row->getTitle()).'</b><br/>';

        $variation = $row->getChildObject()->getVariationOptions();
        if (!empty($variation)) {
            foreach ($variation as $optionName => $optionValue) {
                $optionNameHtml = Mage::helper('M2ePro')->escapeHtml($optionName);
                $optionValueHtml = Mage::helper('M2ePro')->escapeHtml($optionValue);

                $html .= <<<HTML
<span style="font-weight: bold; font-style: italic; padding-left: 10px;">
{$optionNameHtml}:&nbsp;
</span>
{$optionValueHtml}<br/>
HTML;
            }
        }

        $itemUrl = Mage::helper('M2ePro/Component_Ebay')->getItemUrl(
            $row->getItemId(),
            $this->_order->getAccount()->getChildObject()->getMode(),
            $this->_order->getMarketplaceId()
        );

        $itemLink = '<a href="'.$itemUrl.'" target="_blank">'.Mage::helper('M2ePro')->__('View on eBay').'</a>';

        $productLink = '';

        if ($productId = $row->getData('product_id')) {
            $productUrl = $this->getUrl(
                'adminhtml/catalog_product/edit', array(
                'id'    => $productId,
                'store' => $row->getOrder()->getStoreId()
                )
            );
            $productLink .= ' | <a href="'.$productUrl.'" target="_blank">'.Mage::helper('M2ePro')->__('View').'</a>';
        }

        $html .= <<<HTML
<div style="float: left;">
{$itemLink}{$productLink}
</div>
HTML;

        $orderItemId = (int)$row->getId();
        $gridId = $this->getId();

        $editLink = '';
        if (!$row->getProductId() || $row->getMagentoProduct()->isProductWithVariations()) {
            if (!$row->getProductId()) {
                $action = Mage::helper('M2ePro')->__('Map to Magento Product');
            } else {
                $action = Mage::helper('M2ePro')->__('Set Options');
            }

            $class = 'class="gray"';

            $js = "{OrderEditItemObj.edit('{$gridId}', {$orderItemId});}";
            $editLink = '<a href="javascript:void(0);" onclick="'.$js.'" '.$class.'>'.$action.'</a>';
        }

        $discardLink = '';
        if ($row->getProductId()) {
            $action = Mage::helper('M2ePro')->__('Unmap');

            $js = "{OrderEditItemObj.unassignProduct('{$gridId}', {$orderItemId});}";
            $discardLink = '<a href="javascript:void(0);" onclick="'.$js.'" class="gray">'.$action.'</a>';

            if ($editLink) {
                $discardLink = '&nbsp;|&nbsp;' . $discardLink;
            }
        }

        $html .= <<<HTML
<div style="float: right;">
{$editLink}{$discardLink}
</div>
<div style="clear: both;"></div>
HTML;

        return $html;
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
