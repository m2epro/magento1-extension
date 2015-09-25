<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Account_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $viewComponentHelper = NULL;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialize view
        //------------------------------
        $view = Mage::helper('M2ePro/View')->getCurrentView();
        $this->viewComponentHelper = Mage::helper('M2ePro/View')->getComponentHelper($view);
        //------------------------------

        // Initialization block
        //------------------------------
        $this->setId($view . 'AccountGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('title');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Get collection of accounts
        $collection = $this->getCollection();
        if (is_null($collection)) {
            $collection = Mage::getModel('M2ePro/Account')->getCollection();
        }

        $components = $this->viewComponentHelper->getActiveComponents();
        $collection->addFieldToFilter('main_table.component_mode', array('in'=>$components));

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $confirm = 'Attention! By Deleting Account you delete all information on it from M2E Pro Server. ';
        $confirm .= 'This will cause inappropriate work of all Accounts\' copies.';
        $confirm = Mage::helper('M2ePro')->__($confirm);

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit'),
                    'url'       => array('base'=> '*/*/edit'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete'),
                    'url'       => array('base'=> '*/*/delete'),
                    'field'     => 'id',
                    'confirm'  => $confirm
                )
            )
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        // Set delete action
        //--------------------------------
        $confirm = 'Attention! By deleting Account you delete all information on it from M2E Pro Server. ';
        $confirm .= 'This will cause inappropriate work of all Accounts\' copies.';
        $confirm  = Mage::helper('M2ePro')->__($confirm);

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('M2ePro')->__('Delete'),
             'url'      => $this->getUrl('*/*/delete'),
             'confirm'  => $confirm
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/accountGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return Mage::helper('M2ePro/View')
            ->getUrl($row, 'account', 'edit', array('id' => $row->getData('id')));
    }

    // ####################################
}