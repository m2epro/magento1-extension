<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelGeneralForm');
        $this->setTemplate('M2ePro/controlPanel.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $tabsBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_tabs');
        $this->setChild('tabs_controlPanel', $tabsBlock);

        return parent::_beforeToHtml();
    }

    //########################################
}
