<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Common_MainController
    extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //########################################

    protected function getCustomViewNick()
    {
        return Ess_M2ePro_Helper_View_Common::NICK;
    }

    //########################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(Ess_M2ePro_Helper_View_Common::MENU_ROOT_NODE_NICK);
    }

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $this->setComponentPageHelpLink();

        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_Common::MENU_ROOT_NODE_NICK);
        $tempResult->_title(Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel());
        return $tempResult;
    }

    //########################################

    protected function setComponentPageHelpLink($view = NULL, $component = NULL)
    {
        if (!is_null($component)) {
            $this->setPageHelpLink($component, $view);
            return;
        }

        $components = Mage::helper('M2ePro/View_Common_Component')->getActiveComponents();

        if (count($components) == 1) {
            $this->setPageHelpLink(array_shift($components), $view);
        }
    }

    //########################################
}