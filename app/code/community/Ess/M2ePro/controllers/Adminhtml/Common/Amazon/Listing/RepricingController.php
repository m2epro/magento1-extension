<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_Listing_RepricingController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    public function indexAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        return $this->_redirect('*/adminhtml_common_amazon_listing/view', array(
            'id' => $listingId
        ));
    }

    //########################################

    public function openAddProductsAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $accountId = $this->getRequest()->getParam('account_id');
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        if (empty($productsIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Products not selected.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $url = $repricing->getAddProductsUrl($listingId, $productsIds);

        if ($url === false) {
            $this->_getSession()->addWarning(Mage::helper('M2ePro')->__(
                'The selected Amazon Products cannot be Managed by Amazon Repricing Tool.')
            );
            return $this->indexAction();
        }

        $this->_redirectUrl($url);
    }

    public function addProductsAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $responseToken = $this->getRequest()->getParam('response_token');

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $response = $repricing->getResponseData($responseToken);

        if (!empty($response['messages'])) {
            $this->parseRepricingMessages($response['messages']);
        }

        if ($response['status'] == '0') {
            return $this->indexAction();
        }

        if (empty($response['offers'])) {
            return $this->indexAction();
        }

        $skus = array();
        foreach ($response['offers'] as $offer) {
            $skus[] = $offer['sku'];
        }

        $repricing->setProductRepricingStatusBySku(
            $skus,
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_YES
        );

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('Amazon Products have been successfully added to the Amazon Repricing Tool.')
        );
        return $this->indexAction();
    }

    //########################################

    public function openShowDetailsAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $accountId = $this->getRequest()->getParam('account_id');
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        if (empty($productsIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Products not selected.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $url = $repricing->getShowDetailsUrl($listingId, $productsIds);

        if ($url === false) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')
                    ->__('The selected Amazon Products cannot be Managed by Amazon Repricing Tool.')
            );
            return $this->indexAction();
        }

        return $this->_redirectUrl($url);
    }

    public function showDetailsAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $responseToken = $this->getRequest()->getParam('response_token');

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $response = $repricing->getResponseData($responseToken);

        if (!empty($response['messages'])) {
            $this->parseRepricingMessages($response['messages']);
        }

        return $this->indexAction();
    }

    //########################################

    public function openEditProductsAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $accountId = $this->getRequest()->getParam('account_id');
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        if (empty($productsIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Products not selected.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $url = $repricing->getEditProductsUrl($listingId, $productsIds);

        if ($url === false) {
            $this->_getSession()->addWarning(Mage::helper('M2ePro')->__(
                'The selected Amazon Products cannot be Managed by Amazon Repricing Tool.')
            );
            return $this->indexAction();
        }

        $this->_redirectUrl($url);
    }

    public function editProductsAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $responseToken = $this->getRequest()->getParam('response_token');

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $response = $repricing->getResponseData($responseToken);

        if (!empty($response['messages'])) {
            $this->parseRepricingMessages($response['messages']);
        }

        return $this->indexAction();
    }

    //########################################

    public function openRemoveProductsAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $accountId = $this->getRequest()->getParam('account_id');
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        if (empty($productsIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Products not selected.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $url = $repricing->getRemoveProductsUrl($listingId, $productsIds);

        if ($url === false) {
            $this->_getSession()->addWarning(Mage::helper('M2ePro')->__(
                'The selected Amazon Products cannot be Managed by Amazon Repricing Tool.')
            );
            return $this->indexAction();
        }

        $this->_redirectUrl($url);
    }

    public function removeProductsAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $responseToken = $this->getRequest()->getParam('response_token');

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $response = $repricing->getResponseData($responseToken);

        if (!empty($response['messages'])) {
            $this->parseRepricingMessages($response['messages']);
        }

        if ($response['status'] == '0') {
            return $this->indexAction();
        }

        if (empty($response['offers'])) {
            return $this->indexAction();
        }

        $skus = array();
        foreach ($response['offers'] as $offer) {
            $skus[] = $offer['sku'];
        }

        $repricing->setProductRepricingStatusBySku(
            $skus,
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_REPRICING_NO
        );

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('Amazon Products have been successfully removed from the Amazon Repricing Tool.')
        );
        return $this->indexAction();
    }

    //########################################

    public function openManagementAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if ($accountId && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricing Ess_M2ePro_Model_Amazon_Repricing */
        $repricing = Mage::getModel('M2ePro/Amazon_Repricing', $model);

        $this->_redirectUrl($repricing->getManagementUrl());
    }

    //########################################

    private function parseRepricingMessages($messages)
    {
        foreach ($messages as $message) {

            if ($message['type'] == 'notice') {
                $this->_getSession()->addNotice($message['text']);
            }

            if ($message['type'] == 'warning') {
                $this->_getSession()->addWarning($message['text']);
            }

            if ($message['type'] == 'error') {
                $this->_getSession()->addError($message['text']);
            }
        }
    }

    //########################################
}
