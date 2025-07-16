<?php

class Ess_M2ePro_Adminhtml_Walmart_Account_UnitedStates_AfterGetTokenController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    public function afterGetTokenAction()
    {
        $authCode = $this->getRequest()->getParam('code');
        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id');
        $sellerId = $this->getRequest()->getParam('sellerId');
        /** @var string|null $clientId */
        $clientId = $this->getRequest()->getParam('clientId');
        $specificEndUrl = $this->getRequest()->getParam('specific_end_url');

        if (!$authCode) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'Auth Code is not defined')
            );
            return $this->_redirect('*/adminhtml_walmart_account/index');
        }

        $accountId = (int)$this->getRequest()->getParam('id');

        try {
            if (empty($accountId)) {
                /** @var $account Ess_M2ePro_Model_Account */
                $account = Mage::getModel('M2ePro/Walmart_Account_UnitedStates_Create')->createAccount(
                    $authCode,
                    $marketplaceId,
                    $sellerId,
                    $clientId
                );

                $this->_getSession()->addSuccess(
                    Mage::helper('M2ePro')->__('Account was created successfully.')
                );

                if ($specificEndUrl !== null) {
                    return $this->_redirect($specificEndUrl);
                }

                return $this->_redirect(
                    '*/adminhtml_walmart_account/edit',
                    array(
                        '_current' => true, 'id' => $account->getId()
                    )
                );
            }

            if (!Mage::getModel('M2ePro/Walmart_Account_Repository')->isAccountExists($accountId)) {
                throw new \LogicException('Account not found.');
            }

            Mage::getModel('M2ePro/Walmart_Account_UnitedStates_Update')->updateAccount(
                $authCode,
                $sellerId,
                $accountId,
                $clientId
            );

            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('Account was updated successfully.')
            );

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'Access details were not updated.<br/>Reason: %error_message%',
                    $exception->getMessage()
                )
            );

            if ($specificEndUrl !== null) {
                return $this->_redirectUrl($specificEndUrl);
            }

            if (empty($accountId)) {
                return $this->_redirect('*/adminhtml_walmart_account/index');
            }
        }

        return $this->_redirect(
            '*/adminhtml_walmart_account/edit',
            array(
                'id' => $accountId
            )
        );
    }
}
