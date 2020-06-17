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

    protected function _preDispatch()
    {
        return null;
    }

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
        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_title(Mage::helper('M2ePro/View_ControlPanel')->getTitle());
        $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_ControlPanel::MENU_ROOT_NODE_NICK);
        return $tempResult;
    }

    //########################################
}
