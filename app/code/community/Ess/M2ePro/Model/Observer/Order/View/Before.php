<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Order_View_Before extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Mage_Sales_Model_Order $magentoOrder */
        $magentoOrder = Mage::registry('current_order');
        if (empty($magentoOrder) || !$magentoOrder->getId()) {
            return;
        }

        try {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Ebay')->getObject(
                'Order', $magentoOrder->getId(), 'magento_order_id'
            );
        } catch (Exception $exception) {
            return;
        }

        if (empty($order) || !$order->getId()) {
            return;
        }

        $customerId = $magentoOrder->getCustomerId();
        if (empty($customerId) || $magentoOrder->getCustomerIsGuest()) {
            return;
        }

        $customer = Mage::getModel('customer/customer')->load($customerId);

        $magentoOrder->setData(
            'customer_'.Ess_M2ePro_Model_Ebay_Order_Proxy::USER_ID_ATTRIBUTE_CODE,
            $customer->getData(Ess_M2ePro_Model_Ebay_Order_Proxy::USER_ID_ATTRIBUTE_CODE)
        );
    }

    //########################################
}