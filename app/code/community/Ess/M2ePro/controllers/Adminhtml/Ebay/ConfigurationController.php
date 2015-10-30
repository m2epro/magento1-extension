<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
            ->addJs('M2ePro/Ebay/ConfigurationHandler.js');

        $this->_initPopUp();

        $this->setComponentPageHelpLink('General');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration');
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_configuration', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_GENERAL)
                )
            )->renderLayout();
    }

    public function globalAction()
    {
        $this->_initAction();

        $this->setComponentPageHelpLink('Global+Settings');

        $this->_addContent($this->getLayout()->createBlock(
                    'M2ePro/adminhtml_ebay_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_GLOBAL)
                )
            )->renderLayout();
    }

    public function saveAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/ebay/', 'mode',
            $this->getRequest()->getParam('view_ebay_mode')
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/ebay/feedbacks/notification/', 'mode',
            (int)$this->getRequest()->getParam('view_ebay_feedbacks_notification_mode')
        );

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/ebay/template/category/', 'use_last_specifics',
            (int)$this->getRequest()->getParam('use_last_specifics_mode')
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/ebay/connector/listing/', 'check_the_same_product_already_listed',
            (int)$this->getRequest()->getParam('check_the_same_product_already_listed_mode')
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/ebay/description/', 'upload_images_mode',
            (int)$this->getRequest()->getParam('upload_images_mode')
        );

        $sellingCurrency = $this->getRequest()->getParam('selling_currency');
        if (!empty($sellingCurrency)) {
            foreach ($sellingCurrency as $code => $value) {
                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                    '/ebay/selling/currency/', $code, (string)$value
                );
            }
        }

        $motorsEpidsAttribute = $this->getRequest()->getParam('motors_epids_attribute');
        $motorsKtypesAttribute = $this->getRequest()->getParam('motors_ktypes_attribute');

        if (!empty($motorsKtypesAttribute) && !empty($motorsEpidsAttribute) &&
            $motorsEpidsAttribute == $motorsKtypesAttribute
        ) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('ePIDs and kTypes Attributes can not be the same.')
            );
            $this->_redirectUrl($this->_getRefererUrl());
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/ebay/motors/', 'epids_attribute',
            $motorsEpidsAttribute
        );

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/ebay/motors/', 'ktypes_attribute',
            $motorsKtypesAttribute
        );

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Settings was successfully saved.'));
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

        $this->_getSession() ->addSuccess("Successfully added '{$added}' compatibility records.");
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

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core/write');
        $connWrite->delete($helper->getDictionaryTable($motorsType), '`is_custom` = 1');

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Added compatibility data has been cleared.'));
        return $this->_redirect('*/*/index');
    }

    //########################################

    private function getExistedMotorsItems()
    {
        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
        $motorsType = $this->getRequest()->getParam('motors_type');

        $queryStmt = Mage::getSingleton('core/resource')->getConnection('core/read')
            ->select()
            ->from($helper->getDictionaryTable($motorsType),
                   array($helper->getIdentifierKey($motorsType)))
            ->query();

        $result = array();

        while ($id = $queryStmt->fetchColumn()) {
            $result[] = $id;
        }

        return $result;
    }

    private function getPreparedInsertData($csvRow, $existedItems)
    {
        $helper = Mage::helper('M2ePro/Component_Ebay_Motors');
        $motorsType = $this->getRequest()->getParam('motors_type');

        $idCol = $helper->getIdentifierKey($motorsType);

        if (!isset($csvRow[$idCol]) || in_array($csvRow[$idCol], $existedItems)) {
            return false;
        }

        if ($motorsType == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE) {

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

            if (empty($csvRow[$columnName])) {
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
            'is_custom'    => 1
        );
    }

    //########################################
}