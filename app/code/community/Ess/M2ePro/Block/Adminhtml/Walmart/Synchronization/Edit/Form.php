<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Synchronization_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartSynchronizationForm');
        $this->setContainerId('magento_block_walmart_synchronization');
        $this->setTemplate('M2ePro/walmart/synchronization/edit/form.phtml');
        // ---------------------------------------
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $this->instructionsMode = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/cron/task/walmart/listing/product/process_instructions/', 'mode');
        // ---------------------------------------

        // ---------------------------------------
        $this->inspectorMode = Mage::helper('M2ePro/Module_Configuration')->isEnableListingProductInspectorMode();
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}