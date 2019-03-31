<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_CreditMemo_View extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Mage_Sales_Model_Order_Creditmemo $creditMemo */
        $creditMemo = Mage::registry('current_creditmemo');
        if (empty($creditMemo) || !$creditMemo->getId()) {
            return;
        }

        try {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Ebay')->getObject(
                'Order', $creditMemo->getOrderId(), 'magento_order_id'
            );
        } catch (Exception $exception) {
            return;
        }

        if (empty($order) || !$order->getId()) {
            return;
        }

        $customerId = $creditMemo->getOrder()->getCustomerId();
        if (empty($customerId) || $creditMemo->getOrder()->getCustomerIsGuest()) {
            return;
        }

        $customer = Mage::getModel('customer/customer')->load($customerId);

        $creditMemo->getOrder()->setData(
            'customer_'.Ess_M2ePro_Model_Ebay_Order_Proxy::USER_ID_ATTRIBUTE_CODE,
            $customer->getData(Ess_M2ePro_Model_Ebay_Order_Proxy::USER_ID_ATTRIBUTE_CODE)
        );
    }

    //########################################
}