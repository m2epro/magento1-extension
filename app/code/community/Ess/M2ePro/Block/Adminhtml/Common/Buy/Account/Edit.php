<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyAccountEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_buy_account';
        $this->_mode = 'edit';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Buy')->getTitle();
            $headerTextEdit = $this->_headerText = Mage::helper('M2ePro')->__(
                "Edit %component_name% Account",
                $componentName
            );
            $headerTextAdd = $this->_headerText = Mage::helper('M2ePro')->__(
                "Add %component_name% Account",
                $componentName
            );
        } else {
            $headerTextEdit = $this->_headerText = Mage::helper('M2ePro')->__("Edit Account");
            $headerTextAdd = $this->_headerText = Mage::helper('M2ePro')->__("Add Account");
        }

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
        ) {
            $this->_headerText = $headerTextEdit;
            $this->_headerText .= ' "'.$this->escapeHtml(
                Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getTitle()
            ).'"';
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

        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if ($wizardHelper->isActive('buy') &&
            $wizardHelper->getStep('buy') == 'account'
        ) {

            // ---------------------------------------
            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'BuyAccountHandlerObj.save_and_edit_click(\'\',\'buyAccountEditTabs\')',
                'class'     => 'save'
            ));
            // ---------------------------------------

            if ($this->getRequest()->getParam('id')) {
                // ---------------------------------------
                $url = $this->getUrl('*/adminhtml_common_buy_account/new', array('wizard' => true));
                $this->_addButton('add_new_account', array(
                    'label'     => Mage::helper('M2ePro')->__('Add New Account'),
                    'onclick'   => 'setLocation(\''. $url .'\')',
                    'class'     => 'add_new_account'
                ));
                // ---------------------------------------

                // ---------------------------------------
                $this->_addButton('close', array(
                    'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                    'onclick'   => 'BuyAccountHandlerObj.completeStep();',
                    'class'     => 'close'
                ));
                // ---------------------------------------
            }
        } else {

            if ((bool)$this->getRequest()->getParam('close_on_save',false)) {

                if ($this->getRequest()->getParam('id')) {
                    $this->_addButton('save', array(
                        'label'     => Mage::helper('M2ePro')->__('Save And Close'),
                        'onclick'   => 'BuyAccountHandlerObj.saveAndClose()',
                        'class'     => 'save'
                    ));
                } else {
                    $this->_addButton('save_and_continue', array(
                        'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                        'onclick'   => 'BuyAccountHandlerObj.save_and_edit_click(\'\',\'buyAccountEditTabs\')',
                        'class'     => 'save'
                    ));
                }
                return;
            }

            // ---------------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('list');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'BuyAccountHandlerObj.back_click(\'' . $url .'\')',
                'class'     => 'back'
            ));
            // ---------------------------------------

            if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
                Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
            ) {
                // ---------------------------------------
                $this->_addButton('delete', array(
                    'label'     => Mage::helper('M2ePro')->__('Delete'),
                    'onclick'   => 'BuyAccountHandlerObj.delete_click()',
                    'class'     => 'delete M2ePro_delete_button'
                ));
                // ---------------------------------------
            }

            // ---------------------------------------
            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save'),
                'onclick'   => 'BuyAccountHandlerObj.save_click()',
                'class'     => 'save'
            ));
            // ---------------------------------------

            // ---------------------------------------
            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'BuyAccountHandlerObj.save_and_edit_click(\'\',\'buyAccountEditTabs\')',
                'class'     => 'save'
            ));
            // ---------------------------------------
        }
    }

    //########################################
}