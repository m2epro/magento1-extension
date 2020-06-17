<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Account as Account;

class Ess_M2ePro_Adminhtml_Wizard_InstallationAmazonController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_WizardController
{
    protected $_sessionKey = 'amazon_listing_product_add';

    //########################################

    protected function _initAction()
    {
        parent::_initAction();

        $this->getLayout()->getBlock('head')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addJs('M2ePro/SynchProgress.js')
             ->addJs('M2ePro/Marketplace.js')
             ->addJs('M2ePro/Wizard/InstallationAmazon.js');

        return $this;
    }

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Amazon::WIZARD_INSTALLATION_NICK;
    }

    //########################################

    public function listingGeneralAction()
    {
        return $this->_redirect('*/adminhtml_amazon_listing_create', array('step' => 1, 'wizard' => true));
    }

    public function listingSellingAction()
    {
        return $this->_redirect('*/adminhtml_amazon_listing_create', array('step' => 2, 'wizard' => true));
    }

    public function listingSearchAction()
    {
        return $this->_redirect('*/adminhtml_amazon_listing_create', array('step' => 3, 'wizard' => true));
    }

    public function sourceModeAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_amazon_listing_productAdd/index',
            array(
                'step'        => 1,
                'id'          => $listingId,
                'new_listing' => true,
                'wizard'      => true
            )
        );
    }

    public function productSelectionAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing')->getLastItem()->getId();

        $productAddSessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey . $listingId);

        return $this->_redirect(
            '*/adminhtml_amazon_listing_productAdd/index',
            array(
                'step'        => 2,
                'source'      => isset($productAddSessionData['source']) ? $productAddSessionData['source'] : null,
                'id'          => $listingId,
                'new_listing' => true,
                'wizard'      => true,
            )
        );
    }

    public function newAsinAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing')->getLastItem()->getId();

        $productAddSessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey . $listingId);

        return $this->_redirect(
            '*/adminhtml_amazon_listing_productAdd/index',
            array(
                'step'        => 2,
                'source'      => isset($productAddSessionData['source']) ? $productAddSessionData['source'] : null,
                'id'          => $listingId,
                'new_listing' => true,
                'wizard'      => true,
            )
        );
    }

    public function searchAsinAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_amazon_listing_productAdd/index',
            array(
                'step'   => 3,
                'id'     => $listingId,
                'wizard' => true,
            )
        );
    }

    //########################################

    public function beforeTokenAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id', 0);

        $marketplace = Mage::getModel('M2ePro/Marketplace')->load($marketplaceId);

        try {
            $backUrl = $this->getUrl('*/*/afterGetTokenAutomatic');

            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account', 'get', 'authUrl',
                array('back_url' => $backUrl, 'marketplace' => $marketplace->getData('native_id'))
            );

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            Mage::getModel('M2ePro/Servicing_Dispatcher')->processTask(
                Mage::getModel('M2ePro/Servicing_Task_License')->getPublicNick()
            );

            $error = 'The Amazon token obtaining is currently unavailable.<br/>Reason: %error_message%';

            if (!Mage::helper('M2ePro/Module_License')->isValidDomain() ||
                !Mage::helper('M2ePro/Module_License')->isValidIp()) {
                $error .= '</br>Go to the <a href="%url%" target="_blank">License Page</a>.';
                $error = Mage::helper('M2ePro')->__(
                    $error,
                    $exception->getMessage(),
                    Mage::helper('M2ePro/View_Configuration')->getLicenseUrl(array('wizard' => 1))
                );
            } else {
                $error = Mage::helper('M2ePro')->__($error, $exception->getMessage());
            }

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('message' => $error)));
        }

        Mage::helper('M2ePro/Data_Session')->setValue('marketplace_id', $marketplaceId);

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('url' => $response['url'])));
    }

    public function afterGetTokenAutomaticAction()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params)) {
            return $this->indexAction();
        }

        $requiredFields = array(
            'Merchant',
            'Marketplace',
            'MWSAuthToken',
            'Signature',
            'SignedString'
        );

        foreach ($requiredFields as $requiredField) {
            if (!isset($params[$requiredField])) {
                $this->_getSession()->addError(
                    Mage::helper('M2ePro')->__('The Amazon token obtaining is currently unavailable.')
                );

                return $this->indexAction();
            }
        }

        $accountData = array_merge(
            $this->getAmazonAccountDefaultSettings(),
            array(
                'title'          => $params['Merchant'],
                'marketplace_id' => Mage::helper('M2ePro/Data_Session')->getValue('marketplace_id'),
                'merchant_id'    => $params['Merchant'],
                'token'          => $params['MWSAuthToken'],
            )
        );

        return $this->processAfterGetToken($accountData);
    }

    public function afterGetTokenManualAction()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params)) {
            return $this->indexAction();
        }

        $requiredFields = array(
            'merchant_id',
            'marketplace_id',
            'token',
        );

        foreach ($requiredFields as $requiredField) {
            if (!isset($params[$requiredField])) {
                $this->_getSession()->addError(
                    Mage::helper('M2ePro')->__('The Amazon token obtaining is currently unavailable.')
                );

                return $this->indexAction();
            }
        }

        $accountData = array_merge(
            $this->getAmazonAccountDefaultSettings(),
            array(
                'title'          => $params['merchant_id'],
                'marketplace_id' => $params['marketplace_id'],
                'merchant_id'    => $params['merchant_id'],
                'token'          => $params['token'],
            )
        );

        return $this->processAfterGetToken($accountData);
    }

    protected function processAfterGetToken($accountData)
    {
        /** @var Ess_M2ePro_Model_Account $accountModel */
        $accountModel = Mage::helper('M2ePro/Component_Amazon')->getModel('Account');
        Mage::getModel('M2ePro/Amazon_Account_Builder')->build($accountModel, $accountData);

        try {
            /** @var $dispatcherObject Ess_M2ePro_Model_Amazon_Connector_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

            $params = array(
                'title'          => $accountData['merchant_id'],
                'marketplace_id' => $accountData['marketplace_id'],
                'merchant_id'    => $accountData['merchant_id'],
                'token'          => $accountData['token'],
            );

            $connectorObj = $dispatcherObject->getConnector(
                'account',
                'add',
                'entityRequester',
                $params,
                $accountModel->getId()
            );
            $dispatcherObject->process($connectorObj);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'The Amazon access obtaining is currently unavailable.<br/>Reason: %error_message%',
                    $exception->getMessage()
                )
            );

            $accountModel->deleteInstance();

            return $this->indexAction();
        }

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::getModel('M2ePro/Marketplace')->load($accountData['marketplace_id']);
        $marketplace->setData('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
        $marketplace->save();

        $this->setStep($this->getNextStep());

        return $this->_redirect('*/*/installation');
    }

    //########################################

    protected function getAmazonAccountDefaultSettings()
    {
        $data = Mage::getModel('M2ePro/Amazon_Account_Builder')->getDefaultData();

        $data['other_listings_synchronization'] = 0;
        $data['other_listings_mapping_mode'] = 0;

        $data['magento_orders_settings']['listing_other']['store_id'] = Mage::helper('M2ePro/Magento_Store')
            ->getDefaultStoreId();

        return $data;
    }

    //########################################
}
