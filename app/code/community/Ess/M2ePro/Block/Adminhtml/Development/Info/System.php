<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_System extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentAboutSystem');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/info/system.phtml');
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