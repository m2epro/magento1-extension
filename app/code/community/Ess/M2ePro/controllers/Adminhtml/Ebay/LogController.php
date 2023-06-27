<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_LogController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Logs & Events'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Log.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, 'pages/viewpage.action?pageId=17367088');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/logs'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_redirect('*/*/listing');
    }

    // ---------------------------------------

    public function listingAction()
    {
        $id = $this->getRequest()->getParam('listing_id', false);
        if ($id) {
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);

            if (!$listing->getId()) {
                $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
                return $this->_redirect('*/*/index');
            }
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "logs-and-events");

        if (!empty($id)) {
            $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_log_listing_view');
        } else {
            $logBlock = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_log', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_LISTING)
            );
        }

        $this->_addContent($logBlock)->renderLayout();
    }

    public function listingGridAction()
    {
        $id = $this->getRequest()->getParam('listing_id', false);
        if ($id) {
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);

            if (!$listing->getId()) {
                return;
            }
        }

        if ($viewMode = $this->getRequest()->getParam('view_mode', false)) {
            Mage::helper('M2ePro/Module_Log')->setViewMode(
                Ess_M2ePro_Helper_View_Ebay::NICK . '_log_listing_view_mode',
                $viewMode
            );
        } else {
            $viewMode = Mage::helper('M2ePro/Module_Log')->getViewMode(
                Ess_M2ePro_Helper_View_Ebay::NICK . '_log_listing_view_mode'
            );
        }

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_log_listing_view_' . $viewMode . '_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function listingProductAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id', false);
        if ($listingProductId) {
            $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);

            if (!$listingProduct->getId()) {
                $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing Product does not exist.'));
                return $this->_redirect('*/*/index');
            }
        }

        $this->_initAction();

        $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_log_listing_view');

        $this->_addContent($logBlock)->renderLayout();
    }

    public function listingProductGridAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id', false);
        if ($listingProductId) {
            $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);

            if (!$listingProduct->getId()) {
                return;
            }
        }

        if ($viewMode = $this->getRequest()->getParam('view_mode', false)) {
            Mage::helper('M2ePro/Module_Log')->setViewMode(
                Ess_M2ePro_Helper_View_Ebay::NICK . '_log_listing_view_mode',
                $viewMode
            );
        } else {
            $viewMode = Mage::helper('M2ePro/Module_Log')->getViewMode(
                Ess_M2ePro_Helper_View_Ebay::NICK . '_log_listing_view_mode'
            );
        }

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_log_listing_view_' . $viewMode . '_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function synchronizationAction()
    {
        $this->_initAction();

        $this->setPageHelpLink(null, null, "logs-and-events");

        $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_log', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_SYNCHRONIZATION)
            )
        )->renderLayout();
    }

    public function synchronizationGridAction()
    {
        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_log_synchronization_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function orderAction()
    {
        $this->_initAction();

        $this->setPageHelpLink(null, 'pages/viewpage.action?pageId=17367088#Logs.-OrdersLog');

        $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_log', '', array(
                    'active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_ORDER
                )
            )
        )->renderLayout();
    }

    public function orderGridAction()
    {
        $grid = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_log_order_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################
}
