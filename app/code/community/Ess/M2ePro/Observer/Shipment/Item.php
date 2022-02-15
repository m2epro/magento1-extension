<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Shipment_Item extends Ess_M2ePro_Observer_Shipment_Abstract
{
    //########################################

    public function process()
    {
        if (Mage::helper('M2ePro/Data_Global')->getValue('skip_shipment_observer')) {
            Mage::helper('M2ePro/Data_Global')->unsetValue('skip_shipment_observer');
            return;
        }

        /** @var $shipmentItem Mage_Sales_Model_Order_Shipment_Item */
        $shipmentItem = $this->getEvent()->getShipmentItem();
        $shipment = $this->getShipment($shipmentItem);

        if (!$shipment) {
            $class = get_class($this);
            Mage::helper('M2ePro/Module_Logger')->process(
                array(),
                "M2ePro observer $class cannot get shipment data from event or database",
                false
            );

            return;
        }

        /**
         * We can catch two the same events: save of Mage_Sales_Model_Order_Shipment_Item and
         * Mage_Sales_Model_Order_Shipment_Track. So we must skip a duplicated one.
         */
        $objectId = spl_object_hash($shipment->getTracksCollection()->getLastItem());
        $eventKey = 'skip_' . $shipment->getId() .'##'. $objectId;
        if (!Mage::helper('M2ePro/Data_Global')->getValue($eventKey)) {
            Mage::helper('M2ePro/Data_Global')->setValue($eventKey, true);
        }

        try {
            /** @var $order Ess_M2ePro_Model_Order */
            $order = Mage::getModel('M2ePro/Order')->load($shipment->getOrderId(), 'magento_order_id');
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
        $handler->handleItem($order, $shipmentItem);
    }

    //########################################
}
