<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_PickupStore extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountPickupStore');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_account_pickupStore';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton(
            'add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Store'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_ebay_accountPickupStore/new').'\');',
            'class'     => 'add'
            )
        );
        // ---------------------------------------
    }

    //########################################
}
