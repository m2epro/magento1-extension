<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Order_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyOrderView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_buy_order';
        $this->_mode = 'view';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('View Order Details');
        // ---------------------------------------

        /** @var $order Ess_M2ePro_Model_Order */
        $this->order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

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
        $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_order/index');
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
            'class'     => 'back'
        ));
        // ---------------------------------------

        if (is_null($this->order->getMagentoOrderId())) {
            // ---------------------------------------
            $url = $this->getUrl('*/*/createMagentoOrder', array('id' => $this->order->getId()));
            $this->_addButton('order', array(
                'label'     => Mage::helper('M2ePro')->__('Create Order'),
                'onclick'   => "setLocation('".$url."');",
                'class'     => 'scalable'
            ));
            // ---------------------------------------
        } else if (is_null($this->order->getMagentoOrder()) || $this->order->getMagentoOrder()->isCanceled()) {
            // ---------------------------------------
            $url = $this->getUrl('*/*/createMagentoOrder', array('id' => $this->order->getId(), 'force' => 'yes'));
            $confirm = Mage::helper('M2ePro')->escapeJs(
                Mage::helper('M2ePro')->__('Are you sure that you want to create new Magento Order?')
            );

            $this->_addButton('order', array(
                'label'     => Mage::helper('M2ePro')->__('Create Order'),
                'onclick'   => "confirmSetLocation('".$confirm."','".$url."');",
                'class'     => 'scalable'
            ));
            // ---------------------------------------
        }
    }

    //########################################
}