<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_DatabaseController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()->getLayout()->getBlock('head')
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/ControlPanel/DatabaseGrid.js');

        $this->_initPopUp();

        return $this;
    }

    //########################################

    public function manageTableAction()
    {
        $this->_initAction();

        $table = $this->getRequest()->getParam('table');

        if ($table === null) {
            $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageDatabaseTabUrl());
            return;
        }

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ControlPanel_tabs_database_table'))
             ->renderLayout();
    }

    // ---------------------------------------

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
            return;
        }

        $collection = $modelInstance->getCollection();
        $collection->addFieldToFilter($modelInstance->getIdFieldName(), array('in' => $ids));

        if ($collection->getSize() == 0) {
            $this->redirectToTablePage($table);
            return;
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
            return;
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
                        Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($table),
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

    protected function afterTableAction($tableName)
    {
        if (strpos($tableName, 'config') !== false || strpos($tableName, 'wizard') !== false) {
            Mage::helper('M2ePro/Module')->clearCache();
        }
    }

    //########################################

    protected function getModel()
    {
        $table     = $this->getRequest()->getParam('table');
        $modelName = $this->getRequest()->getParam('model');
        $component = $this->getRequest()->getParam('component');

        if (!$this->isMergeModeEnabled($table)) {
            return Mage::getModel('M2ePro/' . $modelName);
        }

        return Mage::helper('M2ePro/Component')->getComponentModel($component, $modelName);
    }

    protected function isMergeModeEnabled($table)
    {
        return (bool)$this->getRequest()->getParam('merge') &&
                Mage::helper('M2ePro/Module_Database_Structure')->isTableHorizontal($table);
    }

    protected function prepareCellsValuesArray()
    {
        $cells = $this->getRequest()->getParam('cells', array());
        is_string($cells) && $cells = array($cells);

        $bindArray = array();
        foreach ($cells as $columnName) {
            if (($columnValue = $this->getRequest()->getParam('value_' . $columnName)) === null) {
                continue;
            }

            strtolower($columnValue) == 'null' && $columnValue = null;
            $bindArray[$columnName] = $columnValue;
        }

        return $bindArray;
    }

    //########################################

    public function databaseGridAction()
    {
        $response = $this->loadLayout()
                         ->getLayout()
                         ->createBlock('M2ePro/adminhtml_ControlPanel_tabs_database_grid')->toHtml();

        $this->getResponse()->setBody($response);
    }

    public function databaseTableGridAction()
    {
        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_ControlPanel_tabs_database_table_grid')->toHtml();

        $this->getResponse()->setBody($response);
    }

    public function getTableCellsPopupHtmlAction()
    {
        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_ControlPanel_tabs_database_table_tableCellsPopup')->toHtml();

        $this->getResponse()->setBody($response);
    }

    public function showOperationHistoryExecutionTreeUpAction()
    {
        $operationHistoryId = $this->getRequest()->getParam('operation_history_id');
        if (empty($operationHistoryId)) {
            $this->_getSession()->addError("Operation history ID is not presented.");
            return $this->redirectToTablePage('m2epro_operation_history');
        }

        $operationHistory = Mage::getModel('M2ePro/OperationHistory');
        $operationHistory->setObject($operationHistoryId);

        $this->getResponse()->setBody(
            '<pre>'.$operationHistory->getExecutionTreeUpInfo().'</pre>'
        );
    }

    public function showOperationHistoryExecutionTreeDownAction()
    {
        $operationHistoryId = $this->getRequest()->getParam('operation_history_id');
        if (empty($operationHistoryId)) {
            $this->_getSession()->addError("Operation history ID is not presented.");
            return $this->redirectToTablePage('m2epro_operation_history');
        }

        $operationHistory = Mage::getModel('M2ePro/OperationHistory');
        $operationHistory->setObject($operationHistoryId);

        while ($parentId = $operationHistory->getObject()->getData('parent_id')) {
            $object = $operationHistory->load($parentId);
            $operationHistory->setObject($object);
        }

        $this->getResponse()->setBody(
            '<pre>'.$operationHistory->getExecutionTreeDownInfo().'</pre>'
        );
    }

    //########################################

    public function redirectToTablePage($tableName)
    {
        $this->_redirect('*/*/manageTable', array('table' => $tableName));
    }

    //########################################
}
