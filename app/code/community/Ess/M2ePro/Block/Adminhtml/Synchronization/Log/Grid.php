<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Synchronization_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
{
    protected $_viewComponentHelper = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $task    = $this->getRequest()->getParam('task');
        $channel = $this->getRequest()->getParam('channel');

        $this->setId('synchronizationLogGrid' . ($task !== null ? $task : '') . ucfirst($channel));

        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $filters = array();
        $task !== null && $filters['task'] = $task;
        $task !== null && $filters['component_mode'] = $channel;
        $this->setDefaultFilter($filters);
    }

    //########################################

    protected function _prepareCollection()
    {
        // Get collection logs
        // ---------------------------------------
        $collection = Mage::getModel('M2ePro/Synchronization_Log')->getCollection();
        // ---------------------------------------

        $channel = $this->getRequest()->getParam('channel');
        if (!empty($channel)) {
            $collection->getSelect()->where('component_mode = ?', $channel);
        } else {
            $components = Mage::helper('M2ePro/Component')->getEnabledComponents();
            $collection->getSelect()
                ->where('component_mode IN(\'' . implode('\',\'', $components) . '\') OR component_mode IS NULL');
        }

        // some actions must be excluded
        // ---------------------------------------
        $allTitles = Mage::helper('M2ePro/Module_Log')->getActionsTitlesByClass('Synchronization_Log');
        if (count($this->getActionTitles()) != count($allTitles)) {
            $excludeTasks = array_diff($allTitles, $this->getActionTitles());
            $collection->getSelect()->where('task NOT IN ('.implode(',', array_keys($excludeTasks)).')');
        }

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'width'     => '150px',
            'index'     => 'create_date'
            )
        );

        $this->addColumn(
            'task', array(
            'header'    => Mage::helper('M2ePro')->__('Synchronization'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'options',
            'index'     => 'task',
            'sortable'  => false,
            'filter_index' => 'main_table.task',
            'options' => $this->getActionTitles()
            )
        );

        $this->addColumn(
            'description', array(
            'header'    => Mage::helper('M2ePro')->__('Description'),
            'align'     => 'left',
            'type'      => 'text',
            'string_limit' => 350,
            'index'     => 'description',
            'filter_index' => 'main_table.description',
            'frame_callback' => array($this, 'callbackDescription')
            )
        );

        $this->addColumn(
            'type', array(
            'header'=> Mage::helper('M2ePro')->__('Type'),
            'width' => '80px',
            'index' => 'type',
            'align'     => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogTypeList(),
            'frame_callback' => array($this, 'callbackColumnType')
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/synchronizationGrid', array(
            '_current'=>true,
            'channel' => $this->getRequest()->getParam('channel')
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}
