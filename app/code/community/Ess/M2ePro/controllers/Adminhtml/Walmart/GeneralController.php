<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'))
            ->_title(Mage::helper('M2ePro')->__('General'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Walmart/Configuration/General.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "walmart-integration");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isLoggedIn();
    }

    //########################################

    public function indexAction()
    {
        if (!Mage::helper('M2ePro/Component_Walmart')->isEnabled()) {
            $this->_redirect('*/adminhtml_walmart_synchronization/index');
        }

        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_walmart_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_Tabs::TAB_ID_GENERAL)
                )
            )->renderLayout();
    }

    //########################################
}
