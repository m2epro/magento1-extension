<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_ConfigurationController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Ebay/Configuration.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "configuration");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_ebay_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_GENERAL)
                )
            )->renderLayout();
    }

    public function globalAction()
    {
        $this->_initAction();

        $this->setPageHelpLink(null, null, "configuration");

        $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_configuration', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_GLOBAL)
            )
        )->renderLayout();
    }

    public function saveAction()
    {
        try {

            Mage::helper('M2ePro/Component_Ebay_Configuration')->setConfigValues($this->getRequest()->getPost());

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Settings was saved.'));

        } catch (Ess_M2ePro_Model_Exception_Logic $e) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__($e->getMessage()));
        }

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################

    public function importMotorsDataAction()
    {
        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
        $motorsType = $this->getRequest()->getPost('motors_type');

        if (!$motorsType || empty($_FILES['source']['tmp_name'])) {
            $this->getSession()->addError(Mage::helper('M2ePro')->__('Some of required fields are not filled up.'));
            return $this->_redirect('*/*/index');
        }

        $csvParser = new Varien_File_Csv();
        $tempCsvData = $csvParser->getData($_FILES['source']['tmp_name']);

        $csvData = array();
        $headers = array_shift($tempCsvData);
        foreach ($tempCsvData as $csvRow) {
            if (!is_array($csvRow) || count($csvRow) != count($headers)) {
                continue;
            }

            $csvData[] = array_combine($headers, $csvRow);
        }

        $added = 0;
        $existedItems = $this->getExistedMotorsItems();

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core/write');
        $tableName = $helper->getDictionaryTable($motorsType);

        foreach ($csvData as $csvRow) {
            if (!$insertsData = $this->getPreparedInsertData($csvRow, $existedItems)) {
                continue;
            }

            $added++;
            $connWrite->insert($tableName, $insertsData);
        }

        $this->_getSession() ->addSuccess("Added '{$added}' compatibility records.");
        return $this->_redirect('*/*/index');
    }

    public function clearAddedMotorsDataAction()
    {
        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
        $motorsType = $this->getRequest()->getPost('motors_type');

        if (!$motorsType) {
            $this->getSession()->addError(Mage::helper('M2ePro')->__('Some of required fields are not filled up.'));
            return $this->_redirect('*/*/index');
        }

        $conditions = array('is_custom = ?' => 1);
        if ($helper->isTypeBasedOnEpids($motorsType)) {
            $conditions['scope = ?'] = $helper->getEpidsScopeByType($motorsType);
        }

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core/write');
        $connWrite->delete(
            $helper->getDictionaryTable($motorsType), $conditions
        );

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Added compatibility data has been cleared.'));
        return $this->_redirect('*/*/index');
    }

    //########################################

    protected function getExistedMotorsItems()
    {
        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
        $motorsType = $this->getRequest()->getParam('motors_type');

        $selectStmt = Mage::getSingleton('core/resource')->getConnection('core/read')
            ->select()
            ->from(
                $helper->getDictionaryTable($motorsType), array($helper->getIdentifierKey($motorsType))
            );

        if ($helper->isTypeBasedOnEpids($motorsType)) {
            $selectStmt->where('scope = ?', $helper->getEpidsScopeByType($motorsType));
        }

        $result = array();
        $queryStmt = $selectStmt->query();

        while ($id = $queryStmt->fetchColumn()) {
            $result[] = $id;
        }

        return $result;
    }

    protected function getPreparedInsertData($csvRow, $existedItems)
    {
        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');

        $motorsType = $this->getRequest()->getParam('motors_type');
        $idCol      = $helper->getIdentifierKey($motorsType);

        if (!isset($csvRow[$idCol]) || in_array($csvRow[$idCol], $existedItems)) {
            return false;
        }

        if ($motorsType == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE) {
            if (strlen($csvRow['ktype']) > 10) {
                return false;
            }

            if (!is_numeric($csvRow['ktype'])) {
                return false;
            }

            return array(
                'ktype'      => (int)$csvRow['ktype'],
                'make'       => (isset($csvRow['make']) ? $csvRow['make'] : null),
                'model'      => (isset($csvRow['model']) ? $csvRow['model'] : null),
                'variant'    => (isset($csvRow['variant']) ? $csvRow['variant'] : null),
                'body_style' => (isset($csvRow['body_style']) ? $csvRow['body_style'] : null),
                'type'       => (isset($csvRow['type']) ? $csvRow['type'] : null),
                'from_year'  => (isset($csvRow['from_year']) ? (int)$csvRow['from_year'] : null),
                'to_year'    => (isset($csvRow['to_year']) ? (int)$csvRow['to_year'] : null),
                'engine'     => (isset($csvRow['engine']) ? $csvRow['engine'] : null),
                'is_custom'  => 1
            );
        }

        $requiredColumns = array('epid','product_type','make','model','year');
        foreach ($requiredColumns as $columnName) {
            if (!isset($csvRow[$columnName])) {
                return false;
            }
        }

        return array(
            'epid'         => $csvRow['epid'],
            'product_type' => (int)$csvRow['product_type'],
            'make'         => $csvRow['make'],
            'model'        => $csvRow['model'],
            'year'         => (int)$csvRow['year'],
            'trim'         => (isset($csvRow['trim']) ? $csvRow['trim'] : null),
            'engine'       => (isset($csvRow['engine']) ? $csvRow['engine'] : null),
            'submodel'     => (isset($csvRow['submodel']) ? $csvRow['submodel'] : null),
            'street_name'  => (isset($csvRow['street_name']) ? $csvRow['street_name'] : null),
            'is_custom'    => 1,
            'scope'        => $helper->getEpidsScopeByType($motorsType)
        );
    }

    //########################################
}
