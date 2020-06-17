<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Requirements
    extends Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelInspectionRequirements');
        $this->setTemplate('M2ePro/controlPanel/inspection/requirements.phtml');
    }

    //########################################
}
