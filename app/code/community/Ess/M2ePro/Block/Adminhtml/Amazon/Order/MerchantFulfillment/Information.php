<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_MerchantFulfillment_Information
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderMerchantFulfillmentInformation');
        // ---------------------------------------
        $this->setTemplate('M2ePro/amazon/order/merchant_fulfillment/information.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        if(is_null($this->getData('fulfillment_not_wizard'))) {
            $breadcrumb = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_amazon_order_merchantFulfillment_breadcrumb');
            $breadcrumb->setData('step', 3);
            $this->setChild('breadcrumb', $breadcrumb);
        }
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'class'   => 'delete',
            'label'   => Mage::helper('M2ePro')->__('Cancel'),
            'onclick' => "OrderMerchantFulfillmentHandlerObj.cancelShippingOfferAction()",
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('cancel_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------]
        $data = array(
            'class'   => 'go',
            'label'   => Mage::helper('M2ePro')->__('Print'),
            'onclick' => 'OrderMerchantFulfillmentHandlerObj.getShippingLabelAction()',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('print_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => "OrderMerchantFulfillmentHandlerObj.refreshDataAction()",
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('refresh_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Use Amazon\'s Shipping Services'),
            'onclick' => "OrderMerchantFulfillmentHandlerObj.resetDataAction()",
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('try_again_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'class'   => 'close',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => "OrderMerchantFulfillmentHandlerObj.closePopUp()",
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('close_button', $buttonBlock);
        // ---------------------------------------
    }

    //########################################

    public function getOrderItems()
    {
        $data = array();

        foreach ($this->getData('order_items') as $parentOrderItem) {
            /**
             * @var $parentOrderItem Ess_M2ePro_Model_Order_Item
             */
            $orderItem = $parentOrderItem->getChildObject();

            $data[] = array(
                'title'    => $orderItem->getTitle(),
                'sku'      => $orderItem->getSku(),
                'asin'     => $orderItem->getGeneralId(),
                'qty'      => $orderItem->getQtyPurchased(),
                'price'    => $orderItem->getPrice(),
                'currency' => $orderItem->getCurrency(),
            );
        }

        return $data;
    }

    //########################################
}