<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Data as Helper;

class Ess_M2ePro_Model_Walmart_Order_Shipment_ItemToShipLoader_Default
    implements Ess_M2ePro_Model_Order_Shipment_ItemToShipLoaderInterface
{
    /** @var Ess_M2ePro_Model_Order $order */
    protected $order;

    /** @var Mage_Sales_Model_Order_Shipment_Item $shipmentItem */
    protected $shipmentItem;

    /** @var Ess_M2ePro_Model_Order_Item $orderItem */
    protected $orderItem;

    //########################################

    public function __construct(array $params)
    {
        list($this->order, $this->shipmentItem) = $params;
    }

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
        $itemQtyPurchased = $orderItem->getChildObject()->getQtyPurchased();
        $qtyAvailable = (int)$this->shipmentItem->getQty();

        if ($itemQtyPurchased > $qtyAvailable) {
            $itemQtyPurchased = $qtyAvailable;
        }

        $orderItemAdditionalData = $orderItem->getAdditionalData();
        $orderItemIdsInShipped = isset($orderItemAdditionalData['order_item_ids_in_shipped']) ?
            $orderItemAdditionalData['order_item_ids_in_shipped'] : array();

        $orderItemIds = array_diff(array_merge(
            array($orderItem->getChildObject()->getWalmartOrderItemId()),
            $orderItem->getChildObject()->getMergedWalmartOrderItemIds()
        ), $orderItemIdsInShipped);

        /**
         * - Walmart returns the same Order Item more than one time with single QTY. That data was merged.
         * - Walmart Order Item QTY is always equals 1.
         */

        if ($itemQtyPurchased === 0 || count($orderItemIds) === 0) {
            return array();
        }

        $itemQty = $itemQtyPurchased / count($orderItemIds);

        $items = array();
        foreach ($orderItemIds as $orderItemId) {
            if ($itemQtyPurchased <= 0) {
                continue;
            }

            $items[$orderItemId] = array(
                'walmart_order_item_id' => $orderItemId,
                'qty'                   => $itemQty
            );

            $itemQtyPurchased--;
        }

        $orderItemIdsInShipped += array_keys($items);
        $orderItemAdditionalData['order_item_ids_in_shipped'] = $orderItemIdsInShipped;
        $orderItem->setSettings('additional_data', $orderItemAdditionalData);
        $orderItem->save();

        $additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$this->shipmentItem->getId()] = $items;
        $this->saveAdditionalDataInShipmentItem($additionalData);

        return $items;
    }

    //########################################

    /**
     * @param array $additionalData
     *
     * @return array|null
     */
    protected function getAlreadyProcessed(array $additionalData)
    {
        if (!isset($additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$this->shipmentItem->getId()])) {
            return null;
        }

        return $additionalData[Helper::CUSTOM_IDENTIFIER]['shipments'][$this->shipmentItem->getId()];
    }

    /**
     * @param array $additionalData
     *
     * @return bool
     */
    protected function validate(array $additionalData)
    {
        if (!isset($additionalData[Helper::CUSTOM_IDENTIFIER]['items']) ||
            !is_array($additionalData[Helper::CUSTOM_IDENTIFIER]['items'])) {
            return false;
        }

        if ($this->shipmentItem->getQty() <= 0) {
            return false;
        }

        if (!isset($additionalData[Helper::CUSTOM_IDENTIFIER]['items'][0]['order_item_id'])) {
            return false;
        }

        $orderItem = $this->getOrderItem($additionalData);
        if (!$orderItem->getId()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $additionalData
     *
     * @throws Exception
     */
    protected function saveAdditionalDataInShipmentItem(array $additionalData)
    {
        $this->shipmentItem->getOrderItem()->setAdditionalData(Mage::helper('M2ePro')->serialize($additionalData));
        $this->shipmentItem->getOrderItem()->save();
    }

    //########################################

    /**
     * @param array $additionalData
     * @return Ess_M2ePro_Model_Order_Item
     */
    protected function getOrderItem(array $additionalData)
    {
        if ($this->orderItem !== null) {
            return $this->orderItem;
        }

        $this->orderItem = Mage::helper('M2ePro/Component_Walmart')->getCollection('Order_Item')
            ->addFieldToFilter('order_id', $this->order->getId())
            ->addFieldToFilter(
                'walmart_order_item_id',
                $additionalData[Helper::CUSTOM_IDENTIFIER]['items'][0]['order_item_id']
            )
            ->getFirstItem();

        return $this->orderItem;
    }

    //########################################
}
