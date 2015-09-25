<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database_Table_TableCellsPopup extends Mage_Adminhtml_Block_Widget
{
    const MODE_CREATE = 'create';
    const MODE_UPDATE = 'update';

    private $mode = self::MODE_UPDATE;

    public $tableName;
    public $modelName;
    public $component;

    public $mergeMode;

    public $rowsIds = array();

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentDatabaseTableCellsPopup');
        //------------------------------

        $this->mode = $this->getRequest()->getParam('mode');

        $this->tableName = $this->getRequest()->getParam('table');
        $this->modelName = $this->getRequest()->getParam('model');
        $this->component = $this->getRequest()->getParam('component');

        $this->mergeMode = (bool)$this->getRequest()->getParam('merge', false);

        $this->rowsIds = explode(',', $this->getRequest()->getParam('ids'));

        $this->setTemplate('M2ePro/development/tabs/database/table_cells_popup.phtml');
    }

    // ########################################

    protected function _toHtml()
    {
        // --------------------------------------
        $data = array(
            'id'      => 'development_database_update_cell_popup_confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'DevelopmentDatabaseGridHandlerObj.confirmUpdateCells();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('popup_confirm_update_button', $buttonBlock);
        // --------------------------------------

        // --------------------------------------
        $data = array(
            'id'      => 'development_database_add_cell_popup_confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'DevelopmentDatabaseGridHandlerObj.confirmAddRow();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('popup_confirm_add_button', $buttonBlock);
        // --------------------------------------

        return parent::_toHtml();
    }

    // ########################################

    public function getTableColumns()
    {
        $table = Mage::getModel("M2ePro/{$this->modelName}")->getResource()->getMainTable();
        $columns = Mage::helper('M2ePro/Module_Database_Structure')->getTableInfo($table);

        if ($this->ifNeedToUseMergeMode()) {

            array_walk($columns, function(&$el){ $el['is_parent'] = true; });

            $modelName = 'M2ePro/'.ucfirst($this->component).'_'.$this->modelName;
            $table = Mage::getModel($modelName)->getResource()->getMainTable();

            $childColumns = Mage::helper('M2ePro/Module_Database_Structure')->getTableInfo($table);
            array_walk($childColumns, function(&$el){ $el['is_parent'] = false; });

            $columns = array_merge($columns, $childColumns);
        }

        return $columns;
    }

    public function isUpdateCellsMode()
    {
        return $this->mode == self::MODE_UPDATE;
    }

    public function ifNeedToUseMergeMode()
    {
        return $this->mergeMode &&
               Mage::helper('M2ePro/Module_Database_Structure')->isTableHorizontal($this->tableName);
    }

    // ########################################
}