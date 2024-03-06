<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_Qty as OnlineQty;

abstract class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    protected function _prepareColumns()
    {
        $helper = Mage::helper('M2ePro');

        $this->addColumn(
            'product_id', array(
                'header'       => $helper->__('Product ID'),
                'align'        => 'right',
                'width'        => '100px',
                'type'         => 'number',
                'index'        => 'entity_id',
                'filter_index' => 'entity_id',
                'renderer'     => 'M2ePro/adminhtml_grid_column_renderer_productId',
                'filter_condition_callback' => array($this, 'callbackFilterProductId')
            )
        );

        $this->addColumn(
            'name', array(
                'header'         => $helper->__('Product Title / Listing / Product SKU'),
                'align'          => 'left',
                'type'           => 'text',
                'index'          => 'name',
                'filter_index'   => 'name',
                'frame_callback' => array($this, 'callbackColumnProductTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'is_in_stock', array(
                'header'    => $helper->__('Stock Availability'),
                'align'     => 'left',
                'width'     => '90px',
                'type'      => 'options',
                'sortable'  => false,
                'index'     => 'is_in_stock',
                'filter_index' => 'is_in_stock',
                'options' => array(
                    '1' => $helper->__('In Stock'),
                    '0' => $helper->__('Out of Stock')
                ),
                'frame_callback' => array($this, 'callbackColumnIsInStock')
            )
        );

        $this->addColumn(
            'item_id', array(
                'header'       => $helper->__('Item ID'),
                'align'        => 'left',
                'width'        => '100px',
                'type'         => 'text',
                'index'        => 'item_id',
                'filter_index' => 'item_id',
                'renderer'     => 'M2ePro/adminhtml_ebay_grid_column_renderer_itemId',
                'filter_condition_callback' => array($this, 'callbackFilterItemId')
            )
        );

        $this->addColumn(
            'online_qty', array(
                'header'            => $helper->__('Available QTY'),
                'align'             => 'right',
                'width'             => '50px',
                'type'              => 'number',
                'index'             => 'online_qty',
                'filter_index'      => 'online_qty',
                'renderer'          => 'M2ePro/adminhtml_ebay_grid_column_renderer_qty',
                'render_online_qty' => OnlineQty::ONLINE_AVAILABLE_QTY,
                'filter_condition_callback' => array($this, 'callbackFilterOnlineQty')
            )
        );

        $this->addColumn(
            'online_qty_sold', array(
                'header'       => $helper->__('Sold QTY'),
                'align'        => 'right',
                'width'        => '50px',
                'type'         => 'number',
                'index'        => 'online_qty_sold',
                'filter_index' => 'online_qty_sold',
                'renderer'     => 'M2ePro/adminhtml_ebay_grid_column_renderer_qty',
            )
        );

        $this->addColumn(
            'price', array(
                'header'       => $helper->__('Price'),
                'align'        => 'right',
                'width'        => '50px',
                'type'         => 'number',
                'index'        => 'online_current_price',
                'filter_index' => 'online_current_price',
                'renderer'     => 'M2ePro/adminhtml_ebay_grid_column_renderer_currentPrice',
                'filter_condition_callback' => array($this, 'callbackFilterPrice')
            )
        );

        $statusColumn = array(
            'header'       => $helper->__('Status'),
            'width'        => '100px',
            'index'        => 'status',
            'filter_index' => 'status',
            'type'         => 'options',
            'sortable'     => false,
            'options'      => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => $helper->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => $helper->__('Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN     => $helper->__('Listed (Hidden)'),
                Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE   => $helper->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => $helper->__('Pending')
            ),
            'renderer'     => 'M2ePro/adminhtml_ebay_grid_column_renderer_status',
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        );

        $listingType = $this->getRequest()->getParam(
            'listing_type', Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_M2E_PRO
        );

        if (Mage::helper('M2ePro/View_Ebay')->isDuplicatesFilterShouldBeShown() &&
            $listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_M2E_PRO) {
            $statusColumn['filter'] = 'M2ePro/adminhtml_ebay_grid_column_filter_status';
        }

        if ($listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_LISTING_OTHER) {
            unset($statusColumn['options'][Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED]);
        }

        $this->addColumn('status', $statusColumn);

        $this->addColumn(
            'goto_listing_item', array(
                'header'    => $helper->__('Manage'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'text',
                'filter'    => false,
                'sortable'  => false,
                'frame_callback' => array($this, 'callbackColumnActions')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        $title       = $row->getData('name');
        $onlineTitle = $row->getData('online_title');

        !empty($onlineTitle) && $title = $onlineTitle;
        $value = '<span>' . Mage::helper('M2ePro')->escapeHtml($title) . '</span>';

        $additionalHtml = $this->getColumnProductTitleAdditionalHtml($row);

        if (!empty($additionalHtml)) {
            $value .= '<br/><hr style="border: none; border-top: 1px solid silver; margin: 2px 0px;"/>' .
                      $additionalHtml;
        }

        return $value;
    }

    //----------------------------------------

    protected function getColumnProductTitleAdditionalHtml($row)
    {
        return '';
    }

    //########################################

    public abstract function callbackColumnActions($value, $row, $column, $isExport);

    //########################################

    abstract protected function callbackFilterProductId($collection, $column);
    abstract protected function callbackFilterTitle($collection, $column);
    abstract protected function callbackFilterPrice($collection, $column);
    abstract protected function callbackFilterOnlineQty($collection, $column);
    abstract protected function callbackFilterStatus($collection, $column);
    abstract protected function callbackFilterItemId($collection, $column);

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
        return $this->getUrl('*/adminhtml_ebay_listing/searchGrid', array('_current'=>true));
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
