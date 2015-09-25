<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation_SellingFormatCurrencies_Content
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationSellingFormatCurrencies');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/migrationToV6/installation/selling_format_currencies.phtml');
    }

    // ########################################

    protected function _toHtml()
    {
        $breadcrumbBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_wizard_migrationToV6_breadcrumb');

        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_wizard_migrationToV6_installation_sellingFormatCurrencies_tabs'
        );

        return $breadcrumbBlock->toHtml() . parent::_toHtml() . $tabsBlock->toHtml();
    }

    // ########################################

    public function getMigrationModel()
    {
        return Mage::helper('M2ePro/Module_Wizard')->getWizard(Ess_M2ePro_Helper_Module::WIZARD_MIGRATION_NICK);
    }

    // ########################################
}