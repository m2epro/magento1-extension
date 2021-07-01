<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Shipment_Track extends Ess_M2ePro_Observer_Abstract
{
    //########################################

    public function process()
    {
        if (Mage::helper('M2ePro/Data_Global')->getValue('skip_shipment_observer')) {
            return;
        }

        /** @var $track Mage_Sales_Model_Order_Shipment_Track */
        $track = $this->getEvent()->getTrack();

        $shipment = $track->getShipment();
        $magentoOrderId = $shipment->getOrderId();

        /**
         * We can catch two the same events: save of Mage_Sales_Model_Order_Shipment_Item and
         * Mage_Sales_Model_Order_Shipment_Track. So we must skip a duplicated one.
         */
        $eventKey = 'skip_' . $shipment->getId() .'##'. spl_object_hash($track);
        if (Mage::helper('M2ePro/Data_Global')->getValue($eventKey)) {
            Mage::helper('M2ePro/Data_Global')->unsetValue($eventKey);
            return;
        }

        try {
            /** @var $order Ess_M2ePro_Model_Order */
            $order = Mage::getModel('M2ePro/Order')->load($magentoOrderId, 'magento_order_id');
        } catch (Exception $e) {
            return;
        }

        if ($order->isEmpty()) {
            return;
        }

        if (!in_array($order->getComponentMode(), Mage::helper('M2ePro/Component')->getEnabledComponents())) {
            return;
        }

        $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

        /** @var Ess_M2ePro_Model_Order_Shipment_Handler $handler */
        $componentMode = ucfirst($order->getComponentMode());
        $handler = Mage::getModel("M2ePro/{$componentMode}_Order_Shipment_Handler");
        $handler->handle($order, $shipment);
    }

    //########################################
}
