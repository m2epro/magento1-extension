<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Shipment_Handler extends Ess_M2ePro_Model_Order_Shipment_Handler
{
    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return int
     */
    public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        if (!$order->isComponentModeAmazon()) {
            throw new InvalidArgumentException('Invalid component mode.');
        }

        $trackingDetails = $this->getTrackingDetails($shipment);

        if (!$order->getChildObject()->canUpdateShippingStatus($trackingDetails)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        $items = $this->getItemsToShip($order, $shipment);

        $trackingDetails['fulfillment_date'] = $shipment->getCreatedAt();

        $order->getChildObject()->updateShippingStatus($trackingDetails, $items);

        return self::HANDLE_RESULT_SUCCEEDED;
    }

    /**
     * @param Ess_M2ePro_Model_Order          $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @throws LogicException
     *
     * @return array
     */
    private function getItemsToShip(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $shipmentItems = $shipment->getAllItems();
        $orderItemDataIdentifier = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER;

        $items = array();

        foreach ($shipmentItems as $shipmentItem) {
            $additionalData = $shipmentItem->getOrderItem()->getAdditionalData();
            $additionalData = is_string($additionalData) ? @unserialize($additionalData) : array();

            if (!isset($additionalData[$orderItemDataIdentifier]['items'])) {
                continue;
            }

            if (!is_array($additionalData[$orderItemDataIdentifier]['items'])) {
                continue;
            }

            $qtyAvailable = (int)$shipmentItem->getQty();

            foreach ($additionalData[$orderItemDataIdentifier]['items'] as $data) {
                if ($qtyAvailable <= 0) {
                    continue;
                }

                if (!isset($data['order_item_id'])) {
                    continue;
                }

                $item = $order->getItemsCollection()
                    ->getItemByColumnValue('amazon_order_item_id', $data['order_item_id']);

                if (is_null($item)) {
                    continue;
                }

                $qty = $item->getChildObject()->getQtyPurchased();

                if ($qty > $qtyAvailable) {
                    $qty = $qtyAvailable;
                }

                $items[] = array(
                    'qty' => $qty,
                    'amazon_order_item_id' => $data['order_item_id']
                );

                $qtyAvailable -= $qty;
            }
        }

        return $items;
    }

    //########################################
}
