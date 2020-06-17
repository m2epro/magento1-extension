<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationWalmart_Installation_Account
    extends Ess_M2ePro_Block_Adminhtml_Wizard_InstallationWalmart_Installation
{
    //########################################

    protected function getStep()
    {
        return 'account';
    }

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addClassConstants('Ess_M2ePro_Model_Walmart_Account');
        Mage::helper('M2ePro/View')->getJsPhpRenderer()->addClassConstants('Ess_M2ePro_Helper_Component_Walmart');

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_wizard_installationWalmart');
        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_walmart_account');

        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'M2E Pro was not able to get access to the Walmart Account' => Mage::helper('M2ePro')->__(
                    'M2E Pro could not get access to your Walmart account. <br>
                 For Walmart CA, please check if you entered valid Consumer ID and Private Key. <br>
                 For Walmart US, please ensure to provide M2E Pro with full access permissions
                 to all API sections and enter valid Consumer ID, Client ID, and Client Secret.'
                ),
                'M2E Pro was not able to get access to the Walmart Account. Reason: %error_message%' =>
                    Mage::helper('M2ePro')->__(
                        'M2E Pro was not able to get access to the Walmart Account. Reason: %error_message%'
                    ),

                'Consumer ID' => 'Consumer ID',
                'Consumer ID / Partner ID' => 'Consumer ID / Partner ID',
                'The specified Consumer ID / Partner ID is not valid' => Mage::helper('M2ePro')->__(
                    'The specified Consumer ID / Partner ID is not valid.
                 Please find the instruction on how to get it <a target="_blank" href="%url%">here</a>.',
                    Mage::helper('M2ePro/Module_Support')->getSupportUrl(
                        'how-to-guide/1570387-how-to-get-my-consumer-id-partner-id-to-auth-m2e-on-walmart-us'
                    )
                )
            )
        );

        parent::_prepareLayout();
    }

    //########################################
}
