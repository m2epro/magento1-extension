<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Account as EbayAccount;

class Ess_M2ePro_Adminhtml_Wizard_InstallationEbayController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_WizardController
{
    protected $_sessionKey = 'ebay_listing_product_add';

    //########################################

    protected function _initAction()
    {
        $result = parent::_initAction();

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Wizard/InstallationEbay.js');

        return $result;
    }

    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK;
    }

    //########################################

    public function beforeGetSellApiTokenAction()
    {
        $accountMode = $this->getRequest()->getParam('mode');

        if ($accountMode === null) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array('message' => Mage::helper('M2ePro')->__('Account type have not been specified.'))
                )
            );
        }

        try {
            $backUrl = $this->getUrl('*/*/afterGetSellApiToken', array('mode' => $accountMode));

            $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'account',
                'get',
                'grantAccessUrl',
                array('back_url' => $backUrl, 'mode' => $accountMode),
                null,
                null,
                null
            );

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            Mage::getModel('M2ePro/Servicing_Dispatcher')->processTask(
                \Ess_M2ePro_Model_Servicing_Task_License::NAME
            );

            $error = 'The eBay token obtaining is currently unavailable.<br/>Reason: %error_message%';

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

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('url' => $response['url'])));
    }

    public function afterGetSellApiTokenAction()
    {
        $accountMode = $this->getRequest()->getParam('mode');
        $authCode = base64_decode($this->getRequest()->getParam('code'));

        $params = array(
            'mode'          => $accountMode,
            'auth_code' => $authCode
        );

        $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'account',
            'add',
            'entity',
            $params,
            null,
            null,
            null
        );

        $dispatcherObject->process($connectorObj);
        $responseData = array_filter($connectorObj->getResponseData());

        if (empty($responseData)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account Add Entity failed.'));

            return $this->_redirect('*/*/installation');
        }
        try {
            $account = Mage::getModel('Ess_M2ePro_Model_Ebay_Account_Create')->create(
                $authCode,
                $accountMode === 'sandbox' ? EbayAccount::MODE_SANDBOX : EbayAccount::MODE_PRODUCTION
            );
        } catch (Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());
            return $this->_redirect('*/*/installation');
        }


        Mage::getModel('M2ePro/Ebay_Account_Store_Category_Update')->process($account->getChildObject());

        $this->setStep($this->getNextStep());

        return $this->_redirect('*/*/installation');
    }

    public function listingAccountAction()
    {
        return $this->_redirect('*/adminhtml_ebay_listing_create', array('step' => 1,'wizard' => true,'clear' => true));
    }

    public function listingGeneralAction()
    {
        return $this->_redirect('*/adminhtml_ebay_listing_create', array('step' => 2, 'wizard' => true));
    }

    public function listingTemplatesAction()
    {
        return $this->_redirect('*/adminhtml_ebay_listing_create', array('step' => 2, 'wizard' => true));
    }

    public function sourceModeAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_ebay_listing_productAdd/sourceMode',
            array(
                'wizard' => true,
                'listing_id' => $listingId,
                'listing_creation' => true
            )
        );
    }

    public function productSelectionAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        $productAddSessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey . $listingId);

        return $this->_redirect(
            '*/adminhtml_ebay_listing_productAdd',
            array(
                'clear'            => true,
                'step'             => 1,
                'wizard'           => true,
                'listing_id'       => $listingId,
                'listing_creation' => true,
                'source'           => isset($productAddSessionData['source']) ? $productAddSessionData['source'] : null
            )
        );
    }

    public function productSettingsAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_ebay_listing_productAdd',
            array(
                'step'             => 2,
                'wizard'           => true,
                'listing_id'       => $listingId,
                'listing_creation' => true,
            )
        );
    }

    public function categoryStepOneAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_ebay_listing_categorySettings',
            array(
                'step'             => 1,
                'wizard'           => true,
                'listing_id'       => $listingId,
                'listing_creation' => true,
            )
        );
    }

    public function categoryStepTwoAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_ebay_listing_categorySettings',
            array(
                'step'             => 2,
                'wizard'           => true,
                'listing_id'       => $listingId,
                'listing_creation' => true,
            )
        );
    }

    public function categoryStepThreeAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_ebay_listing_categorySettings',
            array(
                'step'             => 3,
                'wizard'           => true,
                'listing_id'       => $listingId,
                'listing_creation' => true,
            )
        );
    }

    //########################################

    protected function getEbayAccountDefaultSettings()
    {
        $data = Mage::getModel('M2ePro/Ebay_Account_Builder')->getDefaultData();

        $data['marketplaces_data'] = array();

        $data['other_listings_synchronization'] = 0;

        $data['magento_orders_settings']['listing_other']['store_id'] = Mage::helper('M2ePro/Magento_Store')
            ->getDefaultStoreId();
        $data['magento_orders_settings']['qty_reservation']['days'] = 0;

        return $data;
    }
}
