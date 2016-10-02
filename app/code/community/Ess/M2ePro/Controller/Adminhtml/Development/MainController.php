<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Development_MainController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function indexAction()
    {
        $this->_redirect(Mage::helper('M2ePro/View_Development')->getPageRoute());
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isLoggedIn();
    }

    protected function _validateSecretKey()
    {
        return true;
    }

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            $this->addDevelopmentNotification();
            $this->addMaintenanceNotification();
        }

        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_title(Mage::helper('M2ePro/View_Development')->getTitle());
        return $tempResult;
    }

    //########################################

    private function addDevelopmentNotification()
    {
        if (!Mage::helper('M2ePro/Magento')->isDeveloper() &&
            !Mage::helper('M2ePro/Module')->isDevelopmentMode()) {
            return false;
        }

        $enabledMods = array();
        Mage::helper('M2ePro/Magento')->isDeveloper() && $enabledMods[] = 'Magento';
        Mage::helper('M2ePro/Module')->isDevelopmentMode() && $enabledMods[] = 'M2ePro';

        $this->_getSession()->addWarning(implode(', ', $enabledMods).' Development Mode is Enabled.');

        return true;
    }

    private function addMaintenanceNotification()
    {
        if (!Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            return false;
        }

        $this->_getSession()->addWarning('Maintenance is Active now.');

        return true;
    }

    //########################################
}