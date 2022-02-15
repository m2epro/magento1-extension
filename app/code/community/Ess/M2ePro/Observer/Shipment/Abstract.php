<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Observer_Shipment_Abstract extends Ess_M2ePro_Observer_Abstract
{
    /**
     * @param Mage_Sales_Model_Order_Shipment_Item|Mage_Sales_Model_Order_Shipment_Track $source
     * @return Mage_Sales_Model_Order_Shipment|null
     */
    protected function getShipment($source)
    {
        $shipment = $source->getShipment();
        if ($shipment != null && $shipment->getId()) {
            return $shipment;
        }

        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->addFieldToFilter('entity_id', $source->getParentId());

        /** @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $shipmentCollection->getLastItem();
        if ($shipment != null && $shipment->getId()) {
            return $shipment;
        }

        return null;
    }
}