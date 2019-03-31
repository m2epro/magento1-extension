<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_LogController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Activity Logs'));

        $this->getLayout()->getBlock('head')
            ->addCss('M2ePro/css/Plugin/DropDown.css')

            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/LogHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK . '/logs'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_redirect('*/*/listing');
    }

    //########################################

    public function listingAction()
    {
        $id = $this->getRequest()->getParam('id', false);
        if ($id) {
            $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $id);

            if (!$listing->getId()) {
                $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
                return $this->_redirect('*/*/index');
            }

            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_log');
        }

        if (empty($block)) {
            $block = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_walmart_log', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Log_Tabs::TAB_ID_LISTING)
            );
        }

        $this->_initAction();

        $channel = $this->getRequest()->getParam('channel');

        if (!is_null($channel) && $channel !== 'all') {

            if ($channel == Ess_M2ePro_Helper_Component_Walmart::NICK) {
                $this->setPageHelpLink(NULL, NULL, "x/L4taAQ");
            } else {
                $this->setComponentPageHelpLink('Logs#Logs-ListingsLog', $channel);
            }

        } else {
            $this->setComponentPageHelpLink('Logs#Logs-ListingsLog');
        }

        $this->_title(Mage::helper('M2ePro')->__('Listings Log'))
             ->_addContent($block)
             ->renderLayout();
    }

    public function listingGridAction()
    {
        $id = $this->getRequest()->getParam('id', false);
        if ($id) {
            $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $id);

            if (!$listing->getId()) {
                return;
            }
        }

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function listingProductAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id', false);
        if ($listingProductId) {
            $listingProduct = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Listing_Product', $listingProductId);

            if (!$listingProduct->getId()) {
                $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing Product does not exist.'));
                return $this->_redirect('*/*/index');
            }
        }

        $this->_initAction();

        $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_log');

        $this->_addContent($logBlock)->renderLayout();
    }

    public function listingProductGridAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id', false);
        if ($listingProductId) {
            $listingProduct = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Listing_Product', $listingProductId);

            if (!$listingProduct->getId()) {
                return;
            }
        }

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_log_grid')->toHtml();

        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function listingOtherAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing_Other')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('3rd Party Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());

        if ($model->getId()) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_other_log');
        } else {
            $block = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_walmart_log', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Log_Tabs::TAB_ID_LISTING_OTHER)
            );
        }

        $this->_initAction();

        $channel = $this->getRequest()->getParam('channel');

        if (!is_null($channel) && $channel !== 'all') {

            if ($channel == Ess_M2ePro_Helper_Component_Walmart::NICK) {
                $this->setPageHelpLink(NULL, NULL, "x/L4taAQ");
            } else {
                $this->setComponentPageHelpLink('Logs#Logs-3rdPartyListingsLog', $channel);
            }

        } else {
            $this->setComponentPageHelpLink('Logs#Logs-3rdPartyListingsLog');
        }

        $this->_title(Mage::helper('M2ePro')->__('3rd Party Listings Log'))
             ->_addContent($block)
             ->renderLayout();
    }

    public function listingOtherGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing_Other')->load($id);

        if (!$model->getId() && $id) {
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_listing_other_log_grid', '', array(
                'channel' => $this->getRequest()->getParam('channel')
            ))->toHtml();
        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function synchronizationAction()
    {
        $this->_initAction();

        $channel = $this->getRequest()->getParam('channel');

        if (!is_null($channel) && $channel !== 'all') {

            if ($channel == Ess_M2ePro_Helper_Component_Walmart::NICK) {
                $this->setPageHelpLink(NULL, NULL, "x/L4taAQ");
            } else {
                $this->setComponentPageHelpLink('Logs#Logs-SynchronizationLog', $channel);
            }

        } else {
            $this->setComponentPageHelpLink('Logs#Logs-SynchronizationLog');
        }

        $this->_title(Mage::helper('M2ePro')->__('Synchronization Log'))
             ->_addContent($this->getLayout()->createBlock(
                 'M2ePro/adminhtml_walmart_log', '',
                 array('active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Log_Tabs::TAB_ID_SYNCHRONIZATION)
             ))
             ->renderLayout();
    }

    public function synchronizationGridAction()
    {
        $response = $this->loadLayout()->getLayout()
             ->createBlock('M2ePro/adminhtml_walmart_synchronization_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function orderAction()
    {
        $this->_initAction();

        $channel = $this->getRequest()->getParam('channel');

        if (!is_null($channel) && $channel !== 'all') {

            if ($channel == Ess_M2ePro_Helper_Component_Walmart::NICK) {
                $this->setPageHelpLink(NULL, NULL, "x/L4taAQ");
            } else {
                $this->setComponentPageHelpLink('Logs#Logs-OrdersLog', $channel);
            }

        } else {
            $this->setComponentPageHelpLink('Logs#Logs-OrdersLog');
        }

        $this->_title(Mage::helper('M2ePro')->__('Orders Log'))
             ->_addContent($this->getLayout()->createBlock(
                 'M2ePro/adminhtml_walmart_log', '',
                 array('active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Log_Tabs::TAB_ID_ORDER)
             ))
             ->renderLayout();
    }

    public function orderGridAction()
    {
        $grid = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order_log_grid', '', array(
            'channel' => $this->getRequest()->getParam('channel')
        ));
        $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################
}