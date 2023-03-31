<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation_Account
    extends Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation
{
    protected function getStep()
    {
        return 'account';
    }

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsTranslatorRenderer()->addTranslations(
            array(
                'The specified Title is already used for other Account. Account Title must be unique.' =>
                    Mage::helper('M2ePro')->__(
                        'The specified Title is already used for other Account. Account Title must be unique.'
                    ),
                'Please fill Merchant ID and MWS Auth Token fields.' => Mage::helper('M2ePro')->__(
                    'Please fill Merchant ID and MWS Auth Token fields.'
                ),
                'Please select Marketplace first.' => Mage::helper('M2ePro')->__('Please select Marketplace first.'),
                'An error during of account creation.' => Mage::helper('M2ePro')->__(
                    'The Amazon token obtaining is currently unavailable. Please try again later.'
                ),
            )
        );

        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_wizard_installationAmazon');
        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions('adminhtml_amazon_account');

        parent::_prepareLayout();
    }
}
