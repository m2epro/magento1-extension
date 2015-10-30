<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Tabs_Stop
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Data
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayTemplateSynchronizationEditFormTabsStop');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/template/synchronization/form/tabs/stop.phtml');
    }

    //########################################

    public function getDefault()
    {
        return Mage::helper('M2ePro/View_Ebay')->isSimpleMode()
            ? Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getStopDefaultSettingsSimpleMode()
            : Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getStopDefaultSettingsAdvancedMode();
    }

    //########################################
}