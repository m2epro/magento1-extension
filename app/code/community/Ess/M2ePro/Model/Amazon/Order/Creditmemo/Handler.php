<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Creditmemo_Handler extends Ess_M2ePro_Model_Order_Creditmemo_Handler
{
    const AMAZON_REFUND_REASON_CUSTOMER_RETURN = 'CustomerReturn';
    const AMAZON_REFUND_REASON_NO_INVENTORY    = 'NoInventory';
    const AMAZON_REFUND_REASON_BUYER_CANCELED  = 'BuyerCanceled';

    //########################################

    /**
     * @return string
     */
    public function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Amazon::NICK;
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
        $itemsToRefund = array();

        $refundReason = $this->isOrderStatusShipped($order) || $order->isStatusUpdatingToShipped() ?
                        self::AMAZON_REFUND_REASON_CUSTOMER_RETURN :
                        self::AMAZON_REFUND_REASON_NO_INVENTORY;

        /** @var Ess_M2ePro_Model_Order_Proxy $proxy */
        $proxy = $order->getProxy()->setStore($order->getStore());
        $shippingData = $proxy->getShippingData();

        $isTaxAddedToShippingCost = $proxy->isTaxModeNone() && $proxy->getShippingPriceTaxRate() > 0;

        $fullShippingCostRefunded = $creditmemo->getShippingAmount() > 0
            ? $shippingData['shipping_price'] === $creditmemo->getShippingAmount()
            : false;

        $fullShippingTaxRefunded = $creditmemo->getShippingTaxAmount() > 0 ?
            $order->getChildObject()->getShippingPriceTaxAmount() === $creditmemo->getShippingTaxAmount() :
            false;

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
                if (in_array($orderItemId, $itemsToRefund)) {
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

                $itemToRefund = array(
                    'item_id'  => $orderItemId,
                    'reason'   => $refundReason,
                    'qty'      => $itemQty,
                    'prices'   => array(
                        'product' => $price,
                    ),
                    'taxes'    => array(
                        'product' => $tax,
                    ),
                );

                if ($fullShippingCostRefunded) {
                    $itemToRefund['prices']['shipping'] = $item->getChildObject()->getShippingPrice();
                }

                if ($fullShippingTaxRefunded || ($fullShippingCostRefunded && $isTaxAddedToShippingCost)) {
                    $itemToRefund['taxes']['shipping'] = $item->getChildObject()->getShippingTaxAmount();
                }

                $itemsToRefund[] = $itemToRefund;

                $qtyAvailable -= $itemQty;
                $data['refunded_qty'][$orderItemId] = $itemQty;
            }

            unset($data);

            $creditmemoItem->getOrderItem()->setAdditionalData(
                Mage::helper('M2ePro')->serialize($additionalData)
            );

            // @codingStandardsIgnoreLine
            $creditmemoItem->getOrderItem()->save();
        }

        return $itemsToRefund;
    }

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function isOrderStatusShipped(Ess_M2ePro_Model_Order $order)
    {
        return $order->getChildObject()->isShipped() || $order->getChildObject()->isPartiallyShipped();
    }

    //########################################
}
