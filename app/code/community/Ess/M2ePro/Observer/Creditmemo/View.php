<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Creditmemo_View extends Ess_M2ePro_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = Mage::registry('current_creditmemo');
        if (empty($creditmemo) || !$creditmemo->getId()) {
            return;
        }

        try {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Ebay')->getObject(
                'Order', $creditmemo->getOrderId(), 'magento_order_id'
            );
        } catch (Exception $exception) {
            return;
        }

        if (empty($order) || !$order->getId()) {
            return;
        }

        $customerId = $creditmemo->getOrder()->getCustomerId();
        if (empty($customerId) || $creditmemo->getOrder()->getCustomerIsGuest()) {
            return;
        }

        $customer = Mage::getModel('customer/customer')->load($customerId);

        $creditmemo->getOrder()->setData(
            'customer_'.Ess_M2ePro_Model_Ebay_Order_Proxy::USER_ID_ATTRIBUTE_CODE,
            $customer->getData(Ess_M2ePro_Model_Ebay_Order_Proxy::USER_ID_ATTRIBUTE_CODE)
        );
    }

    //########################################
}