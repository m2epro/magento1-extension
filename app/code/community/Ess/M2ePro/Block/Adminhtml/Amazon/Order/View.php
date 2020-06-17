<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /** @var $_order Ess_M2ePro_Model_Order */
    protected $_order;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_order';
        $this->_mode = 'view';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Amazon')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('View %component_name% Order Details', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('View Order Details');
        }

        // ---------------------------------------

        $this->_order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

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
        $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_amazon_order/index');
        $this->_addButton(
            'back', array(
                'label'   => Mage::helper('M2ePro')->__('Back'),
                'onclick' => 'CommonObj.back_click(\'' . $url . '\')',
                'class'   => 'back'
            )
        );
        // ---------------------------------------

        if ($this->_order->getChildObject()->canUpdateShippingStatus() && !$this->_order->getChildObject()->isPrime()) {
            // ---------------------------------------
            $url = $this->getUrl('*/*/updateShippingStatus', array('id' => $this->_order->getId()));
            $this->_addButton(
                'update_shipping_status', array(
                    'label'   => Mage::helper('M2ePro')->__('Mark as Shipped'),
                    'onclick' => "setLocation('" . $url . "');",
                    'class'   => 'scalable'
                )
            );
            // ---------------------------------------
        }

        if ($this->_order->getReserve()->isPlaced()) {
            // ---------------------------------------
            $url = $this->getUrl('*/adminhtml_order/reservationCancel', array('ids' => $this->_order->getId()));
            $this->_addButton(
                'reservation_cancel', array(
                    'label'   => Mage::helper('M2ePro')->__('Cancel QTY Reserve'),
                    'onclick' => "confirmSetLocation(M2ePro.translator.translate('Are you sure?'), '" . $url . "');",
                    'class'   => 'scalable'
                )
            );
            // ---------------------------------------
        } elseif ($this->_order->isReservable()) {
            // ---------------------------------------
            $url = $this->getUrl('*/adminhtml_order/reservationPlace', array('ids' => $this->_order->getId()));
            $this->_addButton(
                'reservation_place', array(
                    'label'   => Mage::helper('M2ePro')->__('Reserve QTY'),
                    'onclick' => "confirmSetLocation(M2ePro.translator.translate('Are you sure?'), '" . $url . "');",
                    'class'   => 'scalable'
                )
            );
            // ---------------------------------------
        }

        if ($this->_order->getMagentoOrderId() === null) {
            // ---------------------------------------
            $url = $this->getUrl('*/*/createMagentoOrder', array('id' => $this->_order->getId()));
            $this->_addButton(
                'order', array(
                    'label'   => Mage::helper('M2ePro')->__('Create Order'),
                    'onclick' => "setLocation('" . $url . "');",
                    'class'   => 'scalable'
                )
            );
            // ---------------------------------------
        } elseif ($this->_order->getMagentoOrder() === null || $this->_order->getMagentoOrder()->isCanceled()) {
            // ---------------------------------------
            $url = $this->getUrl('*/*/createMagentoOrder', array('id' => $this->_order->getId(), 'force' => 'yes'));
            $confirm = Mage::helper('M2ePro')->escapeJs(
                Mage::helper('M2ePro')->__('Are you sure that you want to create new Magento Order?')
            );

            $this->_addButton(
                'order', array(
                    'label'   => Mage::helper('M2ePro')->__('Create Order'),
                    'onclick' => "confirmSetLocation('" . $confirm . "','" . $url . "');",
                    'class'   => 'scalable'
                )
            );
            // ---------------------------------------
        }
    }

    //########################################
}
