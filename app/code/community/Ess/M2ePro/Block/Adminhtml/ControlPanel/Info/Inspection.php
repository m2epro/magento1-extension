<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_Inspection extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelSummaryInspection');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/info/inspection.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->mainChecks = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $this->getUrl('*/adminhtml_controlPanel_inspection/mainChecks'),
            Mage::helper('M2ePro')->__('Show')
        );
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}
