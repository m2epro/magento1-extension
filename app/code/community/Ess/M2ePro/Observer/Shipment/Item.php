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
            return;
        }

        /** @var $shipmentItem Mage_Sales_Model_Order_Shipment_Item */
        $shipmentItem = $this->getEvent()->getShipmentItem();
        $shipment = $this->getShipment($shipmentItem);

        if (!$shipment) {
            $class = get_class($this);
            Mage::helper('M2ePro/Module_Logger')->process(
                array(),
                "M2ePro observer $class cannot get shipment data from event or database"
            );

            return;
        }

        /**
         * Due to task m2e-team/m2e-pro/backlog#3421 this event observer can be called two times.
         * If first time was successful, second time will be skipped.
         * "Successful" means "$shipment variable is not null".
         * There is code that looks same below, but event keys and logic are different.
         */
        $eventKey = 'skip_shipment_item_' . $shipmentItem->getId();
        if (Mage::helper('M2ePro/Data_Global')->getValue($eventKey)) {
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue($eventKey, true);

        /**
         * We can catch two the same events: save of Mage_Sales_Model_Order_Shipment_Item and
         * Mage_Sales_Model_Order_Shipment_Track. So we must skip a duplicated one.
         * Possible situations:
         * 1. Shipment without tracks was created for Magento order. Only 'Item' observer will be called.
         * 2. Shipment with track(s) was created for Magento order. Both 'Item' and 'Track' observers will be called.
         * 3. New track(s) was added for existing shipment. Only 'Track' observer will be called.
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
