<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Tabs_Inspection extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelInspection');
        $this->setTemplate('M2ePro/controlPanel/tabs/inspection.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->setChild(
            'inspections', $this->getLayout()->createBlock(
                'M2ePro/adminhtml_controlPanel_inspection_grid'
            )
        );

        return parent::_beforeToHtml();
    }

    //########################################
}
