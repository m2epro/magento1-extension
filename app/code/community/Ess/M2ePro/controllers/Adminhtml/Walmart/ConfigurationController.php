<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_ConfigurationController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'))
            ->_title(Mage::helper('M2ePro')->__('Global'));

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "walmart-integration");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_walmart_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_Tabs::TAB_ID_GLOBAL)
                )
            )->renderLayout();
    }

    //########################################

    public function saveAction()
    {
        Mage::helper('M2ePro/Component_Walmart_Configuration')->setConfigValues($this->getRequest()->getPost());

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Settings was saved.'));
        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################
}
