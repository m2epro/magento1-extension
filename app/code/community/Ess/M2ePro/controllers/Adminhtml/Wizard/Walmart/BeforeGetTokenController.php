<?php

class Ess_M2ePro_Adminhtml_Wizard_Walmart_BeforeGetTokenController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_WizardController
{
    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Walmart::WIZARD_INSTALLATION_NICK;
    }

    public function beforeGetTokenAction()
    {
        try {
            $backUrl = $this->getUrl(
                '*/adminhtml_wizard_walmart_afterGetToken/afterGetToken',
                array(
                    '_current' => true,
                )
            );

            $response = Mage::getModel('M2ePro/Walmart_Connector_Account_GetGrantAccessUrl_Processor')->process($backUrl);

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

        $this->_redirectUrl($response->getUrl());
    }
}
