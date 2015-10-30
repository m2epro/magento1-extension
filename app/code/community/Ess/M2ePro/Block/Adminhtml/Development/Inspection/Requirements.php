<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_Requirements
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentInspectionRequirements');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/inspection/requirements.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->requirements = Mage::helper('M2ePro/Module')->getRequirementsInfo();

        return parent::_beforeToHtml();
    }

    //########################################
}