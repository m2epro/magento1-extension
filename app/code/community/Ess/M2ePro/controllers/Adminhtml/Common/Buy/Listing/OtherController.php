<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Buy_Listing_OtherController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('3rd Party Listings'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')

            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/Other/GridHandler.js')
            ->addJs('M2ePro/Common/Listing/Other/GridHandler.js')
            ->addJs('M2ePro/Common/Buy/Listing/Other/GridHandler.js')

            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/Listing/MovingHandler.js')
            ->addJs('M2ePro/Listing/Other/AutoMappingHandler.js')

            ->addJs('M2ePro/Listing/Other/MappingHandler.js')

            ->addJs('M2ePro/Listing/Other/RemovingHandler.js')
            ->addJs('M2ePro/Listing/Other/UnmappingHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Buy::NICK, '3rd+Party+Listings');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/listings');
    }

    //########################################

    public function indexAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_common_listing_other/index');
        }

        /** @var $block Ess_M2ePro_Block_Adminhtml_Common_Listing_Other */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing_other');
        $block->enableBuyTab();

        $this->getResponse()->setBody($block->getBuyTabHtml());
    }

    public function gridAction()
    {
        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_buy_listing_other_view_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function viewAction()
    {
        /** @var $block Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Other_View */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_listing_other_view');
        $this->_initAction()->_addContent($block)->renderLayout();
    }

    //########################################
}
