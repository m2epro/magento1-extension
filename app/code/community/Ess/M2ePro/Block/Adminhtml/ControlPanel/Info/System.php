<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_System extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelAboutSystem');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/info/system.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->systemName = Mage::helper('M2ePro/Client')->getSystem();
        $this->systemTime = Mage::helper('M2ePro')->getCurrentGmtDate();

        return parent::_beforeToHtml();
    }

    //########################################
}
