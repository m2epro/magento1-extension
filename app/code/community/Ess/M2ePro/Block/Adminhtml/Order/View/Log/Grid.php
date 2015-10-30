<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Order_View_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('orderViewLogGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
        // ---------------------------------------

        /** @var $order Ess_M2ePro_Model_Order */
        $this->order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('M2ePro/Order_Log')->getCollection();
        $collection->addFieldToFilter('order_id', $this->order->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('M2ePro')->__('Message'),
            'align'     => 'left',
            'width'     => '*',
            'type'      => 'text',
            'sortable'  => false,
            'filter_index' => 'id',
            'index'     => 'description',
            'frame_callback' => array($this, 'callbackColumnDescription')
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '65px',
            'index'     => 'type',
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnType')
        ));

        $this->addColumn('initiator', array(
            'header'    => Mage::helper('M2ePro')->__('Run Mode'),
            'align'     => 'left',
            'width'     => '65px',
            'index'     => 'initiator',
            'sortable'  => false,
            'type'      => 'options',
            'options'   => array(
                Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN   => Mage::helper('M2ePro')->__('Unknown'),
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION => Mage::helper('M2ePro')->__('Automatic'),
                Ess_M2ePro_Helper_Data::INITIATOR_USER      => Mage::helper('M2ePro')->__('Manual'),
            ),
            'frame_callback' => array($this, 'callbackColumnInitiator')
        ));

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Create Date'),
            'align'     => 'left',
            'width'     => '165px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date'
        ));

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnDescription($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro/View')->getModifiedLogMessage($row->getData('description'));
    }

    public function callbackColumnType($value, $row, $column, $isExport)
    {
        switch ($value) {
            case Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS:
                $message = '<span style="color: green;">'.Mage::helper('M2ePro')->__('Success').'</span>';
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE:
                $message = '<span style="color: blue;">'.Mage::helper('M2ePro')->__('Notice').'</span>';
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING:
                $message = '<span style="color: orange;">'.Mage::helper('M2ePro')->__('Warning').'</span>';
                break;
            case Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR:
            default:
                $message = '<span style="color: red;">'.Mage::helper('M2ePro')->__('Error').'</span>';
                break;
        }

        return $message;
    }

    public function callbackColumnInitiator($value, $row, $column, $isExport)
    {
        $initiator = $row->getData('initiator');

        switch ($initiator) {
            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                $message = "<span style=\"text-decoration: underline;\">{$value}</span>";
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN:
                $message = "<span style=\"font-style: italic; color: gray;\">{$value}</span>";
                break;
            case Ess_M2ePro_Helper_Data::INITIATOR_USER:
            default:
                $message = "<span>{$value}</span>";
                break;
        }

        return $message;
    }

    //########################################

    public function getRowUrl($row)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_order/viewLogGrid', array('_current' => true));
    }

    //########################################
}