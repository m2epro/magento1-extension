<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_account';
        $this->_mode = 'edit';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/View_Ebay_Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Ebay')->getTitle();
            $headerTextEdit = Mage::helper('M2ePro')->__("Edit %component_name% Account", $componentName);
            $headerTextAdd = Mage::helper('M2ePro')->__("Add %component_name% Account", $componentName);
        } else {
            $headerTextEdit = Mage::helper('M2ePro')->__("Edit Account");
            $headerTextAdd = Mage::helper('M2ePro')->__("Add Account");
        }

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()) {
            $this->_headerText = $headerTextEdit;
            $this->_headerText .= ' "'.$this->escapeHtml(
                Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getTitle()).'"';
        } else {
            $this->_headerText = $headerTextAdd;
        }
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if ((bool)$this->getRequest()->getParam('close_on_save',false)) {

            if ($this->getRequest()->getParam('id')) {
                $this->_addButton('save', array(
                    'label'     => Mage::helper('M2ePro')->__('Save And Close'),
                    'onclick'   => 'EbayAccountHandlerObj.saveAndClose()',
                    'class'     => 'save'
                ));
            } else {
                $this->_addButton('save_and_continue', array(
                    'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                    'onclick'   => 'EbayAccountHandlerObj.save_and_edit_click(\'\',\'ebayAccountEditTabs\')',
                    'class'     => 'save'
                ));
            }
            return;
        }

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'EbayAccountHandlerObj.back_click(\'' .Mage::helper('M2ePro')->getBackUrl('list').'\')',
            'class'     => 'back'
        ));

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()) {

            $this->_addButton('delete', array(
                'label'     => Mage::helper('M2ePro')->__('Delete'),
                'onclick'   => 'EbayAccountHandlerObj.delete_click()',
                'class'     => 'delete M2ePro_delete_button'
             ));

            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save'),
                'onclick'   => 'EbayAccountHandlerObj.save_click()',
                'class'     => 'save'
            ));
        }

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'EbayAccountHandlerObj.save_and_edit_click(\'\',\'ebayAccountEditTabs\')',
            'class'     => 'save'
        ));
    }

    //########################################
}