<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Invoice_View extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = Mage::registry('current_invoice');
        if (empty($invoice) || !$invoice->getId()) {
            return;
        }

        try {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Ebay')->getObject(
                'Order', $invoice->getOrderId(), 'magento_order_id'
            );
        } catch (Exception $exception) {
            return;
        }

        if (empty($order) || !$order->getId()) {
            return;
        }

        $customerId = $invoice->getOrder()->getCustomerId();
        if (empty($customerId) || $invoice->getOrder()->getCustomerIsGuest()) {
            return;
        }

        $customer = Mage::getModel('customer/customer')->load($customerId);

        $invoice->getOrder()->setData(
            'customer_'.Ess_M2ePro_Model_Ebay_Order_Proxy::USER_ID_ATTRIBUTE_CODE,
            $customer->getData(Ess_M2ePro_Model_Ebay_Order_Proxy::USER_ID_ATTRIBUTE_CODE)
        );
    }

    //########################################
}