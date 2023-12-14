<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Data as Helper;

class Ess_M2ePro_Model_Walmart_Order_Shipment_ItemToShipLoader_PretendedToBeSimple
    extends Ess_M2ePro_Model_Walmart_Order_Shipment_ItemToShipLoader_Default
{
    //########################################

    /**
     * @return array
     * @throws Exception
     */
    public function loadItem()
    {
        $additionalData = Mage::helper('M2ePro')->unserialize($this->shipmentItem->getOrderItem()->getAdditionalData());
        if ($cache = $this->getAlreadyProcessed($additionalData)) {
            return $cache;
        }

        if (!$this->validate($additionalData)) {
            return array();
        }

        $orderItem = $this->getOrderItem($additionalData);
        $qtyAvailable = (int)$this->shipmentItem->getQty();

        $shippingInfo = array();
        $orderItemAdditionalData = $orderItem->getAdditionalData();
        if (isset($orderItemAdditionalData['shipping_info'])) {
            $shippingInfo = $orderItemAdditionalData['shipping_info'];
        }

        $shipmentItemId = $this->shipmentItem->getId();
        $productId = $this->shipmentItem->getProductId();
        if (!isset($shippingInfo['items'][$productId]['shipped'][$shipmentItemId])) {
            $shippingInfo['items'][$productId]['shipped'][$shipmentItemId] = $qtyAvailable;
            $orderItemAdditionalData['shipping_info'] = $shippingInfo;
            $orderItem->setSettings('additional_data', $orderItemAdditionalData);
            $orderItem->save();
        }

        foreach ($shippingInfo['items'] as $productId => $data) {
            $totalQtyShipped = 0;
            foreach ($data['shipped'] as $shipmentItemId => $itemQtyShipped) {
                $totalQtyShipped += $itemQtyShipped;
            }

            if ($totalQtyShipped < $data['total']) {
                $additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$this->shipmentItem->getId()] = array();
                $this->saveAdditionalDataInShipmentItem($additionalData);

                return array();
            }
        }

        /**
         * - Walmart returns the same Order Item more than one time with single QTY. That data was merged.
         * - Walmart Order Item QTY is always equals 1.
         */
        $items = array();
        $orderItemIds = array_merge(
            array($orderItem->getChildObject()->getWalmartOrderItemId()),
            $orderItem->getChildObject()->getMergedWalmartOrderItemIds()
        );

        $qtyPurchased = $orderItem->getChildObject()->getQtyPurchased();
        if ($qtyPurchased === 0 || count($orderItemIds) === 0) {
            return array();
        }

        $itemQty = $qtyPurchased / count($orderItemIds);

        foreach ($orderItemIds as $orderItemId) {
            $items[] = array(
                'walmart_order_item_id' => $orderItemId,
                'qty'                   => $itemQty
            );
        }

        $additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$this->shipmentItem->getId()] = $items;
        $this->saveAdditionalDataInShipmentItem($additionalData);

        return $items;
    }

    //########################################
}
