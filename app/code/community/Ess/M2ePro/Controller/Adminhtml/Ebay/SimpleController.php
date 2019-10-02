<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Ebay_SimpleController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK);
    }

    //########################################
}
