<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('templateDescriptionGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('M2ePro/Template_Description')->getCollection();
        $collection->addFieldToFilter('main_table.component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);

        $collection->getSelect()->join(
            array('atd' => Mage::getModel('M2ePro/Amazon_Template_Description')->getResource()->getMainTable()),
            'atd.template_description_id=main_table.id',
            array('marketplace_id','category_path','browsenode_id','is_new_asin_accepted')
        );

        $collection->getSelect()->join(
            array('mm' => Mage::getModel('M2ePro/Marketplace')->getResource()->getMainTable()),
            'mm.id=atd.marketplace_id',
            array('status')
        );

        $collection->addFieldToFilter('mm.status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'       => Mage::helper('M2ePro')->__('ID'),
            'align'        => 'right',
            'type'         => 'number',
            'width'        => '50px',
            'index'        => 'id',
            'filter_index' => 'main_table.id',
            'frame_callback' => array($this, 'callbackColumnId')
        ));

        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Title / Category'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '500px',
            'index'        => 'title',
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('new_asin', array(
                'header' => Mage::helper('M2ePro')->__('New ASIN / ISBN'),
                'align'        => 'left',
                'type'         => 'options',
                'options'      => array(
                    '1'  => Mage::helper('M2ePro')->__('Yes'),
                    '0'  => Mage::helper('M2ePro')->__('No')
                ),
                'width'        => '50px',
                'index'        => 'is_new_asin_accepted',
                'filter_index' => 'atd.is_new_asin_accepted'
            ));

        $this->addColumn('marketplace', array(
            'header'       => Mage::helper('M2ePro')->__('Marketplace'),
            'align'        => 'left',
            'type'         => 'options',
            'options'      => Mage::helper('M2ePro/Component_Amazon')->getMarketplacesAvailableForAsinCreation()
                                                                     ->toOptionHash(),
            'width'        => '100px',
            'index'        => 'marketplace_id',
            'filter_index' => 'atd.marketplace_id'
        ));

        $this->addColumn('update_date', array(
            'header'       => Mage::helper('M2ePro')->__('Update Date'),
            'align'        => 'left',
            'width'        => '80px',
            'type'         => 'datetime',
            'format'       => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'        => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $this->addColumn('create_date', array(
            'header'       => Mage::helper('M2ePro')->__('Creation Date'),
            'align'        => 'left',
            'width'        => '80px',
            'type'         => 'datetime',
            'format'       => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'        => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
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
                    'caption' => Mage::helper('M2ePro')->__('Delete'),
                    'confirm' => Mage::helper('M2ePro')->__('Are you sure?'),
                    'url'     => array('base' => '*/*/delete'),
                    'field'   => 'ids'
                ),
            )
        ));
    }

    // ####################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        // Set delete action
        //--------------------------------
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('M2ePro')->__('Delete'),
             'url'      => $this->getUrl('*/*/delete'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnId($value, $row, $column, $isExport)
    {
        return $value.'&nbsp;';
    }

    //--------------------------------

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $titleWord = Mage::helper('M2ePro')->__('Title');
        $title = Mage::helper('M2ePro')->escapeHtml($value);

        $categoryWord = Mage::helper('M2ePro')->__('Category');
        $categoryPath = !empty($row['category_path']) ? "{$row['category_path']} ({$row['browsenode_id']})"
                                                      : Mage::helper('M2ePro')->__('Not Set');
        return <<<HTML
<span style="font-weight: bold">{$titleWord}</span>: {$title}
<div>
    <span style="font-weight: bold">{$categoryWord}</span>: <span style="color: #505050">{$categoryPath}</span><br/>
</div>
HTML;
    }

    // ####################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            '`atd`.`category_path` LIKE ? OR `atd`.`browsenode_id` LIKE ? OR `main_table`.`title` LIKE ?',
            '%'. $value .'%'
        );
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_template_description/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/adminhtml_common_amazon_template_description/edit', array('id' => $row->getData('id')));
    }

    // ####################################
}