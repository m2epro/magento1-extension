<?php

class Ess_M2ePro_Adminhtml_Wizard_Walmart_AfterGetTokenController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_WizardController
{
    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Walmart::WIZARD_INSTALLATION_NICK;
    }

    public function afterGetTokenAction()
    {
        $authCode = $this->getRequest()->getParam('code');
        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id');
        $sellerId = $this->getRequest()->getParam('sellerId');
        /** @var string|null $clientId */
        $clientId = $this->getRequest()->getParam('clientId');

        if (!$authCode) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'Auth Code is not defined')
            );
            return $this->_redirect('*/adminhtml_wizard_installationWalmart/installation');
        }

        try {
            /** @var $account Ess_M2ePro_Model_Account */
            $account = Mage::getModel('M2ePro/Walmart_Account_UnitedStates_Create')->createAccount(
                $authCode,
                $marketplaceId,
                $sellerId,
                $clientId
            );

            Mage::getModel('M2ePro/Walmart_Account_Builder')->build($account, $this->getAccountDefaultStoreId());

            /** @var Ess_M2ePro_Model_Marketplace $marketplace */
            $marketplace = Mage::getModel('M2ePro/Marketplace')->loadInstance($marketplaceId);
            $marketplace->setData('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $marketplace->save();

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'Account Add Entity failed.<br/>Reason: %error_message%',
                    $exception->getMessage()
                )
            );

            return $this->_redirect('*/adminhtml_wizard_installationWalmart/installation');

        }

        $this->setStep($this->getNextStep());

        return $this->_redirect('*/adminhtml_wizard_installationWalmart/installation');
    }

    /**
     * @return array
     */
    private function getAccountDefaultStoreId()
    {
        $data['magento_orders_settings']['listing_other']['store_id'] = Mage::helper('M2ePro/Magento_Store')
            ->getDefaultStoreId();

        return $data;
    }
}
