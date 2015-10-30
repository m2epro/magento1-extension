<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_CronScheduleTable_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('cronScheduleTable');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        // Get collection of prices templates
        $collection = Mage::getModel('cron/schedule')->getCollection();

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('schedule_id', array(
            'header'    => Mage::helper('M2ePro')->__('ID'),
            'align'     => 'right',
            'type'      => 'number',
            'index'     => 'schedule_id',
            'filter_index' => 'schedule_id',
        ));

        $this->addColumn('job_code', array(
            'header'    => Mage::helper('M2ePro')->__('Job Code'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'job_code',
            'filter_index' => 'job_code',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('M2ePro')->__('Status'),
            'align'     => 'left',
            'width'     => '50px',
            'type'      => 'text',
            'index'     => 'status',
            'filter_index' => 'status',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('M2ePro')->__('Created At'),
            'align'     => 'right',
            'width'     => '150px',
            'type'      => 'datetime',
            'index'     => 'created_at',
            'filter_index' => 'created_at',
            'frame_callback' => array($this, 'callbackColumnIfEmptyThenNotAvailable'),
        ));

        $this->addColumn('scheduled_at', array(
            'header'    => Mage::helper('M2ePro')->__('Scheduled At'),
            'align'     => 'right',
            'width'     => '150px',
            'type'      => 'datetime',
            'index'     => 'scheduled_at',
            'filter_index' => 'scheduled_at',
            'frame_callback' => array($this, 'callbackColumnIfEmptyThenNotAvailable'),
        ));

        $this->addColumn('executed_at', array(
            'header'    => Mage::helper('M2ePro')->__('Executed At'),
            'align'     => 'right',
            'width'     => '150px',
            'type'      => 'datetime',
            'index'     => 'executed_at',
            'filter_index' => 'executed_at',
            'frame_callback' => array($this, 'callbackColumnIfEmptyThenNotAvailable'),
        ));

        $this->addColumn('finished_at', array(
            'header'    => Mage::helper('M2ePro')->__('Finished At'),
            'align'     => 'right',
            'width'     => '150px',
            'type'      => 'datetime',
            'index'     => 'finished_at',
            'filter_index' => 'finished_at',
            'frame_callback' => array($this, 'callbackColumnIfEmptyThenNotAvailable'),
        ));

        $this->addColumn('messages', array(
            'header'    => Mage::helper('M2ePro')->__('Messages'),
            'align'     => 'center',
            'width'     => '40px',
            'type'      => 'text',
            'index'     => 'messages',
            'frame_callback' => array($this, 'callbackColumnMessages'),
        ));

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnIfEmptyThenNotAvailable($value, $row, $column, $isExport)
    {
        return empty($value) ? 'N/A' : $value;
    }

    public function callbackColumnMessages($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return 'N/A';
        }

        $url = $this->getUrl('*/*/cronScheduleTableShowMessages', array('id' => $row->getId()));

        return <<<HTML
<a href="javascript:" onclick="window.open('{$url}')">View</a>
HTML;
    }

    //########################################
}