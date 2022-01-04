<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Data as Helper;

abstract class Ess_M2ePro_Model_Order_Shipment_Handler
{
    const HANDLE_RESULT_FAILED    = -1;
    const HANDLE_RESULT_SKIPPED   = 0;
    const HANDLE_RESULT_SUCCEEDED = 1;

    const CUSTOM_CARRIER_CODE = 'custom';

    //########################################

    /**
     * @return string
     */
    abstract protected function getComponentMode();

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment_Item $shipmentItem
     *
     * @return Ess_M2ePro_Model_Order_Shipment_ItemToShipLoaderInterface
     */
    protected function getItemToShipLoader(
        Ess_M2ePro_Model_Order $order,
        Mage_Sales_Model_Order_Shipment_Item $shipmentItem
    ) {
        $additionalData = Mage::helper('M2ePro')->unserialize($shipmentItem->getOrderItem()->getAdditionalData());
        $data = isset($additionalData[Helper::CUSTOM_IDENTIFIER])
            ? $additionalData[Helper::CUSTOM_IDENTIFIER]
            : array();

        $componentMode = ucfirst($this->getComponentMode());
        if (isset($data['pretended_to_be_simple']) && $data['pretended_to_be_simple'] === true) {
            return Mage::getModel(
                "M2ePro/{$componentMode}_Order_Shipment_ItemToShipLoader_PretendedToBeSimple",
                array($order, $shipmentItem)
            );
        }

        return Mage::getModel(
            "M2ePro/{$componentMode}_Order_Shipment_ItemToShipLoader_Default",
            array($order, $shipmentItem)
        );
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return int
     * @throws Exception
     */
    public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $trackingDetails = $this->getTrackingDetails($order, $shipment);
        if (!$this->isNeedToHandle($order, $trackingDetails)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        $items = array();
        foreach ($shipment->getAllItems() as $shipmentItem) {
            /** @var Mage_Sales_Model_Order_Shipment_Item $shipmentItem */
            $items = array_merge(
                $items,
                $this->getItemToShipLoader($order, $shipmentItem)->loadItem()
            );
        }

        return $this->processStatusUpdates($order, $trackingDetails, $items)
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

        $items = $this->getItemToShipLoader($order, $shipmentItem)->loadItem();
        return $this->processStatusUpdates($order, $trackingDetails, $items)
            ? self::HANDLE_RESULT_SUCCEEDED
            : self::HANDLE_RESULT_FAILED;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param array $trackingDetails
     * @param array $items
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processStatusUpdates(Ess_M2ePro_Model_Order $order, array $trackingDetails, array $items)
    {
        if (empty($items)) {
            return false;
        }

        return $order->getChildObject()->updateShippingStatus($trackingDetails, $items);
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    protected function getTrackingDetails(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $track = $shipment->getTracksCollection()->getLastItem();
        $number = (string)trim($track->getData('number'));
        if (empty($number)) {
            return array();
        }

        $carrierCode = $carrierTitle = trim($track->getData('carrier_code'));
        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($carrierCode, $order->getStoreId());
        $carrier && $carrierTitle = $carrier->getConfigData('title');

        return array(
            'carrier_code'    => $carrierCode,
            'carrier_title'   => $carrierTitle,
            'shipping_method' => trim($track->getData('title')),
            'tracking_number' => $number
        );
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
