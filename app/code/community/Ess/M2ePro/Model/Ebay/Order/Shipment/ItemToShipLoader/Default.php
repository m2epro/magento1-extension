<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Data as Helper;

class Ess_M2ePro_Model_Ebay_Order_Shipment_ItemToShipLoader_Default
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
        if (!$this->validate($additionalData)) {
            return array();
        }

        return array($this->shipmentItem->getOrderItem()->getId() => $this->getOrderItem($additionalData));
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

        if (!isset($additionalData[Helper::CUSTOM_IDENTIFIER]['items'][0]['item_id'])) {
            return false;
        }

        if (!isset($additionalData[Helper::CUSTOM_IDENTIFIER]['items'][0]['transaction_id'])) {
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
        if ($this->orderItem !== null && $this->orderItem->getId()) {
            return $this->orderItem;
        }

        $this->orderItem = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order_Item')
            ->addFieldToFilter('order_id', $this->order->getId())
            ->addFieldToFilter('item_id', $additionalData[Helper::CUSTOM_IDENTIFIER]['items'][0]['item_id'])
            ->addFieldToFilter(
                'transaction_id',
                $additionalData[Helper::CUSTOM_IDENTIFIER]['items'][0]['transaction_id']
            )
            ->getFirstItem();

        return $this->orderItem;
    }

    //########################################
}
