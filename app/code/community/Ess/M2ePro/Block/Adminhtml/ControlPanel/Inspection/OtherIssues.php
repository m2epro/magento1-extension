<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_OtherIssues
    extends Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelInspectionOtherIssues');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/inspection/otherIssues.phtml');
    }

    //########################################

    protected function isShown()
    {
        return $this->isGdLibraryUnAvailable() ||
               $this->isZendOpcacheAvailable();
    }

    //########################################

    public function isGdLibraryUnAvailable()
    {
        return !extension_loaded('gd') || !function_exists('gd_info');
    }

    public function isZendOpcacheAvailable()
    {
        return Mage::helper('M2ePro/Client_Cache')->isZendOpcacheAvailable();
    }

    //########################################
}
