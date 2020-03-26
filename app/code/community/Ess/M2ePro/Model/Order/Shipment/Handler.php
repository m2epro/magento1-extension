<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Handles shipments, created by seller in admin panel
 */
abstract class Ess_M2ePro_Model_Order_Shipment_Handler
{
    const HANDLE_RESULT_FAILED    = -1;
    const HANDLE_RESULT_SKIPPED   = 0;
    const HANDLE_RESULT_SUCCEEDED = 1;

    const CUSTOM_CARRIER_CODE = 'custom';

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $trackingDetails = $this->getTrackingDetails($order, $shipment);
        if (!$this->isNeedToHandle($order, $trackingDetails)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        return $this->processStatusUpdates($order, $trackingDetails, $this->getItemsToShip($order, $shipment))
            ? self::HANDLE_RESULT_SUCCEEDED
            : self::HANDLE_RESULT_FAILED;
    }

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment_Item $shipmentItem
     * @return int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function handleItem(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment_Item $shipmentItem)
    {
        $trackingDetails = $this->getTrackingDetails($order, $shipmentItem->getShipment());
        if (!$this->isNeedToHandle($order, $trackingDetails)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        $items = $this->getItemsToShipForShipmentItem($order, $shipmentItem);
        return $this->processStatusUpdates($order, $trackingDetails, $items)
            ? self::HANDLE_RESULT_SUCCEEDED
            : self::HANDLE_RESULT_FAILED;
    }

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getItemsToShip(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $itemsToShip = array();

        foreach ($shipment->getAllItems() as $shipmentItem) {
            /** @var Mage_Sales_Model_Order_Shipment_Item $shipmentItem */
            $itemsToShip = array_merge($itemsToShip, $this->getItemsToShipForShipmentItem($order, $shipmentItem));
        }

        return $itemsToShip;
    }

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param array $trackingDetails
     * @param array $items
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processStatusUpdates(Ess_M2ePro_Model_Order $order, array $trackingDetails, array $items)
    {
        return $order->getChildObject()->updateShippingStatus($trackingDetails, $items);
    }

    abstract protected function getComponentMode();
    abstract protected function getItemsToShipForShipmentItem(
        Ess_M2ePro_Model_Order $order,
        Mage_Sales_Model_Order_Shipment_Item $shipmentItem
    );

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    protected function getTrackingDetails(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $track = $shipment->getTracksCollection()->getLastItem();
        $trackingDetails = array();

        $number = trim($track->getData('number'));

        if (!empty($number)) {
            $carrierCode = $carrierTitle = trim($track->getData('carrier_code'));

            $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($carrierCode, $order->getStoreId());
            $carrier && $carrierTitle = $carrier->getConfigData('title');

            $trackingDetails = array(
                'carrier_code'    => $carrierCode,
                'carrier_title'   => $carrierTitle,
                'shipping_method' => trim($track->getData('title')),
                'tracking_number' => (string)$number
            );
        }

        return $trackingDetails;
    }

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param array $trackingDetails
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function isNeedToHandle(Ess_M2ePro_Model_Order $order, array $trackingDetails)
    {
        if ($order->getComponentMode() !== $this->getComponentMode()) {
            throw new InvalidArgumentException('Invalid component mode.');
        }

        return $order->getChildObject()->canUpdateShippingStatus($trackingDetails);
    }

    //########################################
}