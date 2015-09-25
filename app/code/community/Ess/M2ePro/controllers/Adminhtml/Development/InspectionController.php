<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_InspectionController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    public function phpInfoAction()
    {
        phpinfo();
    }

    public function cacheSettingsAction()
    {
        echo '<pre>'.print_r(Mage::app()->getCache(), true);
    }

    public function resourcesSettingsAction()
    {
        $resourcesConfig = Mage::getConfig()->getNode('global/resources');
        $resourcesConfig = json_decode(json_encode((array)$resourcesConfig), true);
        echo '<pre>'.print_r($resourcesConfig, true).'</pre>';
    }

    //#############################################

    public function cronScheduleTableAction()
    {
        $this->loadLayout();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_development_inspection_cronScheduleTable_grid');
            return $this->getResponse()->setBody($block->toHtml());
        }

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_development_inspection_cronScheduleTable');

        $this->_addContent($block);
        return $this->renderLayout();
    }

    public function cronScheduleTableShowMessagesAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            return $this->_redirect('*/*/cronScheduleTable');
        }

        return $this->getResponse()->setBody(Mage::getModel('cron/schedule')->load($id)->getMessages());
    }

    //---------------------------------------------

    public function repairCrashedTableAction()
    {
        if (!$tableName = $this->getRequest()->getParam('table_name')) {
            $this->_getSession()->addError('Table Name is not presented.');
            return $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageInspectionTabUrl());
        }

        $resultMessage = Mage::helper('M2ePro/Module_Database_Repair')->repairCrashedTable($tableName);
        $resultMessage == 'OK' ? $this->_getSession()->addSuccess('Successfully repaired.')
                               : $this->_getSession()->addError($resultMessage);

        return $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageInspectionTabUrl());
    }

    //#############################################
}