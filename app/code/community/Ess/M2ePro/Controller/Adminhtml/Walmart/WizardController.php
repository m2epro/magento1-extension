<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Walmart_WizardController
    extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //########################################

    protected function getCustomViewNick()
    {
        return Ess_M2ePro_Helper_View_Walmart::NICK;
    }

    protected function getMenuRootNodeNick()
    {
        return Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK;
    }

    protected function getMenuRootNodeLabel()
    {
        return Mage::helper('M2ePro/View_Walmart')->getMenuRootNodeLabel();
    }

    //########################################
}
