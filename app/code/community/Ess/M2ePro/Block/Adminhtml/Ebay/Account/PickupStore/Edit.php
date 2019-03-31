<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_PickupStore_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountPickupStoreEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_account_pickupStore';
        $this->_mode = 'edit';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()) {
            $this->_headerText = Mage::helper('M2ePro')->__("Edit Store");
            $this->_headerText .= ' "'.$this->escapeHtml(
                    Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getName()).'"';
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("Add Store");
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

        $url = $this->getUrl('M2ePro/adminhtml_ebay_accountPickupStore/save',
            array('back' => Mage::helper('M2ePro')->makeBackUrlParam('edit', array()))
        );

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'EbayPickupStoreHandlerObj.back_click(\'' .Mage::helper('M2ePro')->getBackUrl('list').'\')',
            'class'     => 'back'
        ));

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()) {

            $duplicateHeaderText = Mage::helper('M2ePro')->__('Add Store');

            $this->_addButton('duplicate', array(
                'label'     => Mage::helper('M2ePro')->__('Duplicate'),
                'onclick'   => 'EbayPickupStoreHandlerObj.duplicate_click(
                    \'ebay-account-pickupStore\', \''.$duplicateHeaderText.'\'
                    )',
                'class'     => 'add M2ePro_duplicate_button'
            ));

            $this->_addButton('delete', array(
                'label'     => Mage::helper('M2ePro')->__('Delete'),
                'onclick'   => 'EbayPickupStoreHandlerObj.delete_click()',
                'class'     => 'delete M2ePro_delete_button'
            ));

            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save'),
                'onclick'   => 'EbayPickupStoreHandlerObj.save_click()',
                'class'     => 'save'
            ));
        }

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'EbayPickupStoreHandlerObj.save_click()',
            'class'     => 'save'
        ));

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'EbayPickupStoreHandlerObj.save_and_edit_click(\''.$url.'\')',
            'class'     => 'save'
        ));
        // ---------------------------------------
    }

    //########################################
}