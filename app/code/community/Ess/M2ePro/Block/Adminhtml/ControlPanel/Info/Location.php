<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_Location extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelAboutLocation');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/info/location.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->locationHost = Mage::helper('M2ePro/Client')->getHost();
        $this->locationDomain = Mage::helper('M2ePro/Client')->getDomain();
        $this->locationIp = Mage::helper('M2ePro/Client')->getIp();
        $this->locationDirectory = Mage::helper('M2ePro/Client')->getBaseDirectory();

        return parent::_beforeToHtml();
    }

    //########################################
}
