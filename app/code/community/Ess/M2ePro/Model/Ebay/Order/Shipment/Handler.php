<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_Shipment_Handler extends Ess_M2ePro_Model_Order_Shipment_Handler
{
    //########################################

    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Ebay::NICK;
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

        $allowedItems = array();
        $items = array();
        foreach ($shipment->getAllItems() as $shipmentItem) {
            /** @var Mage_Sales_Model_Order_Shipment_Item $shipmentItem */
            $orderItem = $shipmentItem->getOrderItem();

            if ($orderItem->getParentItemId() !== null ) {
                continue;
            }

            $allowedItems[] = $orderItem->getId();

            $item = $this->getItemToShipLoader($order, $shipmentItem)->loadItem();
            if (empty($item)) {
                continue;
            }

            $items += $item;
        }

        $resultItems = array();
        foreach ($items as $orderItemId => $item) {
            if (!in_array($orderItemId, $allowedItems)) {
                continue;
            }

            $resultItems[] = $item;
        }

        return $this->processStatusUpdates($order, $trackingDetails, $resultItems)
            ? self::HANDLE_RESULT_SUCCEEDED
            : self::HANDLE_RESULT_FAILED;
    }

    //########################################
}
