<?php

class Ess_M2ePro_Adminhtml_Walmart_Account_UnitedStates_BeforeGetTokenController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    public function beforeGetTokenAction()
    {
        $accountId = (int)$this->getRequest()->getParam('id');
        $specificEndUrl = $this->getRequest()->getParam('specific_end_url');

        try {
            $backUrl = $this->getUrl(
                '*/adminhtml_walmart_account_unitedStates_afterGetToken/afterGetToken',
                array(
                    'id' => $accountId,
                    '_current' => true,
                    'specific_end_url' => urlencode($specificEndUrl),
                )
            );

            $response = Mage::getModel('M2ePro/Walmart_Connector_Account_GetGrantAccessUrl_Processor')->process($backUrl);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $error = 'The Walmart access obtaining is currently unavailable.<br/>Reason: %error_message%';
            $error = Mage::helper('M2ePro')->__($error, $exception->getMessage());

            $this->_getSession()->addError($error);

            return $this->_redirect('*/adminhtml_walmart_account/index', array('_current' => true));
        }

        $this->_redirectUrl($response->getUrl());
    }
}
