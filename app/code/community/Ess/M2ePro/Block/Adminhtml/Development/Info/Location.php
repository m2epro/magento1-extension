<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_Location extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentAboutLocation');
        //------------------------------

        $this->setTemplate('M2ePro/development/info/location.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->locationHost = Mage::helper('M2ePro/Client')->getHost();
        $this->locationDomain = Mage::helper('M2ePro/Client')->getDomain();
        $this->locationIp = Mage::helper('M2ePro/Client')->getIp();

        return parent::_beforeToHtml();
    }

    // ########################################
}