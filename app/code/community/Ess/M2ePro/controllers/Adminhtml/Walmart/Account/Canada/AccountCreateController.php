<?php

class Ess_M2ePro_Adminhtml_Walmart_Account_Canada_AccountCreateController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    public function addAccountAction()
    {
        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id');
        $consumerId = $this->getRequest()->getPost('consumer_id');
        $privateKey = $this->getRequest()->getPost('private_key');
        $title = $this->getRequest()->getPost('title');
        $specificEndUrl = $this->getRequest()->getPost('specific_end_url');

        try {
            /** @var $account Ess_M2ePro_Model_Account */
            $account = Mage::getModel('M2ePro/Walmart_Account_Canada_Create')->createAccount(
                $marketplaceId,
                $consumerId,
                $privateKey,
                $title
            );

            $url = $this->getUrl('*/adminhtml_walmart_account/edit', array(
                '_current' => true,
                'id' => $account->getId()
            ));

            if ($specificEndUrl !== null) {
                $url = $specificEndUrl;
            }

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Account was saved'));

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