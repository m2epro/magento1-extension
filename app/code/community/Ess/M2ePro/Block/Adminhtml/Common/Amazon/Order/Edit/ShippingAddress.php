<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Order_Edit_ShippingAddress
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderEditShippingAddress');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_amazon_order_edit';
        $this->_mode = 'shippingAddress';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Edit Shipping Address');
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

        if (!is_null($this->getRequest()->getParam('back'))) {
            // ---------------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_order/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
            // ---------------------------------------
        } else {
            // ---------------------------------------
            $url = $this->getUrl('*/*/view', array('id' => $this->getRequest()->getParam('id')));
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
            // ---------------------------------------
        }

        // ---------------------------------------
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save Order Address'),
            'onclick'   => 'CommonHandlerObj.save_click()',
            'class'     => 'save'
        ));
        // ---------------------------------------
    }

    //########################################
}