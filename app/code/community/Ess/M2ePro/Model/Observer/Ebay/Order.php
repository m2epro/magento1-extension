<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Ebay_Order extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Mage_Sales_Model_Order $magentoOrder */
        $magentoOrder = $this->getEvent()->getOrder();

        $origData = $magentoOrder->getOrigData();
        if (empty($origData)) {
            return;
        }

        if ($origData['status'] == $magentoOrder->getStatus() && $origData['state'] == $magentoOrder->getState()) {
            return;
        }

        try {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Ebay')->getObject(
                'Order',
                $magentoOrder->getId(),
                'magento_order_id'
            );
        } catch (Exception $exception) {
            return;
        }

        /** @var Ess_M2ePro_Model_Ebay_Order $ebayOrder */
        $ebayOrder = $order->getChildObject();

        $ebayAccount = $ebayOrder->getEbayAccount();

        if (!$ebayAccount->isPickupStoreEnabled() || !$ebayAccount->isMagentoOrdersInStorePickupEnabled()) {
            return;
        }

        if ($magentoOrder->getState() == Mage_Sales_Model_Order::STATE_CANCELED &&
            $this->sendNotification($order->getAccount(), 'cancelled', $ebayOrder->getEbayOrderId())
        ) {
            $order->addSuccessLog(Mage::helper('M2ePro')->__('Order was successfully marked as Cancelled'));
            return;
        }

        $readyForPickupStatus = $ebayAccount->getMagentoOrdersInStorePickupStatusReadyForPickup();
        if ($readyForPickupStatus == $magentoOrder->getStatus() &&
            $this->sendNotification($order->getAccount(), 'ready_for_pickup', $ebayOrder->getEbayOrderId())
        ) {
            $order->addSuccessLog(Mage::helper('M2ePro')->__('Order was successfully marked as Ready For Pickup'));
        }

        $pickedUpStatus = $ebayAccount->getMagentoOrdersInStorePickupStatusPickedUp();
        if ($pickedUpStatus == $magentoOrder->getStatus() &&
            $this->sendNotification($order->getAccount(), 'picked_up', $ebayOrder->getEbayOrderId())
        ) {
            $order->addSuccessLog(Mage::helper('M2ePro')->__('Order was successfully marked as Picked Up'));
        }
    }

    //########################################

    private function sendNotification($account, $orderId, $type)
    {
        $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connector = $dispatcher->getVirtualConnector(
            'store', 'update', 'order',
            array('order_id' => $orderId, 'type' => $type),
            NULL, NULL, $account
        );

        try {
            $dispatcher->process($connector);
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }

    //########################################
}