<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id', array(
                'header'    => Mage::helper('M2ePro')->__('ID'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'id',
                'filter_index' => 'main_table.id'
            )
        );

        $this->addColumn(
            'title', array(
                'header'    => Mage::helper('M2ePro')->__('Title / Info'),
                'align'     => 'left',
                'type'      => 'text',
                'index'     => 'title',
                'filter_index' => 'main_table.title',
                'frame_callback' => array($this, 'callbackColumnTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'products_total_count', array(
                'header'    => Mage::helper('M2ePro')->__('Total Items'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'products_total_count',
                'filter_index' => 't.products_total_count',
                'frame_callback' => array($this, 'callbackColumnProductsCount')
            )
        );

        $this->addColumn(
            'products_active_count', array(
                'header'    => Mage::helper('M2ePro')->__('Active Items'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'products_active_count',
                'filter_index' => 't.products_active_count',
                'frame_callback' => array($this, 'callbackColumnProductsCount')
            )
        );

        $this->addColumn(
            'products_inactive_count', array(
                'header'    => Mage::helper('M2ePro')->__('Inactive Items'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'products_inactive_count',
                'filter_index' => 't.products_inactive_count',
                'frame_callback' => array($this, 'callbackColumnProductsCount')
            )
        );

        $this->setColumns();

        $this->addColumn(
            'actions', array(
                'header'    => Mage::helper('M2ePro')->__('Actions'),
                'align'     => 'left',
                'width'     => '150px',
                'type'      => 'action',
                'index'     => 'actions',
                'filter'    => false,
                'sortable'  => false,
                'getter'    => 'getId',
                'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
                'group_order' => $this->getGroupOrder(),
                'actions'     => $this->getColumnActionsItems()
            )
        );

        return parent::_prepareColumns();
    }

    protected function setColumns()
    {
        return null;
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return $value;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        return null;
    }

    // ---------------------------------------

    public function callbackColumnProductsCount($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    // ---------------------------------------

    protected function getGroupOrder()
    {
        return array(
            'products_actions' => Mage::helper('M2ePro')->__('Products'),
            'edit_actions'     => Mage::helper('M2ePro')->__('Edit Settings'),
            'other'            => Mage::helper('M2ePro')->__('Other'),
        );
    }

    protected function getColumnActionsItems()
    {
        return array();
    }

    //########################################
}
