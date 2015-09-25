<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Order_Item_Builder extends Mage_Core_Model_Abstract
{
    // ########################################

    public function initialize(array $data)
    {
        // Init general data
        // ------------------
        $this->setData('buy_order_item_id', $data['order_item_id']);
        $this->setData('order_id', $data['order_id']);
        $this->setData('sku', trim($data['reference_id']));
        $this->setData('general_id', trim($data['sku']));
        $this->setData('title', trim($data['title']));
        // ------------------

        // Init sale data
        // ------------------
        $this->setData('currency', 'USD');
        $this->setData('price', (float)$data['price']);
        $this->setData('tax_amount', (float)$data['tax_amount']);
        $this->setData('qty', (int)$data['qty']);
        $this->setData('qty_shipped', (int)$data['qty_shipped']);
        $this->setData('qty_cancelled', (int)$data['qty_cancelled']);
        // ------------------

        // Fees
        // ------------------
        $this->setData('product_owed', (float)$data['product_owed']);
        $this->setData('shipping_owed', (float)$data['shipping_owed']);
        $this->setData('commission', (float)$data['commission']);
        $this->setData('shipping_fee', (float)$data['shipping_fee']);
        $this->setData('per_item_fee', (float)$data['per_item_fee']);
        // ------------------
    }

    // ########################################

    public function process()
    {
        return $this->createOrderItem();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Order_Item
     */
    private function createOrderItem()
    {
        $existItem = Mage::helper('M2ePro/Component_Buy')
            ->getCollection('Order_Item')
            ->addFieldToFilter('buy_order_item_id', $this->getData('buy_order_item_id'))
            ->addFieldToFilter('order_id', $this->getData('order_id'))
            ->getFirstItem();

        $existItem->addData($this->getData());
        $existItem->save();

        return $existItem;
    }

    // ########################################
}