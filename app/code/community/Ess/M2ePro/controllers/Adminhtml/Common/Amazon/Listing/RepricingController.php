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
        $listingId   = $this->getRequest()->getParam('id');
        $accountId   = $this->getRequest()->getParam('account_id');
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        if (empty($productsIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Products not selected.'));
            return $this->indexAction();
        }

        $backUrl = Mage::helper('adminhtml')->getUrl(
            '*/adminhtml_common_amazon_listing_repricing/addProducts',
            array('id' => $listingId, 'account_id' => $accountId)
        );

        /** @var $repricingAction Ess_M2ePro_Model_Amazon_Repricing_Action_Product */
        $repricingAction = Mage::getModel('M2ePro/Amazon_Repricing_Action_Product', $account);
        $serverRequestToken = $repricingAction->sendAddProductsActionData($productsIds, $backUrl);

        if ($serverRequestToken === false) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('The selected Amazon Products cannot be Managed by Amazon Repricing Tool.')
            );
            return $this->indexAction();
        }

        $this->_redirectUrl(
            Mage::helper('M2ePro/Component_Amazon_Repricing')->prepareActionUrl(
                Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_OFFERS_ADD, $serverRequestToken
            )
        );
    }

    public function addProductsAction()
    {
        $accountId     = $this->getRequest()->getParam('account_id');
        $responseToken = $this->getRequest()->getParam('response_token');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricingAction Ess_M2ePro_Model_Amazon_Repricing_Action_Product */
        $repricingAction = Mage::getModel('M2ePro/Amazon_Repricing_Action_Product', $account);
        $response = $repricingAction->getActionResponseData($responseToken);

        if (!empty($response['messages'])) {
            $this->addRepricingMessages($response['messages']);
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

        /** @var $repricingSynchronization Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General */
        $repricingSynchronization = Mage::getModel('M2ePro/Amazon_Repricing_Synchronization_General', $account);
        $repricingSynchronization->run($skus);

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('Amazon Products have been successfully added to the Amazon Repricing Tool.')
        );

        return $this->indexAction();
    }

    //########################################

    public function openShowDetailsAction()
    {
        $listingId   = $this->getRequest()->getParam('id');
        $accountId   = $this->getRequest()->getParam('account_id');
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        if (empty($productsIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Products not selected.'));
            return $this->indexAction();
        }

        $backUrl = Mage::helper('adminhtml')->getUrl(
            '*/adminhtml_common_amazon_listing_repricing/showDetails',
            array('id' => $listingId, 'account_id' => $accountId)
        );

        /** @var $repricingAction Ess_M2ePro_Model_Amazon_Repricing_Action_Product */
        $repricingAction = Mage::getModel('M2ePro/Amazon_Repricing_Action_Product', $account);
        $serverRequestToken = $repricingAction->sendShowProductsDetailsActionData($productsIds, $backUrl);

        if ($serverRequestToken === false) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('The selected Amazon Products cannot be Managed by Amazon Repricing Tool.')
            );
            return $this->indexAction();
        }

        $this->_redirectUrl(
            Mage::helper('M2ePro/Component_Amazon_Repricing')->prepareActionUrl(
                Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_OFFERS_DETAILS, $serverRequestToken
            )
        );
    }

    public function showDetailsAction()
    {
        $accountId     = $this->getRequest()->getParam('account_id');
        $responseToken = $this->getRequest()->getParam('response_token');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricingAction Ess_M2ePro_Model_Amazon_Repricing_Action_Product */
        $repricingAction = Mage::getModel('M2ePro/Amazon_Repricing_Action_Product', $account);
        $response = $repricingAction->getActionResponseData($responseToken);

        if (!empty($response['messages'])) {
            $this->addRepricingMessages($response['messages']);
        }

        return $this->indexAction();
    }

    //########################################

    public function openEditProductsAction()
    {
        $listingId   = $this->getRequest()->getParam('id');
        $accountId   = $this->getRequest()->getParam('account_id');
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        if (empty($productsIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Products not selected.'));
            return $this->indexAction();
        }

        $backUrl = Mage::helper('adminhtml')->getUrl(
            '*/adminhtml_common_amazon_listing_repricing/editProducts',
            array('id' => $listingId, 'account_id' => $accountId)
        );

        /** @var $repricingAction Ess_M2ePro_Model_Amazon_Repricing_Action_Product */
        $repricingAction = Mage::getModel('M2ePro/Amazon_Repricing_Action_Product', $account);
        $serverRequestToken = $repricingAction->sendEditProductsActionData($productsIds, $backUrl);

        if ($serverRequestToken === false) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('The selected Amazon Products cannot be Managed by Amazon Repricing Tool.')
            );
            return $this->indexAction();
        }

        $this->_redirectUrl(
            Mage::helper('M2ePro/Component_Amazon_Repricing')->prepareActionUrl(
                Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_OFFERS_EDIT, $serverRequestToken
            )
        );
    }

    public function editProductsAction()
    {
        $accountId     = $this->getRequest()->getParam('account_id');
        $responseToken = $this->getRequest()->getParam('response_token');

        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricingAction Ess_M2ePro_Model_Amazon_Repricing_Action_Product */
        $repricingAction = Mage::getModel('M2ePro/Amazon_Repricing_Action_Product', $account);
        $response = $repricingAction->getActionResponseData($responseToken);

        if (!empty($response['messages'])) {
            $this->addRepricingMessages($response['messages']);
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

        /** @var $repricingSynchronization Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General */
        $repricingSynchronization = Mage::getModel('M2ePro/Amazon_Repricing_Synchronization_General', $account);
        $repricingSynchronization->run($skus);

        return $this->indexAction();
    }

    //########################################

    public function openRemoveProductsAction()
    {
        $listingId   = $this->getRequest()->getParam('id');
        $accountId   = $this->getRequest()->getParam('account_id');
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        if (empty($productsIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Products not selected.'));
            return $this->indexAction();
        }

        $backUrl = Mage::helper('adminhtml')->getUrl(
            '*/adminhtml_common_amazon_listing_repricing/removeProducts',
            array('id' => $listingId, 'account_id' => $accountId)
        );

        /** @var $repricingAction Ess_M2ePro_Model_Amazon_Repricing_Action_Product */
        $repricingAction = Mage::getModel('M2ePro/Amazon_Repricing_Action_Product', $account);
        $serverRequestToken = $repricingAction->sendRemoveProductsActionData($productsIds, $backUrl);

        if ($serverRequestToken === false) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('The selected Amazon Products cannot be Managed by Amazon Repricing Tool.')
            );
            return $this->indexAction();
        }

        $this->_redirectUrl(
            Mage::helper('M2ePro/Component_Amazon_Repricing')->prepareActionUrl(
                Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_OFFERS_REMOVE, $serverRequestToken
            )
        );
    }

    public function removeProductsAction()
    {
        $accountId     = $this->getRequest()->getParam('account_id');
        $responseToken = $this->getRequest()->getParam('response_token');

        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        /** @var $repricingAction Ess_M2ePro_Model_Amazon_Repricing_Action_Product */
        $repricingAction = Mage::getModel('M2ePro/Amazon_Repricing_Action_Product', $account);
        $response = $repricingAction->getActionResponseData($responseToken);

        if (!empty($response['messages'])) {
            $this->addRepricingMessages($response['messages']);
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

        /** @var $repricingSynchronization Ess_M2ePro_Model_Amazon_Repricing_Synchronization_General */
        $repricingSynchronization = Mage::getModel('M2ePro/Amazon_Repricing_Synchronization_General', $account);
        $repricingSynchronization->run($skus);

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('Amazon Products have been successfully removed from the Amazon Repricing Tool.')
        );
        return $this->indexAction();
    }

    //########################################

    public function openManagementAction()
    {
        $accountId = $this->getRequest()->getParam('id');

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account')->load($accountId);

        if (!$account->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account does not exist.'));
            return $this->indexAction();
        }

        $this->_redirectUrl(Mage::helper('M2ePro/Component_Amazon_Repricing')->getManagementUrl($account));
    }

    //########################################

    public function getUpdatedPriceBySkusAction()
    {
        $groupedSkus = $this->getRequest()->getParam('grouped_skus');

        if (empty($groupedSkus)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $groupedSkus = json_decode($groupedSkus, true);
        $resultPrices = array();

        foreach ($groupedSkus as $accountId => $skus) {
            /** @var Ess_M2ePro_Model_Account $account */
            $account = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Account', $accountId);

            /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
            $amazonAccount = $account->getChildObject();

            $currency = $amazonAccount->getMarketplace()->getChildObject()->getDefaultCurrency();

            $repricingSynchronization = Mage::getModel('M2ePro/Amazon_Repricing_Synchronization_General', $account);
            $repricingSynchronization->run($skus);

            /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
            $listingProductCollection->getSelect()->joinLeft(
                array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
                'l.id = main_table.listing_id',
                array()
            );
            $listingProductCollection->addFieldToFilter('l.account_id', $accountId);
            $listingProductCollection->addFieldToFilter('sku', array('in' => $skus));

            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns(
                array(
                    'second_table.sku',
                    'second_table.online_price'
                )
            );

            $listingsProductsData = $listingProductCollection->getData();

            foreach ($listingsProductsData as $listingProductData) {
                $price = Mage::app()->getLocale()->currency($currency)->toCurrency($listingProductData['online_price']);
                $resultPrices[$accountId][$listingProductData['sku']] = $price;
            }

            /** @var Ess_M2ePro_Model_Mysql4_Listing_Other_Collection $listingOtherCollection */
            $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

            $listingOtherCollection->addFieldToFilter('account_id', $accountId);
            $listingOtherCollection->addFieldToFilter('sku', array('in' => $skus));

            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns(
                array(
                    'second_table.sku',
                    'second_table.online_price'
                )
            );

            $listingsOthersData = $listingOtherCollection->getData();

            foreach ($listingsOthersData as $listingOtherData) {
                $price = Mage::app()->getLocale()->currency($currency)->toCurrency($listingOtherData['online_price']);
                $resultPrices[$accountId][$listingOtherData['sku']] = $price;
            }
        }

        return $this->getResponse()->setBody(json_encode($resultPrices));
    }

    //########################################

    private function addRepricingMessages($messages)
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
