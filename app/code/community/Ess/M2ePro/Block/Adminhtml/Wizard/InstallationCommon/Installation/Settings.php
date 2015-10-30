<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Installation_Settings
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInstallationSettings');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/installationCommon/installation/settings.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData(array(
                                          'label'   => Mage::helper('M2ePro')->__('Proceed'),
                                          'onclick' => '',
                                          'id'      => 'process_settings_button'
                                      ));
        $this->setChild('process_settings_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $this->setData('is_ebay_active', Mage::helper('M2ePro/Component_Ebay')->isActive());
        $this->setData('is_amazon_active', Mage::helper('M2ePro/Component_Amazon')->isActive());
        $this->setData('is_buy_active', Mage::helper('M2ePro/Component_Buy')->isActive());

        $this->setData('is_ebay_allowed', Mage::helper('M2ePro/Component_Ebay')->isActive());
        $this->setData('is_amazon_allowed', Mage::helper('M2ePro/Component_Amazon')->isAllowed());
        $this->setData('is_buy_allowed', Mage::helper('M2ePro/Component_Buy')->isAllowed());
        $this->setData('is_rakuten_allowed', Mage::helper('M2ePro/Component')->isRakutenAllowed());

        $this->setData('default_component', Mage::helper('M2ePro/View_Common_Component')->getDefaultComponent());
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}