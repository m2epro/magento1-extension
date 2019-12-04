<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_Php extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelAboutPhp');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/info/php.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->phpVersion = Mage::helper('M2ePro/Client')->getPhpVersion();
        $this->phpApi = Mage::helper('M2ePro/Client')->getPhpApiName();
        $this->phpSettings = Mage::helper('M2ePro/Client')->getPhpSettings();

        return parent::_beforeToHtml();
    }

    //########################################
}
