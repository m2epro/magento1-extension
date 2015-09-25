<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_System extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentAboutSystem');
        //------------------------------

        $this->setTemplate('M2ePro/development/info/system.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->systemName = Mage::helper('M2ePro/Client')->getSystem();
        $this->systemTime = Mage::helper('M2ePro')->getCurrentGmtDate();

        return parent::_beforeToHtml();
    }

    // ########################################
}