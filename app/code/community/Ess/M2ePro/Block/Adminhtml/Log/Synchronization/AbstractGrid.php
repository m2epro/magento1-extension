<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Synchronization_AbstractGrid extends
    Ess_M2ePro_Block_Adminhtml_Log_AbstractGrid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('synchronizationLogGrid' . ucfirst($this->getComponentMode()));

        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    //########################################

    abstract public function getComponentMode();

    //########################################

    protected function _getLogTypeList()
    {
        return array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING            => Mage::helper('M2ePro')->__('Warning'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR              => Mage::helper('M2ePro')->__('Error'),
            Ess_M2ePro_Model_Synchronization_Log::TYPE_FATAL_ERROR => Mage::helper('M2ePro')->__('Fatal Error')
        );
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('M2ePro/Synchronization_Log')->getCollection();
        $collection->getSelect()->where('component_mode IS NULL OR component_mode = ?', $this->getComponentMode());

        $allTitles = Mage::helper('M2ePro/Module_Log')->getActionsTitlesByClass('Synchronization_Log');
        if (count($this->getActionTitles()) != count($allTitles)) {
            $excludeTasks = array_diff($allTitles, $this->getActionTitles());
            $collection->addFieldToFilter('task', array('nin' => array_keys($excludeTasks)));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'create_date',
            array(
                'header' => Mage::helper('M2ePro')->__('Date'),
                'align'  => 'left',
                'type'   => 'datetime',
                'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'width'  => '150px',
                'index'  => 'create_date'
            )
        );

        $this->addColumn(
            'task',
            array(
                'header'       => Mage::helper('M2ePro')->__('Task'),
                'align'        => 'left',
                'width'        => '200px',
                'type'         => 'options',
                'index'        => 'task',
                'sortable'     => false,
                'filter_index' => 'main_table.task',
                'options'      => $this->getActionTitles()
            )
        );

        $this->addColumn(
            'description',
            array(
                'header'         => Mage::helper('M2ePro')->__('Message'),
                'align'          => 'left',
                'type'           => 'text',
                'string_limit'   => 350,
                'index'          => 'description',
                'filter_index'   => 'main_table.description',
                'frame_callback' => array($this, 'callbackColumnDescription')
            )
        );

        $this->addColumn(
            'detailed_description',
            array(
                'header'         => Mage::helper('M2ePro')->__('Detailed'),
                'align'          => 'left',
                'type'           => 'text',
                'string_limit'   => 65000,
                'index'          => 'detailed_description',
                'filter_index'   => 'main_table.detailed_description',
                'frame_callback' => array($this, 'callbackColumnDetailedDescription')
            )
        );

        $this->addColumn(
            'type',
            array(
                'header'         => Mage::helper('M2ePro')->__('Type'),
                'width'          => '120px',
                'index'          => 'type',
                'align'          => 'right',
                'type'           => 'options',
                'sortable'       => false,
                'options'        => $this->_getLogTypeList(),
                'frame_callback' => array($this, 'callbackColumnType')
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
    }

    //########################################

    public function callbackColumnDetailedDescription($value, $row, $column, $isExport)
    {
        $fullDescription = Mage::helper('M2ePro/View')->getModifiedLogMessage($row->getData('detailed_description'));

        $renderedText = $this->stripTags($fullDescription, '<br>');
        if (strlen($renderedText) < 200) {
            return $fullDescription;
        }

        $renderedText =  Mage::helper('core/string')->truncate($renderedText, 200, '');

        return <<<HTML
{$renderedText}
<a href="javascript://" onclick="LogObj.showFullText(this);">
    {$this->__('more')}
</a>
<div class="no-display">{$fullDescription}</div>
HTML;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/synchronizationGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    /**
     * Implements by using traits
     */
    abstract protected function getExcludedActionTitles();

    // ---------------------------------------

    protected function getActionTitles()
    {
        $allActions = Mage::helper('M2ePro/Module_Log')->getActionsTitlesByClass('Synchronization_Log');

        return array_diff_key($allActions, $this->getExcludedActionTitles());
    }

    //########################################
}
