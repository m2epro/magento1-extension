<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

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
        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id', 0);

        $marketplace = Mage::getModel('M2ePro/Marketplace')->load($marketplaceId);

        try {
            $backUrl = $this->getUrl(
                '*/*/afterToken',
                array(
                    'marketplace_id' => $marketplaceId,
                )
            );

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
                \Ess_M2ePro_Model_Servicing_Task_License::NAME
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

    public function afterTokenAction()
    {
        try {
            $amazonData = $this->getAmazonData();
            if ($amazonData === null) {
                return $this->indexAction();
            }

            $marketplaceId = (int)$amazonData['marketplace_id'];
        } catch (\LogicException $exception) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'The Amazon access obtaining is currently unavailable.<br/>Reason: %error_message%',
                    $exception->getMessage()
                )
            );

            return $this->indexAction();
        }

        try {
            $result = Mage::getModel('M2ePro/Amazon_Account_Server_Create')->process(
                $amazonData['token'],
                $amazonData['merchant'],
                $marketplaceId
            );
        } catch (\Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'The Amazon access obtaining is currently unavailable.<br/>Reason: %error_message%',
                    $exception->getMessage()
                )
            );

            return $this->indexAction();
        }

        $this->createAccount(
            $amazonData['merchant'],
            $amazonData['merchant'],
            $marketplaceId,
            $result
        );

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::getModel('M2ePro/Marketplace')->load($marketplaceId);
        $marketplace->setData('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
        $marketplace->save();

        $this->setStep($this->getNextStep());

        return $this->_redirect('*/*/installation');
    }

    /**
     * @return array{merchant:string, token:string, marketplace_id:int}|null
     */
    private function getAmazonData()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params)) {
            return null;
        }

        $requiredFields = array(
            'selling_partner_id',
            'spapi_oauth_code',
            'marketplace_id',
        );

        foreach ($requiredFields as $requiredField) {
            if (!isset($params[$requiredField])) {
                throw new \LogicException($this->__('The Amazon token obtaining is currently unavailable.'));
            }
        }

        return array(
            'merchant' => $params['selling_partner_id'],
            'token' => $params['spapi_oauth_code'],
            'marketplace_id' => $params['marketplace_id'],
        );
    }

    /**
     * @param string                                                $title
     * @param string                                                $merchantId
     * @param int                                                   $marketplaceId
     * @param \Ess_M2ePro_Model_Amazon_Account_Server_Create_Result $serverResult
     *
     * @return void
     */
    private function createAccount(
        $title,
        $merchantId,
        $marketplaceId,
        \Ess_M2ePro_Model_Amazon_Account_Server_Create_Result $serverResult
    ) {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Amazon')->getModel('Account');

        $data = Mage::getModel('M2ePro/Amazon_Account_Builder')->getDefaultData();

        // region prepare data
        $data['magento_orders_settings']['tax']['excluded_states'] = implode(
            ',',
            $data['magento_orders_settings']['tax']['excluded_states']
        );

        $data['magento_orders_settings']['tax']['excluded_countries'] = implode(
            ',',
            $data['magento_orders_settings']['tax']['excluded_countries']
        );
        // endregion

        $data['title'] = $title;
        $data['merchant_id'] = $merchantId;
        $data['marketplace_id'] = $marketplaceId;

        Mage::getModel('M2ePro/Amazon_Account_Builder')->build(
            $account,
            $data
        );

        /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
        $amazonAccount = $account->getChildObject();
        $amazonAccount->setServerHash($serverResult->getHash());
        $amazonAccount->setInfo($serverResult->getInfo());

        $amazonAccount->save();
    }
}
