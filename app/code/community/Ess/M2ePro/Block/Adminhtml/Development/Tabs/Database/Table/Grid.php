<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database_Table_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const MERGE_MODE_COOKIE_KEY = 'database_tables_merge_mode_cookie_key';

    public $tableName;
    public $modelName;

    public $mergeMode = 0;
    public $component;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentTable'.$this->getRequest()->getParam('table').'Grid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------

        $this->init();
    }

    private function init()
    {
        $this->tableName = $this->getRequest()->getParam('table');
        $this->modelName = Mage::helper('M2ePro/Module_Database_Structure')->getTableModel($this->tableName);
        $this->component = $this->getRequest()->getParam('component');
        $this->mergeMode = Mage::app()->getCookie()->get(self::MERGE_MODE_COOKIE_KEY);

        if (!$this->modelName) {
            $errorMsg = str_replace(
                '%table_name%', $this->tableName, 'Specified table "%table_name%" cannot be managed.'
            );
            throw new Ess_M2ePro_Model_Exception($errorMsg);
        }

        if (!$this->ifNeedToUseMergeMode()) {
            return;
        }

        preg_match('/(ebay|amazon|buy)_/i', $this->modelName, $matches);
        if (!$this->component && !empty($matches[1])) {
            $this->modelName = str_replace($matches[1].'_', '', $this->modelName);
            $this->component = strtolower($matches[1]);
        }

        if (!$this->component) {
            $this->mergeMode = 0;
        }
    }

    //########################################

    private function getModel()
    {
        return !$this->ifNeedToUseMergeMode()
            ? Mage::getModel('M2ePro/'.$this->modelName)
            : Mage::helper('M2ePro/Component')->getComponentModel($this->component, $this->modelName);
    }

    private function ifNeedToUseMergeMode()
    {
        return $this->mergeMode &&
               Mage::helper('M2ePro/Module_Database_Structure')->isTableHorizontal($this->tableName);
    }

    //########################################

    protected function _prepareCollection()
    {
        $this->setCollection($this->getModel()->getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $table = Mage::getModel("M2ePro/{$this->modelName}")->getResource()->getMainTable();
        $columns = Mage::helper('M2ePro/Module_Database_Structure')->getTableInfo($table);

        if ($this->ifNeedToUseMergeMode()) {

            array_walk($columns, function(&$el) { $el['is_parent'] = true; });

            $modelName = 'M2ePro/'.ucfirst($this->component).'_'.$this->modelName;
            $table = Mage::getModel($modelName)->getResource()->getMainTable();

            $childColumns = Mage::helper('M2ePro/Module_Database_Structure')->getTableInfo($table);
            array_walk($childColumns, function(&$el) { $el['is_parent'] = false; });

            $columns = array_merge($columns, $childColumns);
        }

        foreach ($columns as $column) {

            $header = "<big>{$column['name']}</big> &nbsp;";
            if (isset($column['is_parent']) && $column['is_parent']) {
                $header = '<span style="color: orangered;">p:&nbsp;</span>' . $header;
            }
            if (isset($column['is_parent']) && !$column['is_parent']) {
                $header = '<span style="color: forestgreen;">ch:&nbsp;</span>' . $header;
            }
            $header .= "<small style=\"font-weight:normal;\">({$column['type']})</small>";

            $filterIndex = 'main_table.' . strtolower($column['name']);
            if (isset($column['is_parent']) && !$column['is_parent']) {
                $filterIndex = 'second_table.' . strtolower($column['name']);
            }

            $params = array(
                'header'         => $header,
                'align'          => 'left',
                'type'           => $this->getColumnType($column),
                'string_limit'   => 10000,
                'index'          => strtolower($column['name']),
                'filter_index'   => $filterIndex,
                'frame_callback' => array($this, 'callbackColumnData'),

                'is_auto_increment' => strpos($column['extra'], 'increment') !== false
            );

            if ($this->getColumnType($column) == 'datetime') {
                $params['align']       = 'right';
                $params['filter_time'] = true;
                $params['renderer'] = 'M2ePro/adminhtml_development_tabs_database_table_grid_column_renderer_datetime';
                $params['filter']   = 'M2ePro/adminhtml_development_tabs_database_table_grid_column_filter_datetime';
            }

            if ($this->tableName == 'm2epro_operation_history' && $column['name'] == 'nick') {
                $params['filter'] = 'M2ePro/adminhtml_development_tabs_database_table_grid_column_filter_select';
            }

            $this->addColumn($column['name'], $params);
        }

        $this->addColumn('actions_row', array(
            'header'    => '&nbsp;'.Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '70px',
            'type'      => 'text',
            'index'     => 'actions_row',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    protected function _toHtml()
    {
        $urlParams = array(
            'model'     => $this->modelName,
            'table'     => $this->tableName,
            'component' => $this->component,
            'merge'     => $this->mergeMode
        );

        $root = 'adminhtml_development_database';
        $urls = json_encode(array(
            $root.'/deleteTableRows'        => $this->getUrl('*/*/deleteTableRows', $urlParams),
            $root.'/updateTableCells'       => $this->getUrl('*/*/updateTableCells', $urlParams),
            $root.'/addTableRow'            => $this->getUrl('*/*/addTableRow', $urlParams),
            $root.'/getTableCellsPopupHtml' => $this->getUrl('*/*/getTableCellsPopupHtml', $urlParams),

            $root.'/manageTable' => $this->getUrl('*/*/manageTable', array('table' => $this->tableName)),
        ));

        $commonJs = <<<HTML
<script type="text/javascript">
    DevelopmentDatabaseGridHandlerObj.afterInitPage();
</script>
HTML;
        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $additionalJs = <<<HTML
<script type="text/javascript">

   M2ePro.url.add({$urls});
   DevelopmentDatabaseGridHandlerObj = new DevelopmentDatabaseGridHandler('{$this->getId()}');

</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs . $commonJs;
    }

    //########################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        // ---------------------------------------
        $this->getMassactionBlock()->addItem('deleteTableRows', array(
             'label'    => Mage::helper('M2ePro')->__('Delete'),
             'url'      => '',
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->getMassactionBlock()->addItem('updateTableCells', array(
            'label'    => Mage::helper('M2ePro')->__('Update'),
            'url'      => ''
        ));
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnData($value, $row, $column, $isExport)
    {
        $rowId = $row->getId();
        $columnId = $column->getId();
        $cellId = 'table_row_cell_'.$columnId.'_'.$rowId;

        $tempValue = '<span style="color:silver;"><small>NULL</small></span>';
        if (!is_null($value)) {
            $tempValue = strlen($value) > 255 ? substr($value,0,255).' ...' : $value;
            $tempValue = Mage::helper('M2ePro')->escapeHtml($tempValue);
        }

        $inputValue = 'NULL';
        !is_null($value) && $inputValue = Mage::helper('M2ePro')->escapeHtml($value);

        $divMouseActions = '';

        if (!$column->getData('is_auto_increment')) {
            $divMouseActions = <<<HTML
onmouseover="DevelopmentDatabaseGridHandlerObj.mouseOverCell('{$cellId}');"
onmouseout="DevelopmentDatabaseGridHandlerObj.mouseOutCell('{$cellId}');"
HTML;
        }

        return <<<HTML
<div style="min-height: 20px;" id="{$cellId}" {$divMouseActions}>

    <span id="{$cellId}_view_container">{$tempValue}</span>

    <span id="{$cellId}_edit_container" style="display: none;">
        <textarea style="width:100%; height:100%;" id="{$cellId}_edit_input"
                  onkeydown="DevelopmentDatabaseGridHandlerObj.onKeyDownEdit('{$rowId}','{$columnId}', event)"
>{$inputValue}</textarea>
    </span>

    <span id="{$cellId}_edit_link" style="display: none;">&nbsp;
        <a href="javascript:void(0);"
           onclick="DevelopmentDatabaseGridHandlerObj.switchCellToEdit('{$cellId}');">edit</a>
    </span>
    <span id="{$cellId}_view_link" style="display: none;">&nbsp;
        <a href="javascript:void(0);"
           onclick="DevelopmentDatabaseGridHandlerObj.switchCellToView('{$cellId}');">cancel</a>
    </span>
    <span id="{$cellId}_save_link" style="display: none;">&nbsp;
        <a href="javascript:void(0);"
           onclick="DevelopmentDatabaseGridHandlerObj.saveTableCell('{$rowId}','{$columnId}');">save</a>
    </span>
</div>
HTML;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $html = <<<HTML
<a href="javascript:void(0);" onclick="DevelopmentDatabaseGridHandlerObj.deleteTableRows('{$row->getId()}')">
    <span>delete</span>
</a>
HTML;
        $componentMode = $row->getData('component_mode');
        if (Mage::helper('M2ePro/Module_Database_Structure')->isTableHorizontalParent($this->tableName) &&
            $componentMode && !$this->mergeMode) {

            $html .= <<<HTML
<br/>
<a style="color: green;" href="javascript:void(0);"
   onclick="DevelopmentDatabaseGridHandlerObj.mergeParentTable('{$componentMode}')">
    <span>join</span>
</a>
HTML;
        }

        return $html;
    }

    //########################################

    protected function _addColumnFilterToCollection($column)
    {
        if (!$this->getCollection()) {
            return $this;
        }

        $value = $column->getFilter()->getValue();
        $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex()
                                               : $column->getIndex();

        if ($this->isNullFilter($value)) {
            $this->getCollection()->addFieldToFilter($field, array('null' => true));
            return $this;
        }

        if ($this->isNotIsNullFilter($value)) {
            $this->getCollection()->addFieldToFilter($field, array('notnull' => true));
            return $this;
        }

        return parent::_addColumnFilterToCollection($column);
    }

    private function isNullFilter($value)
    {
        if ($value === 'isnull') {
            return true;
        }

        if (isset($value['from'] ,$value['to']) && $value['from'] === 'isnull' && $value['to'] === 'isnull') {
            return true;
        }

        return false;
    }

    private function isNotIsNullFilter($value)
    {
        if ($value === '!isnull') {
            return true;
        }

        if (isset($value['from'] ,$value['to']) && $value['from'] === '!isnull' && $value['to'] === '!isnull') {
            return true;
        }

        return false;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/databaseTableGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        //return $this->getUrl('*/*/editTableRow', array('id' => $row->getId()));
    }

    //########################################

    private function getColumnType($columnData)
    {
        if ($columnData['type'] == 'datetime') {
            return 'datetime';
        }

        if (preg_match('/int|float|decimal/', $columnData['type'])) {
            return 'number';
        }

        return 'text';
    }

    //########################################
}