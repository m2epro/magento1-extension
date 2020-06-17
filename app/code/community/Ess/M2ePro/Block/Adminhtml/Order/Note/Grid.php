<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Order_Note_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('orderNoteGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('M2ePro/Order_Note')->getCollection();
        $collection->addFieldToFilter('order_id', $this->getRequest()->getParam('id'));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id', array(
            'header'       => Mage::helper('M2ePro')->__('Description'),
            'align'        => 'left',
            'width'        => '*',
            'type'         => 'text',
            'sortable'     => false,
            'filter_index' => 'id',
            'index'        => 'note'
            )
        );

        $this->addColumn(
            'create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Create Date'),
            'align'     => 'left',
            'width'     => '165px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date'
            )
        );

        $this->addColumn(
            'actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'action',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
            'actions'   => array(
                array(
                    'caption'        => Mage::helper('M2ePro')->__('Edit'),
                    'onclick_action' => "OrderNoteObj.openEditNotePopup",
                    'field'          => 'id'
                ),
                array(
                    'caption'        => Mage::helper('M2ePro')->__('Delete'),
                    'onclick_action' => "OrderNoteObj.deleteNote",
                    'field'          => 'id'
                )
            )
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function getRowUrl($row)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_order/noteGrid', array('_current' => true));
    }

    //########################################
}