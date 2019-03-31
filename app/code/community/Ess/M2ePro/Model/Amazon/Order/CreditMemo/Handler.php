<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_CreditMemo_Handler extends Ess_M2ePro_Model_Order_CreditMemo_Handler
{
    //########################################

    public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        if (!$order->isComponentModeAmazon()) {
            throw new InvalidArgumentException('Invalid component mode.');
        }

        if (!$order->getChildObject()->canRefund()) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        $items = $this->getItemsToRefund($order, $creditmemo);
        return $order->getChildObject()->refund($items) ? self::HANDLE_RESULT_SUCCEEDED : self::HANDLE_RESULT_FAILED;
    }

    //########################################

    private function getItemsToRefund(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $itemsForCancel = array();

        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            /** @var Mage_Sales_Model_Order_Creditmemo_Item $creditmemoItem */

            $additionalData = $creditmemoItem->getOrderItem()->getAdditionalData();
            $additionalData = is_string($additionalData) ? @unserialize($additionalData) : array();

            if (!isset($additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items']) ||
                !is_array($additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'])) {
                continue;
            }

            $qtyAvailable = (int)$creditmemoItem->getQty();

            foreach ($additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'] as &$data) {
                if ($qtyAvailable <= 0 || !isset($data['order_item_id'])) {
                    continue;
                }

                $orderItemId = $data['order_item_id'];
                if (in_array($orderItemId, $itemsForCancel)) {
                    continue;
                }

                /** @var Ess_M2ePro_Model_Order_Item $item */
                $item = $order->getItemsCollection()->getItemByColumnValue('amazon_order_item_id', $orderItemId);
                if (is_null($item)) {
                    continue;
                }

                /*
                 * Extension stores Refunded QTY for each item starting from v6.5.4
                */
                $itemQtyRef = isset($data['refunded_qty'][$orderItemId]) ? $data['refunded_qty'][$orderItemId] : 0;
                $itemQty = $item->getChildObject()->getQtyPurchased();

                if ($itemQtyRef >= $itemQty) {
                    continue;
                }

                if ($itemQty > $qtyAvailable) {
                    $itemQty = $qtyAvailable;
                }

                $price = $creditmemoItem->getPriceInclTax();
                $tax   = $creditmemoItem->getTaxAmount();

                $itemsForCancel[] = array(
                    'item_id'  => $orderItemId,
                    'qty'      => $itemQty,
                    'prices'   => array(
                        'product' => $price,
                    ),
                    'taxes'    => array(
                        'product' => $tax,
                    ),
                );

                $qtyAvailable -= $itemQty;
                $data['refunded_qty'][$orderItemId] = $itemQty;
            }
            unset($data);

            $creditmemoItem->getOrderItem()->setAdditionalData(serialize($additionalData));
            $creditmemoItem->getOrderItem()->save();
        }

        return $itemsForCancel;
    }

    //########################################
}