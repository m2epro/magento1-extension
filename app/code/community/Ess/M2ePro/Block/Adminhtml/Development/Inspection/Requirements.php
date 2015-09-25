<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_Requirements
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentInspectionRequirements');
        //------------------------------

        $this->setTemplate('M2ePro/development/inspection/requirements.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->requirements = Mage::helper('M2ePro/Module')->getRequirementsInfo();

        return parent::_beforeToHtml();
    }

    // ########################################
}