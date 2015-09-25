<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation_Intro_Content
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationIntro');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/migrationToV6/installation/intro.phtml');
    }

    // ########################################

    protected function _toHtml()
    {
        $breadcrumbBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_wizard_migrationToV6_breadcrumb');

        return $breadcrumbBlock->toHtml() . parent::_toHtml();
    }

    // ########################################
}