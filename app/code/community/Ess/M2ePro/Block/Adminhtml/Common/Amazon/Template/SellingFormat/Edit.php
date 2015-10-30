<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_SellingFormat_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplateSellingFormatEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_amazon_template_sellingFormat';
        $this->_mode = 'edit';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $helper = Mage::helper('M2ePro');
        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Amazon')->getTitle();
            $headerTextEdit = $helper->__("Edit %component_name% Selling Format Policy", $componentName);
            $headerTextAdd = $helper->__("Add %component_name% Selling Format Policy", $componentName);
        } else {
            $headerTextEdit = $helper->__("Edit Selling Format Policy");
            $headerTextAdd = $helper->__("Add Selling Format Policy" );
        }

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data')
            && Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
        ) {
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
        // ---------------------------------------

        // ---------------------------------------
        $url = Mage::helper('M2ePro')->getBackUrl('list');
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'AmazonTemplateSellingFormatHandlerObj.back_click(\'' . $url . '\')',
            'class'     => 'back'
        ));
        // ---------------------------------------

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data')
            && Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
        ) {
            // ---------------------------------------
            $this->_addButton('duplicate', array(
                'label'   => Mage::helper('M2ePro')->__('Duplicate'),
                'onclick' => 'AmazonTemplateSellingFormatHandlerObj.duplicate_click'
                    .'(\'common-amazon-template-sellingFormat\')',
                'class'   => 'add M2ePro_duplicate_button'
            ));
            // ---------------------------------------

            // ---------------------------------------
            $this->_addButton('delete', array(
                'label'     => Mage::helper('M2ePro')->__('Delete'),
                'onclick'   => 'AmazonTemplateSellingFormatHandlerObj.delete_click()',
                'class'     => 'delete M2ePro_delete_button'
            ));
            // ---------------------------------------
        }

        // ---------------------------------------
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'AmazonTemplateSellingFormatHandlerObj.save_click()',
            'class'     => 'save'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'AmazonTemplateSellingFormatHandlerObj.save_and_edit_click()',
            'class'     => 'save'
        ));
        // ---------------------------------------
    }

    //########################################
}