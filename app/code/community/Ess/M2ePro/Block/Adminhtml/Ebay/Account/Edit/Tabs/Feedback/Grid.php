<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit_Tabs_Feedback_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $accountData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountEditTabsFeedbackGrid'.$accountData->getId());
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        $accountData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        // Get collection of synchronizations
        $collection = Mage::getModel('M2ePro/Ebay_Feedback_Template')
                                    ->getCollection()
                                    ->addFieldToFilter('main_table.account_id', $accountData->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('ft_id', array(
            'header'    => Mage::helper('M2ePro')->__('ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'id',
            'filter_index' => 'main_table.id'
        ));

        $this->addColumn('ft_title', array(
            'header'    => Mage::helper('M2ePro')->__('Text'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'body',
            'escape'    => true,
            'filter_index' => 'main_table.body'
        ));

        $this->addColumn('ft_create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('ft_update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $this->addColumn('ft_action_edit', array(
            'header'    => Mage::helper('M2ePro')->__('Edit'),
            'align'     => 'left',
            'width'     => '50px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'frame_callback' => array($this, 'callbackActionEdit')
        ));

        $this->addColumn('ft_action_delete', array(
            'header'    => Mage::helper('M2ePro')->__('Delete'),
            'align'     => 'left',
            'width'     => '50px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'frame_callback' => array($this, 'callbackActionDelete')
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackActionEdit($value, $row, $column, $isExport)
    {
        $value = '<a href="javascript:void(0);" onclick="EbayAccountHandlerObj.feedbacksOpenEditForm(\''
                .$row->getData('id').'\',\''.Mage::helper('M2ePro')->escapeJs($row->getData('body')).'\');">'
                .Mage::helper('M2ePro')->__('Edit').'</a>';
        return $value;
    }

    public function callbackActionDelete($value, $row, $column, $isExport)
    {
        $value = '<a href="javascript:void(0);" onclick="EbayAccountHandlerObj.feedbacksDeleteAction(\''
                .$row->getData('id').'\');">'
                .Mage::helper('M2ePro')->__('Delete').'</a>';
        return $value;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/feedbackTemplateGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}