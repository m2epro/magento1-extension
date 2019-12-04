<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_ControlPanel_MainController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function indexAction()
    {
        $this->_redirect(Mage::helper('M2ePro/View_ControlPanel')->getPageRoute());
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
        }

        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_title(Mage::helper('M2ePro/View_ControlPanel')->getTitle());
        $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_ControlPanel::MENU_ROOT_NODE_NICK);
        return $tempResult;
    }

    //########################################

    protected function addDevelopmentNotification()
    {
        if (!Mage::helper('M2ePro/Magento')->isDeveloper()) {
            return false;
        }

        $enabledMods = array();
        Mage::helper('M2ePro/Magento')->isDeveloper() && $enabledMods[] = 'Magento';

        $this->_getSession()->addWarning(implode(', ', $enabledMods).' Development Mode is Enabled.');

        return true;
    }

    //########################################
}
