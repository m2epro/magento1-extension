<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_LogController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Logs'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/LogHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=17367088');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/logs');
    }

    //########################################

    public function indexAction()
    {
        $this->_redirect('*/*/listing');
    }

    // ---------------------------------------

    public function listingAction()
    {
        $id = $this->getRequest()->getParam('id', false);
        if ($id) {
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);

            if (!$listing->getId()) {
                $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
                return $this->_redirect('*/*/index');
            }
        }

        $this->_initAction();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=17367088#Logs.-ListingsLog');

        if (!empty($id)) {
            $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_log');
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
        $id = $this->getRequest()->getParam('id', false);
        if ($id) {
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);

            if (!$listing->getId()) {
                return;
            }
        }

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_log_grid')->toHtml();
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

        $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_log');

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

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_log_grid')->toHtml();
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

        $this->_initAction();

        $this->setPageHelpLink(NULL,
            'pages/viewpage.action?pageId=17367088#Logs.-3rdPartyListingsLog(advancedmodeonly)');

        if (!empty($id)) {
            $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_log');
        } else {
            $logBlock = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_log', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_LISTING_OTHER)
            );
        }

        $this->_addContent($logBlock)->renderLayout();
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
            ->createBlock('M2ePro/adminhtml_ebay_listing_other_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function synchronizationAction()
    {
        $this->_initAction();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=17367088#Logs.-SynchronizationLog');

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
            ->createBlock('M2ePro/adminhtml_ebay_synchronization_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // ---------------------------------------

    public function orderAction()
    {
        $this->_initAction();

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=17367088#Logs.-OrdersLog');

        $this->_addContent(
                 $this->getLayout()->createBlock(
                     'M2ePro/adminhtml_ebay_log', '',
                     array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_ORDER)
                 )
             )->renderLayout();
    }

    public function orderGridAction()
    {
        $grid = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order_log_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################
}