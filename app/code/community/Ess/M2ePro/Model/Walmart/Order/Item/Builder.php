<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Order_Item_Builder extends Mage_Core_Model_Abstract
{
    /** @var bool */
    protected $_previousBuyerCancellationRequested;
    /** @var int */
    private $walmartOrderItemId;
    /** @var array */
    private $mergedWalmartOrderItemIds = array();

    public function initialize(array $data)
    {
        // Init general data
        // ---------------------------------------
        $this->walmartOrderItemId = (int)$data['walmart_order_item_id'];
        $this->setData('walmart_order_item_id', $this->walmartOrderItemId);
        $this->setData('status', $data['status']);
        $this->setData('order_id', $data['order_id']);
        $this->setData('sku', trim($data['sku']));
        $this->setData('title', trim($data['title']));
        $this->setData('buyer_cancellation_requested', $data['buyer_cancellation_requested']);
        // ---------------------------------------

        // Init sale data
        // ---------------------------------------
        $this->setData('price', (float)$data['price']);
        $this->setData('qty_purchased', (int)$data['qty']);
        // ---------------------------------------

        // ----------------------------------------
        if (!empty($data['shipping_details']['tracking_details']['number'])) {
            $this->setData('tracking_details', Mage::helper('M2ePro')->jsonEncode(array(
                'number' => $data['shipping_details']['tracking_details']['number'],
                'title' => $data['shipping_details']['tracking_details']['carrier'],
            )));
        }
        // ----------------------------------------

        /**
         * Walmart returns the same Order Item more than one time with single QTY. We will merge this data
         */
        // ---------------------------------------
        if (!empty($data['merged_walmart_order_item_ids'])) {
            $this->mergedWalmartOrderItemIds = $data['merged_walmart_order_item_ids'];
            $this->setData(
                'merged_walmart_order_item_ids',
                Mage::helper('M2ePro')->jsonEncode($data['merged_walmart_order_item_ids'])
            );
        }

        // ---------------------------------------
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order_Item
     */
    public function process()
    {
        /** @var Ess_M2ePro_Model_Order_Item $existItem */
        $existItem = Mage::helper('M2ePro/Component_Walmart')->getCollection('Order_Item')
            ->addFieldToFilter('sku', $this->getData('sku'))
            ->addFieldToFilter('order_id', $this->getData('order_id'))
            ->addFieldToFilter('walmart_order_item_id', $this->getAllWalmartOrderItemIds())
            ->getFirstItem();

        $this->_previousBuyerCancellationRequested = false;
        if ($existItem->getId()) {
            $this->_previousBuyerCancellationRequested = $existItem->getChildObject()->isBuyerCancellationRequested();
        }

        if ($existItem->getId() !== null
            && $existItem->getChildObject()->getWalmartOrderItemId() !== $this->walmartOrderItemId
        ) {
            $this->setData('walmart_order_item_id', $existItem->getChildObject()->getWalmartOrderItemId());
            $this->setData(
                'merged_walmart_order_item_ids',
                $existItem->getChildObject()->getData('merged_walmart_order_item_ids')
            );
        }

        foreach ($this->getData() as $key => $value) {
            if (!$existItem->getId() || ($existItem->hasData($key) && $existItem->getData($key) != $value)) {
                $existItem->addData($this->getData());
                $existItem->save();
                break;
            }
        }

        return $existItem;
    }

    /**
     * @return bool
     */
    public function getPreviousBuyerCancellationRequested()
    {
        return $this->_previousBuyerCancellationRequested;
    }

    private function getAllWalmartOrderItemIds()
    {
        if ($this->mergedWalmartOrderItemIds === array()) {
            return array($this->walmartOrderItemId);
        }

        $allIds = $this->mergedWalmartOrderItemIds;
        $allIds[] = $this->walmartOrderItemId;

        return array_map('intval', $allIds);
    }
}
