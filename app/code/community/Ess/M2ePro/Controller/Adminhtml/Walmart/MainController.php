<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
    extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //########################################

    protected function getCustomViewNick()
    {
        return Ess_M2ePro_Helper_View_Walmart::NICK;
    }

    //########################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK);
    }

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $this->setComponentPageHelpLink();

        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK);
        $tempResult->_title(Mage::helper('M2ePro/View_Walmart')->getMenuRootNodeLabel());
        return $tempResult;
    }

    //########################################

    protected function setComponentPageHelpLink($view = NULL)
    {
        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Walmart::NICK, $view);
    }

    //########################################
}
