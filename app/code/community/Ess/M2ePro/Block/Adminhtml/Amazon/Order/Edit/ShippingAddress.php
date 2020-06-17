<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_Edit_ShippingAddress
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
        $this->_controller = 'adminhtml_amazon_order_edit';
        $this->_mode = 'shippingAddress';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Amazon')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('Edit %component_name% Shipping Address', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Edit Shipping Address');
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

        if ($this->getRequest()->getParam('back') !== null) {
            // ---------------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_amazon_order/index');
            $this->_addButton(
                'back', array(
                    'label'   => Mage::helper('M2ePro')->__('Back'),
                    'onclick' => 'CommonObj.back_click(\'' . $url . '\')',
                    'class'   => 'back'
                )
            );
            // ---------------------------------------
        } else {
            // ---------------------------------------
            $url = $this->getUrl('*/*/view', array('id' => $this->getRequest()->getParam('id')));
            $this->_addButton(
                'back', array(
                    'label'   => Mage::helper('M2ePro')->__('Back'),
                    'onclick' => 'CommonObj.back_click(\'' . $url . '\')',
                    'class'   => 'back'
                )
            );
            // ---------------------------------------
        }

        // ---------------------------------------
        $this->_addButton(
            'save', array(
                'label'   => Mage::helper('M2ePro')->__('Save Order Address'),
                'onclick' => 'CommonObj.save_click()',
                'class'   => 'save'
            )
        );
        // ---------------------------------------
    }

    //########################################
}