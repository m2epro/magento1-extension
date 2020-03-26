<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_CreditMemo_Handler extends Ess_M2ePro_Model_Order_CreditMemo_Handler
{
    //########################################

    /**
     * @return string
     */
    public function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Amazon::NICK;
    }

    //########################################

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
                if ($item === null) {
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

                if ($price > $item->getChildObject()->getPrice()) {
                    $price = $item->getChildObject()->getPrice();
                }

                if ($tax > $item->getChildObject()->getTaxAmount()) {
                    $tax = $item->getChildObject()->getTaxAmount();
                }

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

            $creditmemoItem->getOrderItem()->setAdditionalData(
                Mage::helper('M2ePro')->serialize($additionalData)
            );
            $creditmemoItem->getOrderItem()->save();
        }

        return $itemsForCancel;
    }

    //########################################
}
