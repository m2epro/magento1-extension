<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Tabs_Database_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /* The table is excluded because it uses a composite primary key that causes magenta to fail */
    private $excludedTables = array('m2epro_ebay_account_store_category');

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelDatabaseGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('component');
        $this->setDefaultDir('ASC');
        $this->setDefaultLimit(50);

        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

   //########################################

    protected function _prepareCollection()
    {
        $magentoHelper   = Mage::helper('M2ePro/Magento');
        $structureHelper = Mage::helper('M2ePro/Module_Database_Structure');

        $tablesList = $magentoHelper->getMySqlTables();
        foreach ($tablesList as &$tableName) {
            $tableName = $structureHelper->getTableNameWithoutPrefix($tableName);
        }

        $tablesList = array_unique(array_merge($tablesList, $structureHelper->getModuleTables()));

        $collection = new Ess_M2ePro_Model_Collection_Custom();
        foreach ($tablesList as $tableName) {
            if (in_array($tableName, $this->excludedTables, true) || !$structureHelper->isModuleTable($tableName)) {
                continue;
            }

            $tableRow = array(
                'table_name' => $tableName,
                'component'  => '',
                'is_exist'   => $isExists = $structureHelper->isTableExists($tableName),
                'records'    => 0,
                'size'       => 0,
                'model'      => $structureHelper->getTableModel($tableName)
            );

            if ($tableRow['is_exist']) {
                $tableRow['component'] = $structureHelper->getTableComponent($tableName);
                $tableRow['size']      = $structureHelper->getDataLength($tableName);
                $tableRow['records']   = $structureHelper->getCountOfRecords($tableName);
            }

            $collection->addItem(new Varien_Object($tableRow));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'table_name', array(
            'header'    => Mage::helper('M2ePro')->__('Table Name'),
            'align'     => 'left',
            'index'     => 'table_name',
            'filter_index' => 'table_name',
            'frame_callback' => array($this, 'callbackColumnTableName'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle'),
            )
        );

        $options['general'] = 'General';
        $options = array_merge($options, Mage::helper('M2ePro/Component')->getComponentsTitles());

        $this->addColumn(
            'component', array(
            'header'    => Mage::helper('M2ePro')->__('Component'),
            'align'     => 'right',
            'width'     => '120px',
            'index'     => 'component',
            'type'      => 'options',
            'options'   => $options,
            'filter_index' => 'component',
            'filter_condition_callback' => array($this, 'callbackFilterMatch'),
            )
        );

        $this->addColumn(
            'records', array(
            'header'    => Mage::helper('M2ePro')->__('Records'),
            'align'     => 'right',
            'width'     => '100px',
            'index'     => 'records',
            'type'      => 'number',
            'filter'    => false,
            )
        );

        $this->addColumn(
            'size', array(
            'header'    => Mage::helper('M2ePro')->__('Size (Mb)'),
            'align'     => 'right',
            'width'     => '100px',
            'index'     => 'size',
            'filter'    => false,
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnTableName($value, $row, $column, $isExport)
    {
        if (!$row->getData('is_exist')) {
            return "<p style=\"color: red; font-weight: bold;\">{$value} [table is not exists]</p>";
        }

        if (!$row->getData('model')) {
            return "<p style=\"color: #878787;\">{$value}</p>";
        }

        return "<p>{$value}</p>";
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_controlPanel_database/databaseGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        if (!$row->getData('is_exist') || !$row->getData('model')) {
            return false;
        }

        return $this->getUrl(
            '*/adminhtml_controlPanel_database/manageTable',
            array('table' => $row->getData('table_name'))
        );
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $this->getCollection()->addFilter(
            'table_name', $value, Ess_M2ePro_Model_Collection_Custom::CONDITION_LIKE
        );
    }

    protected function callbackFilterMatch($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex()
                                           : $column->getIndex();

        $value = $column->getFilter()->getValue();
        if ($value == null || empty($field)) {
            return;
        }

        $this->getCollection()->addFilter(
            $field, $value, Ess_M2ePro_Model_Collection_Custom::CONDITION_MATCH
        );
    }

    //########################################
}
