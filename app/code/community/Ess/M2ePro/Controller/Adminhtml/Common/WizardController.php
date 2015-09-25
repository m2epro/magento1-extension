<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Common_WizardController
    extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //#############################################

    protected function getCustomViewNick()
    {
        return Ess_M2ePro_Helper_View_Common::NICK;
    }

    protected function getMenuRootNodeNick()
    {
        return Ess_M2ePro_Helper_View_Common::MENU_ROOT_NODE_NICK;
    }

    protected function getMenuRootNodeLabel()
    {
        return Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel();
    }

    //#############################################

    public function indexAction()
    {
        if ($this->isSkipped()) {
            return $this->_redirect('*/adminhtml_common_listing/index/');
        }

        parent::indexAction();
    }

    //#############################################
}