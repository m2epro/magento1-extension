<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_DatabaseController
    extends Ess_M2ePro_Controller_Adminhtml_Development_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()->getLayout()->getBlock('head')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Development/DatabaseGridHandler.js');

        $this->_initPopUp();

        return $this;
    }

    //#############################################

    public function manageTableAction()
    {
        $this->_initAction();

        $table = $this->getRequest()->getParam('table');

        if (is_null($table)) {
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
            return;
        }

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_development_tabs_database_table'))
             ->renderLayout();
    }

    public function manageTablesAction()
    {
        $tables = $this->getRequest()->getParam('tables', array());

        $response = '';
        foreach ($tables as $table) {

            if (is_null(Mage::helper('M2ePro/Module_Database_Structure')->getTableModel($table))) {
                continue;
            }

            $url = Mage::helper('adminhtml')->getUrl('*/adminhtml_development_database/manageTable',
                                                     array('table' => $table));

            $response .= "window.open('{$url}');";
        }

        $backUrl = Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl();

        $response = "<script>
                        {$response}
                        window.location = '{$backUrl}';
                     </script>";

        $this->getResponse()->setBody($response);
    }

    public function truncateTablesAction()
    {
        $tables = $this->getRequest()->getParam('tables', array());
        !is_array($tables) && $tables = array($tables);

        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach ($tables as $table) {

            $tableName = Mage::getSingleton('core/resource')->getTableName($table);
            $writeConnection->delete($tableName);

            $this->afterTableAction($table);
        }

        $this->_getSession()->addSuccess('Truncate Tables was successfully completed.');

        if (count($tables) == 1) {
            $this->redirectToTablePage($tables[0]);
        }
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
    }

    //---------------------------------------------

    public function addTableRowAction()
    {
        $table         = $this->getRequest()->getParam('table');
        $modelInstance = $this->getModel();
        $cellsValues   = $this->prepareCellsValuesArray();

        if (!$modelInstance || empty($cellsValues)) {
            return;
        }

        $modelInstance->setData($cellsValues)->save();
        $this->afterTableAction($table);
    }

    public function deleteTableRowsAction()
    {
        $table = $this->getRequest()->getParam('table');
        $modelInstance = $this->getModel();
        $ids = explode(',', $this->getRequest()->getParam('ids'));

        if (!$modelInstance || empty($ids)) {
            $this->_getSession()->addError("Failed to get model or any of Table Rows are not selected.");
            $this->redirectToTablePage($table);
        }

        $collection = $modelInstance->getCollection();
        $collection->addFieldToFilter($modelInstance->getIdFieldName(), array('in' => $ids));

        if ($collection->getSize() == 0) {
            $this->redirectToTablePage($table);
        }

        foreach ($collection as $item) {

            $item->delete();
            $this->isMergeModeEnabled($table) && $item->getChildObject()->delete();
        }

        $this->afterTableAction($table);
        $this->redirectToTablePage($table);
    }

    public function updateTableCellsAction()
    {
        $table = $this->getRequest()->getParam('table');
        $modelInstance = $this->getModel();

        $cellsValues = $this->prepareCellsValuesArray();
        $ids = explode(',', $this->getRequest()->getParam('ids'));

        if (!$modelInstance || empty($ids) || empty($cellsValues)) {
            return;
        }

        $collection = $modelInstance->getCollection();
        $collection->addFieldToFilter($modelInstance->getIdFieldName(), array('in' => $ids));

        if ($collection->getSize() == 0) {
            $this->redirectToTablePage($table);
        }

        $idFieldName = $modelInstance->getIdFieldName();
        $isAutoIncrement = Mage::helper('M2ePro/Module_Database_Structure')->isIdColumnAutoIncrement($table);

        foreach ($collection->getItems() as $item) {
            foreach ($cellsValues as $field => $value) {

                if ($field == $idFieldName && $isAutoIncrement) {
                    continue;
                }

                if ($field == $idFieldName && !$isAutoIncrement) {

                    Mage::getSingleton('core/resource')->getConnection('core_write')->update(
                        Mage::getSingleton('core/resource')->getTableName($table),
                        array($idFieldName => $value),
                        "`{$idFieldName}` = {$item->getId()}"
                    );
                }

                $item->setData($field, $value);
            }

            $item->save();
        }

        $this->afterTableAction($table);
    }

    private function afterTableAction($tableName)
    {
        if (strpos($tableName, 'config') !== false || strpos($tableName, 'wizard') !== false) {
            Mage::helper('M2ePro/Module')->clearCache();
        }
    }

    //#############################################

    private function getModel()
    {
        $table     = $this->getRequest()->getParam('table');
        $modelName = $this->getRequest()->getParam('model');
        $component = $this->getRequest()->getParam('component');

        if (!$this->isMergeModeEnabled($table)) {
            return Mage::getModel('M2ePro/' . $modelName);
        }

        return Mage::helper('M2ePro/Component')->getComponentModel($component, $modelName);
    }

    private function isMergeModeEnabled($table)
    {
        return (bool)$this->getRequest()->getParam('merge') &&
                Mage::helper('M2ePro/Module_Database_Structure')->isTableHorizontal($table);
    }

    private function prepareCellsValuesArray()
    {
        $cells = $this->getRequest()->getParam('cells', array());
        is_string($cells) && $cells = array($cells);

        $bindArray = array();
        foreach ($cells as $columnName) {

            if (is_null($columnValue = $this->getRequest()->getParam('value_'.$columnName))) {
                continue;
            }

            strtolower($columnValue) == 'null' && $columnValue = NULL;
            $bindArray[$columnName] = $columnValue;
        }

        return $bindArray;
    }

    //#############################################

    public function databaseGridAction()
    {
        $response = $this->loadLayout()
                         ->getLayout()
                         ->createBlock('M2ePro/adminhtml_development_tabs_database_grid')->toHtml();

        $this->getResponse()->setBody($response);
    }

    public function databaseTableGridAction()
    {
        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_database_table_grid')->toHtml();

        $this->getResponse()->setBody($response);
    }

    public function getTableCellsPopupHtmlAction()
    {
        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_database_table_tableCellsPopup')->toHtml();

        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function redirectToTablePage($tableName)
    {
        $this->_redirect('*/*/manageTable', array('table' => $tableName));
    }

    //#############################################
}