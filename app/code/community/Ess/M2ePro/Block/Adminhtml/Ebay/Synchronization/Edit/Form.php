<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Synchronization_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('edit_form');
        $this->setContainerId('magento_block_ebay_synchronization');
        $this->setTemplate('M2ePro/ebay/synchronization/form.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $this->instructionsMode = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/cron/task/ebay/listing/product/process_instructions/', 'mode');
        // ---------------------------------------

        // ---------------------------------------
        $this->inspectorMode = Mage::helper('M2ePro/Module_Configuration')->isEnableListingProductInspectorMode();
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}