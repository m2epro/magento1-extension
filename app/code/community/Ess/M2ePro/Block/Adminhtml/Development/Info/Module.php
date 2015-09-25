<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_Module extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentAboutModule');
        //------------------------------

        $this->setTemplate('M2ePro/development/info/module.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->moduleName = Mage::helper('M2ePro/Module')->getName();
        $this->moduleVersion = Mage::helper('M2ePro/Module')->getVersion();
        $this->moduleRevision = Mage::helper('M2ePro/Module')->getRevision();

        return parent::_beforeToHtml();
    }

    // ########################################
}