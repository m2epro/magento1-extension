<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_Php extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentAboutPhp');
        //------------------------------

        $this->setTemplate('M2ePro/development/info/php.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->phpVersion = Mage::helper('M2ePro/Client')->getPhpVersion();
        $this->phpApi = Mage::helper('M2ePro/Client')->getPhpApiName();
        $this->phpSettings = Mage::helper('M2ePro/Client')->getPhpSettings();

        return parent::_beforeToHtml();
    }

    // ########################################
}