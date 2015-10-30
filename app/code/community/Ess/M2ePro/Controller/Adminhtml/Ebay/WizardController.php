<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Ebay_WizardController
    extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //########################################

    protected function getCustomViewNick()
    {
        return Ess_M2ePro_Helper_View_Ebay::NICK;
    }

    protected function getMenuRootNodeNick()
    {
        return Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK;
    }

    protected function getMenuRootNodeLabel()
    {
        return Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel();
    }

    //########################################
}