<?php

class Ess_M2ePro_Adminhtml_Walmart_Account_Canada_AccountUpdateController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    public function updateCredentialsAction()
    {
        $consumerId = $this->getRequest()->getPost('consumer_id');
        $privateKey = $this->getRequest()->getPost('private_key');
        $accountId = (int)$this->getRequest()->getParam('id');

        try {
            /** @var $account Ess_M2ePro_Model_Account */
            $account = Mage::getModel('M2ePro/Walmart_Account_Canada_Update')->updateAccount(
                $consumerId,
                $privateKey,
                $accountId
            );

            $url = $this->getUrl('*/adminhtml_walmart_account/edit', array(
                '_current' => true,
                'id' => $account->getId()
            ));

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Access Details were updated'));

            return $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(
                    array(
                        'success' => true,
                        'redirectUrl' => $url
                    )
                )
            );

        } catch (\Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            return $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(
                    array(
                        'success' => false,
                        'message' => $exception->getMessage()
                    )
                )
            );
        }
    }
}