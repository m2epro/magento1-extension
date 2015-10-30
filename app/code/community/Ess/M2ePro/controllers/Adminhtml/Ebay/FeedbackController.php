<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_FeedbackController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Feedback'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Ebay/FeedbackHandler.js');

        $this->setPageHelpLink(NULL, 'pages/viewpage.action?pageId=17367096');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration');
    }

    //########################################

    public function indexAction()
    {
        if (is_null($this->getRequest()->getParam('account'))) {
            $this->_redirect('*/adminhtml_ebay_account/index');
        }

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_feedback'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_feedback_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function saveAction()
    {
        $feedbackId = $this->getRequest()->getParam('feedback_id');
        $feedbackText = $this->getRequest()->getParam('feedback_text');

        $feedbackText = strip_tags($feedbackText);

        /** @var Ess_M2ePro_Model_Ebay_Feedback $feedback */
        $feedback = Mage::getModel('M2ePro/Ebay_Feedback')->loadInstance($feedbackId);
        $result = $feedback->sendResponse($feedbackText, Ess_M2ePro_Model_Ebay_Feedback::TYPE_POSITIVE);

        $this->getResponse()->setBody(json_encode(array('result' => ($result ? 'success' : 'failure'))));
    }

    //########################################

    public function getFeedbackTemplatesAction()
    {
        $feedbackId = $this->getRequest()->getParam('feedback_id');

        $account = Mage::getModel('M2ePro/Ebay_Feedback')->loadInstance($feedbackId)->getAccount();
        $feedbacksTemplates = $account->getChildObject()->getFeedbackTemplates(false);

        return $this->getResponse()->setBody(json_encode(array(
            'feedbacks_templates' => $feedbacksTemplates
        )));
    }

    //########################################

    public function goToOrderAction()
    {
        $feedbackId = $this->getRequest()->getParam('feedback_id');

        if (is_null($feedbackId)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Feedback is not defined.'));
            return $this->_redirect('*/adminhtml_ebay_order/index');
        }

        /** @var $feedback Ess_M2ePro_Model_Ebay_Feedback */
        $feedback = Mage::getModel('M2ePro/Ebay_Feedback')->loadInstance((int)$feedbackId);
        $order = $feedback->getOrder();

        if (is_null($order)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Requested Order was not found.'));
            return $this->_redirect('*/adminhtml_ebay_order/index');
        }

        $this->_redirect('*/adminhtml_ebay_order/view', array('id' => $order->getId()));
    }

    //########################################

    public function goToItemAction()
    {
        $feedbackId = $this->getRequest()->getParam('feedback_id');

        if (is_null($feedbackId)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Feedback is not defined.'));
            return $this->_redirect('*/*/index');
        }

        /** @var $feedback Ess_M2ePro_Model_Ebay_Feedback */
        $feedback = Mage::getModel('M2ePro/Ebay_Feedback')->loadInstance((int)$feedbackId);
        $itemId = $feedback->getData('ebay_item_id');

        $listingProduct = Mage::helper('M2ePro/Component_Ebay')->getListingProductByEbayItem(
            $feedback->getData('ebay_item_id'), $feedback->getData('account_id')
        );

        if (!is_null($listingProduct)) {
            $itemUrl = Mage::helper('M2ePro/Component_Ebay')->getItemUrl(
                $itemId,
                $listingProduct->getListing()->getAccount()->getChildObject()->getMode(),
                $listingProduct->getListing()->getMarketplaceId()
            );

            return $this->_redirectUrl($itemUrl);
        }

        $order = $feedback->getOrder();

        if (!is_null($order) && !is_null($order->getMarketplaceId())) {
            $itemUrl = Mage::helper('M2ePro/Component_Ebay')->getItemUrl(
                $itemId,
                $order->getAccount()->getChildObject()->getMode(),
                $order->getMarketplaceId()
            );

            return $this->_redirectUrl($itemUrl);
        }

        $this->_getSession()->addError(Mage::helper('M2ePro')->__('Item\'s Site is Unknown.'));

        return $this->_redirect('*/*/index');
    }

    //########################################
}