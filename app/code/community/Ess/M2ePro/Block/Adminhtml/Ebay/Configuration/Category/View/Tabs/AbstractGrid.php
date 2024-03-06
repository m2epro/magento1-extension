<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_Qty as OnlineQty;
use Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_View_Tabs as CategoryViewTabs;
use Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser as Chooser;

abstract class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_View_Tabs_AbstractGrid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    //########################################

    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection) {
            $columnIndex = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();

            if ($columnIndex == 'available_qty') {
                $collection->getSelect()->order(
                    '(elp.online_qty - elp.online_qty_sold) ' . strtoupper($column->getDir())
                );
            } else {
                $collection->setOrder($columnIndex, strtoupper($column->getDir()));
            }
        }

        return $this;
    }
    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'    => Mage::helper('M2ePro')->__('Product ID'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'entity_id',
                'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_productId',
            )
        );

        $this->addColumn(
            'name', array(
                'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
                'align'     => 'left',
                'width'     => '700px',
                'type'      => 'text',
                'index'     => 'online_title',
                'frame_callback' => array($this, 'callbackColumnTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'is_custom_template', array(
                'header'       => Mage::helper('M2ePro')->__('Item Specifics'),
                'width'        => '100px',
                'index'        => 'is_custom_template',
                'filter_index' => 'is_custom_template',
                'type'         => 'options',
                'sortable'     => false,
                'options'      => array(
                    1 => Mage::helper('M2ePro')->__('Custom'),
                    0 => Mage::helper('M2ePro')->__('Default')
                )
            )
        );

        $this->addColumn(
            'ebay_item_id', array(
                'header'    => Mage::helper('M2ePro')->__('Item ID'),
                'align'     => 'left',
                'width'     => '100px',
                'type'      => 'text',
                'index'     => 'item_id',
                'renderer'  => 'M2ePro/adminhtml_ebay_grid_column_renderer_itemId'
            )
        );

        $this->addColumn(
            'available_qty', array(
                'header'    => Mage::helper('M2ePro')->__('Available QTY'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'available_qty',
                'filter_index'      => 'available_qty',
                'renderer'          => 'M2ePro/adminhtml_ebay_grid_column_renderer_qty',
                'render_online_qty' => OnlineQty::ONLINE_AVAILABLE_QTY,
                'filter_condition_callback' => array($this, 'callbackFilterOnlineQty')
            )
        );

        $this->addColumn(
            'online_qty_sold', array(
                'header'   => Mage::helper('M2ePro')->__('Sold QTY'),
                'align'    => 'right',
                'width'    => '100px',
                'type'     => 'number',
                'index'    => 'online_qty_sold',
                'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_qty'
            )
        );

        $this->addColumn(
            'price', array(
                'header'    => Mage::helper('M2ePro')->__('Price'),
                'align'     =>'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'online_current_price',
                'filter_index' => 'online_current_price',
                'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_currentPrice',
                'filter_condition_callback' => array($this, 'callbackFilterPrice')
            )
        );

        $this->addColumn(
            'end_date', array(
                'header'   => Mage::helper('M2ePro')->__('End Date'),
                'align'    => 'right',
                'width'    => '100px',
                'type'     => 'datetime',
                'format'   => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'index'    => 'end_date',
                'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_dateTime'
            )
        );

        $statusColumn = array(
            'header'       => Mage::helper('M2ePro')->__('Status'),
            'width'        => '100px',
            'index'        => 'status',
            'filter_index' => 'status',
            'type'         => 'options',
            'sortable'     => false,
            'options'      => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN     => Mage::helper('M2ePro')->__('Listed (Hidden)'),
                Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE   => Mage::helper('M2ePro')->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Pending')
            ),
            'renderer' => 'M2ePro/adminhtml_ebay_grid_column_renderer_status',
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        );

        $this->addColumn('status', $statusColumn);

        return parent::_prepareColumns();
    }

    //########################################

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $this->getMassactionBlock()->addItem(
            'editEbayCategory', array(
                'label'    => Mage::helper('M2ePro')->__('Edit'),
                'url'      => ''
            )
        );

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $title       = $row->getData('name');
        $onlineTitle = $row->getData('online_title');

        !empty($onlineTitle) && $title = $onlineTitle;

        $title = Mage::helper('M2ePro')->escapeHtml($title);

        $valueHtml = '<span class="product-title-value">' . $title . '</span>';

        $sku = $row->getData('sku');

        if ($sku === null) {
            $sku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $onlineSku = $row->getData('online_sku');
        !empty($onlineSku) && $sku = $onlineSku;

        $valueHtml .= '<br/>' .
            '<strong>' . Mage::helper('M2ePro')->__('SKU') . ':</strong>&nbsp;' .
            Mage::helper('M2ePro')->escapeHtml($sku);

        return $valueHtml;
    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'online_sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%'),
                array('attribute'=>'online_title','like'=>'%'.$value.'%')
            )
        );
    }

    protected function callbackFilterOnlineQty($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $where = '';
        $onlineQty = 'elp.online_qty - elp.online_qty_sold';

        if (isset($cond['from']) || isset($cond['to'])) {
            if (isset($cond['from']) && $cond['from'] != '') {
                $value = $collection->getConnection()->quote($cond['from']);
                $where .= "{$onlineQty} >= {$value}";
            }

            if (isset($cond['to']) && $cond['to'] != '') {
                if (isset($cond['from']) && $cond['from'] != '') {
                    $where .= ' AND ';
                }

                $value = $collection->getConnection()->quote($cond['to']);
                $where .= "{$onlineQty} <= {$value}";
            }
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $cond = $column->getFilter()->getCondition();

        if (empty($cond)) {
            return;
        }

        $collection->addFieldToFilter('online_current_price', $cond);
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $index = $column->getIndex();

        if ($value == null) {
            return;
        }

        if (is_array($value) && isset($value['value'])) {
            $collection->addFieldToFilter($index, (int)$value['value']);
        } elseif (!is_array($value) && $value !== null) {
            $collection->addFieldToFilter($index, (int)$value);
        }

        if (is_array($value) && isset($value['is_duplicate'])) {
            $collection->addFieldToFilter('is_duplicate', 1);
        }
    }

    //########################################

    protected function _toHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        /** @var Ess_M2ePro_Model_Ebay_Template_Category $template */
        $template = Mage::getModel('M2ePro/Ebay_Template_Category')->load(
            $this->getRequest()->getParam('template_id')
        );
        $urls = array_merge(
            $helper->getControllerActions('adminhtml_ebay_listing'),
            $helper->getControllerActions('adminhtml_ebay_category')
        );

        $categoryMode = $this->getRequest()->getParam('active_tab') == CategoryViewTabs::TAB_ID_PRODUCTS_SECONDARY
            ? Chooser::MODE_EBAY_SECONDARY
            : Chooser::MODE_EBAY_PRIMARY;

        $path = 'adminhtml_ebay_listing/getCategoryChooserHtml';
        $urls[$path] = $this->getUrl('*/' . $path, array('category_mode' => $categoryMode));

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'Category Settings' => $helper->__('Category Settings'),
                'Specifics'         => $helper->__('Specifics'),
            )
        );

        $commonJs = <<<HTML
<script type="text/javascript">
    EbayCategoryGridObj.afterInitPage();
</script>
HTML;

        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $additionalJs = <<<HTML
<script type="text/javascript">
    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    EbayCategoryGridObj = new EbayCategoryGrid(
        '{$this->getId()}',
        '{$template->getMarketplaceId()}',
        null,
        '{$this->getRequest()->getParam('template_id')}',
     );
    
    EbayListingCategoryObj = new EbayListingCategory(EbayCategoryGridObj);
</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs . $commonJs;
    }

    //########################################
}
