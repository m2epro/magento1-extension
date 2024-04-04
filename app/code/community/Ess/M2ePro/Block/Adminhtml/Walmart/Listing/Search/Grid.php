<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id', array(
                'header'       => Mage::helper('M2ePro')->__('Product ID'),
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
                'header'         => Mage::helper('M2ePro')->__('Product Title / Listing / Product SKU'),
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
                'header'    => Mage::helper('M2ePro')->__('Stock Availability'),
                'align'     => 'left',
                'width'     => '90px',
                'type'      => 'options',
                'sortable'  => false,
                'index'     => 'is_in_stock',
                'filter_index' => 'is_in_stock',
                'options' => array(
                    '1' => Mage::helper('M2ePro')->__('In Stock'),
                    '0' => Mage::helper('M2ePro')->__('Out of Stock')
                ),
                'frame_callback' => array($this, 'callbackColumnIsInStock')
            )
        );

        $this->addColumn(
            'online_sku', array(
                'header'                    => Mage::helper('M2ePro')->__('SKU'),
                'align'                     => 'left',
                'width'                     => '150px',
                'type'                      => 'text',
                'index'                     => 'online_sku',
                'filter_index'              => 'online_sku',
                'show_edit_sku'             => false,
                'renderer'                  => 'M2ePro/adminhtml_walmart_grid_column_renderer_sku',
                'filter_condition_callback' => array($this, 'callbackFilterOnlineSku')
            )
        );

        $this->addColumn(
            'gtin', array(
                'header'                    => Mage::helper('M2ePro')->__('GTIN'),
                'align'                     => 'left',
                'width'                     => '150px',
                'type'                      => 'text',
                'index'                     => 'gtin',
                'filter_index'              => 'gtin',
                'show_edit_identifier'      => false,
                'renderer'                  => 'M2ePro/adminhtml_walmart_grid_column_renderer_gtin',
                'filter_condition_callback' => array($this, 'callbackFilterGtin')
            )
        );

        $this->addColumn(
            'online_qty', array(
                'header'         => Mage::helper('M2ePro')->__('QTY'),
                'align'          => 'right',
                'width'          => '70px',
                'type'           => 'number',
                'index'          => 'online_qty',
                'filter_index'   => 'online_qty',
                'renderer'                  => 'M2ePro/adminhtml_walmart_grid_column_renderer_qty',
                'filter_condition_callback' => array($this, 'callbackFilterQty')
            )
        );

        $this->addColumn(
            'online_price', array(
                'header'         => Mage::helper('M2ePro')->__('Price'),
                'align'          => 'right',
                'width'          => '110px',
                'type'           => 'number',
                'index'          => 'online_price',
                'filter_index'   => 'online_price',
                'frame_callback' => array($this, 'callbackColumnPrice'),
                'filter_condition_callback' => array($this, 'callbackFilterPrice')
            )
        );

        $statusColumn = array(
            'header'       => Mage::helper('M2ePro')->__('Status'),
            'width'        => '170px',
            'index'        => 'status',
            'filter_index' => 'status',
            'type'         => 'options',
            'sortable'     => false,
            'options' => array(
                Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                Ess_M2ePro_Model_Listing_Product::STATUS_LISTED     => Mage::helper('M2ePro')->__('Active'),
                Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE   => Mage::helper('M2ePro')->__('Inactive'),
                Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED    => Mage::helper('M2ePro')->__('Incomplete')
            ),
            'frame_callback'            => array($this, 'callbackColumnStatus'),
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        );

        $listingType = $this->getRequest()->getParam(
            'listing_type', Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_M2E_PRO
        );

        if ($listingType == Ess_M2ePro_Block_Adminhtml_Listing_Search_Switcher::LISTING_TYPE_LISTING_OTHER) {
            unset($statusColumn['options'][Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED]);
        }

        $this->addColumn('status', $statusColumn);

        $this->addColumn(
            'goto_listing_item', array(
                'header'    => Mage::helper('M2ePro')->__('Manage'),
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

    abstract public function callbackColumnProductTitle($value, $row, $column, $isExport);
    abstract public function callbackColumnStatus($value, $row, $column, $isExport);
    abstract public function callbackColumnActions($value, $row, $column, $isExport);
    abstract public function callbackColumnPrice($value, $row, $column, $isExport);

    //----------------------------------------

    protected function getProductStatus($row, $status)
    {
        switch ($status) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED:
                return '<span style="color: gray;">' . Mage::helper('M2ePro')->__('Not Listed') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                return '<span style="color: green;">' . Mage::helper('M2ePro')->__('Active') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE:
                return'<span style="color: red;">' . Mage::helper('M2ePro')->__('Inactive') . '</span>';

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                return'<span style="color: orange; font-weight: bold;">' .
                Mage::helper('M2ePro')->__('Incomplete') . '</span>';
        }

        return '';
    }

    //########################################

    abstract protected function callbackFilterProductId($collection, $column);
    abstract protected function callbackFilterTitle($collection, $column);
    abstract protected function callbackFilterOnlineSku($collection, $column);
    abstract protected function callbackFilterGtin($collection, $column);
    abstract protected function callbackFilterQty($collection, $column);
    abstract protected function callbackFilterPrice($collection, $column);
    abstract protected function callbackFilterStatus($collection, $column);

    //########################################

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    //########################################

    protected function getStatusChangeReasons($statusChangeReasons)
    {
        if (empty($statusChangeReasons)) {
            return '';
        }

        $html = '<li style="margin-bottom: 5px;">'
            . implode('</li><li style="margin-bottom: 5px;">', $statusChangeReasons)
            . '</li>';

        return <<<HTML
        <div style="display: inline-block; width: 16px; margin-left: 3px; margin-right: 4px;">
            <img class="tool-tip-image"
                 style="vertical-align: middle;"
                 src="{$this->getSkinUrl('M2ePro/images/i_icon.png')}">
            <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
                <img src="{$this->getSkinUrl('M2ePro/images/i_logo.png')}">
                <ul>
                    {$html}
                </ul>
            </span>
        </div>
HTML;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_walmart_listing/searchGrid', array('_current'=>true));
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
