<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_Module extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelAboutModule');
        $this->setTemplate('M2ePro/controlPanel/info/module.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->moduleVersion = Mage::helper('M2ePro/Module')->getVersion();
        $this->moduleRevision = Mage::helper('M2ePro/Module')->getRevision();
        $this->moduleEnvironment = Mage::helper('M2ePro/Module')->getEnvironment();

        return parent::_beforeToHtml();
    }

    //########################################
}
