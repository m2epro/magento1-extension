<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Wizard_InstallationWalmartController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_WizardController
{
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
             ->addJs('M2ePro/Wizard/InstallationWalmart.js')
             ->addJs('M2ePro/Walmart/Account.js')
             ->addJs('M2ePro/Walmart/Configuration/General.js');

        return $this;
    }

    //########################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Walmart::WIZARD_INSTALLATION_NICK;
    }

    //########################################

    public function accountContinueAction()
    {
        $params = $this->getRequest()->getParams();
        if (empty($params)) {
            return $this->indexAction();
        }

        if (!$this->validateRequiredParams($params)) {
            $error = Mage::helper('M2ePro')->__('You should fill all required fields.');
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('message' => $error)));
        }

        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id');
        $consumerId = $this->getRequest()->getPost('consumer_id');
        $privateKey = $this->getRequest()->getPost('private_key');

        /** @var Ess_M2ePro_Model_Marketplace $marketplace */
        $marketplace = Mage::getModel('M2ePro/Marketplace')->loadInstance($marketplaceId);
        $marketplace->setData('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
        $marketplace->save();

        $title = "Default - {$marketplace->getCode()}";

        try {
            /** @var $account Ess_M2ePro_Model_Account */
            $account = Mage::getModel('M2ePro/Walmart_Account_Canada_Create')->createAccount(
                $marketplaceId,
                $consumerId,
                $privateKey,
                $title
            );

            Mage::getModel('M2ePro/Walmart_Account_Builder')->build($account, $this->getAccountDefaultStoreId());
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            Mage::getModel('M2ePro/Servicing_Dispatcher')->processTask(
                \Ess_M2ePro_Model_Servicing_Task_License::NAME
            );

            $error = 'The Walmart access obtaining is currently unavailable.<br/>Reason: %error_message%';

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

        $this->setStep($this->getNextStep());

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => true)));
    }

    public function listingTutorialContinueAction()
    {
        Mage::helper('M2ePro/Module_Wizard')->setStatus(
            Ess_M2ePro_Helper_View_Walmart::WIZARD_INSTALLATION_NICK,
            Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED
        );

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        return $this->_redirect('*/adminhtml_walmart_listing_create');
    }

    public function settingsAction()
    {
        return $this->renderSimpleStep();
    }

    public function settingsContinueAction()
    {
        $params = $this->getRequest()->getParams();
        if (empty($params)) {
            return $this->indexAction();
        }

        Mage::helper('M2ePro/Component_Walmart_Configuration')->setConfigValues($params);

        $this->setStep($this->getNextStep());

        return $this->_redirect('*/*/installation');
    }

    //########################################

    protected function getAccountDefaultStoreId()
    {
        $data['magento_orders_settings']['listing_other']['store_id'] = Mage::helper('M2ePro/Magento_Store')
            ->getDefaultStoreId();

        return $data;
    }

    protected function validateRequiredParams($params)
    {
        if (empty($params['marketplace_id'])) {
            return false;
        }

        if (empty($params['consumer_id']) || empty($params['private_key'])) {
            return false;
        }

        return true;
    }

    //########################################
}
