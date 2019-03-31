<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_MerchantFulfillment_ShippingServices
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderMerchantFulfillmentShippingServices');
        // ---------------------------------------
        $this->setTemplate('M2ePro/amazon/order/merchant_fulfillment/shipping_services.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $breadcrumb = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_order_merchantFulfillment_breadcrumb');
        $breadcrumb->setData('step', 2);
        $this->setChild('breadcrumb', $breadcrumb);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'fulfillment_save_shipping_services',
            'class'   => 'next',
            'disabled'=> 'disabled',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => "OrderMerchantFulfillmentHandlerObj.createShippingOfferAction()",
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'class'   => 'back',
            'label'   => Mage::helper('M2ePro')->__('Back'),
            'onclick' => "OrderMerchantFulfillmentHandlerObj.getPopupAction()",
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('back_button', $buttonBlock);
        // ---------------------------------------
    }

    //########################################
}