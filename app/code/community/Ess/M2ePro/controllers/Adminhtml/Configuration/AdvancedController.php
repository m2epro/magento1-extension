<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Configuration_AdvancedController
    extends Ess_M2ePro_Controller_Adminhtml_Configuration_MainController
{
    //########################################

    public function saveAction()
    {
        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################

    public function changeModuleModeAction()
    {
        $moduleMode = (int)$this->getRequest()->getParam('module_mode');
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/', 'is_disabled', $moduleMode);

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->_redirectUrl($this->_getRefererUrl());
    }

    public function changeCronModeAction()
    {
        $cronMode = (int)$this->getRequest()->getParam('cron_mode');
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cron/', 'mode', $cronMode);

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################
}