<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Order_Creditmemo_Handler extends Ess_M2ePro_Model_Order_Creditmemo_Handler
{
    //########################################

    /**
     * @return string
     */
    public function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Walmart::NICK;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getItemsToRefund(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $itemsForCancel = array();

        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            /** @var Mage_Sales_Model_Order_Creditmemo_Item $creditmemoItem */

            $additionalData = Mage::helper('M2ePro')->unserialize(
                $creditmemoItem->getOrderItem()->getAdditionalData()
            );

            if (!isset($additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items']) ||
                !is_array($additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'])) {
                continue;
            }

            $qtyAvailable = (int)$creditmemoItem->getQty();

            $dataSize = count($additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items']);
            for ($i = 0; $i < $dataSize; $i++) {
                $data = $additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'][$i];
                if ($qtyAvailable <= 0 || !isset($data['order_item_id'])) {
                    continue;
                }

                $orderItemId = $data['order_item_id'];
                if (in_array($orderItemId, $itemsForCancel)) {
                    continue;
                }

                /** @var Ess_M2ePro_Model_Order_Item $item */
                $item = $order->getItemsCollection()->getItemByColumnValue('walmart_order_item_id', $orderItemId);
                if ($item === null) {
                    continue;
                }
                $qtyPurchased = $item->getChildObject()->getQtyPurchased();

                /**
                 * Walmart returns the same Order Item more than one time with single QTY. That data was merged
                 */
                $mergedOrderItems = $item->getChildObject()->getMergedWalmartOrderItemIds();
                $walmartOrderItemsCount = 1 + count($mergedOrderItems);
                while ($mergedOrderItemId = array_shift($mergedOrderItems)) {
                    if (!isset($data['refunded_qty'][$mergedOrderItemId])) {
                        $orderItemId = $mergedOrderItemId;
                        break;
                    }
                }

                /**
                 * - Extension stores Refunded QTY for each item starting from v6.5.4
                 * - Walmart Order Item QTY is always equals 1
                 */
                $itemQtyRef = isset($data['refunded_qty'][$orderItemId]) ? $data['refunded_qty'][$orderItemId] : 0;
                $itemQty = $qtyPurchased / $walmartOrderItemsCount;

                if ($itemQtyRef >= $itemQty) {
                    continue;
                }

                if ($itemQty > $qtyAvailable) {
                    $itemQty = $qtyAvailable;
                }

                $price = $creditmemoItem->getPriceInclTax();
                $tax   = $creditmemoItem->getTaxAmount();

                if ($price > $item->getChildObject()->getPrice()) {
                    $price = $item->getChildObject()->getPrice();
                }

                if ($tax > $item->getChildObject()->getTaxAmount()) {
                    $tax = $item->getChildObject()->getTaxAmount();
                }

                $entry = array(
                    'item_id'  => $orderItemId,
                    'qty'      => $itemQty,
                    'prices'   => array(
                        'product' => $price,
                    ),
                    'taxes'    => array(
                        'product' => $tax,
                    ),
                );

                if ($item->getChildObject()->isBuyerCancellationRequested()
                    && $item->getChildObject()->isBuyerCancellationPossible()
                ) {
                    $entry['is_buyer_cancellation'] = true;
                }

                $itemsForCancel[] = $entry;

                $qtyAvailable -= $itemQty;
                $data['refunded_qty'][$orderItemId] = $itemQty;

                $additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'][$i] = $data;
                $mergedOrderItemId && $i--;
            }

            $creditmemoItem->getOrderItem()->setAdditionalData(
                Mage::helper('M2ePro')->serialize($additionalData)
            );
            $creditmemoItem->getOrderItem()->save();
        }

        return $itemsForCancel;
    }

    //########################################
}
