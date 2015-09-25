<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_Item_Builder extends Mage_Core_Model_Abstract
{
    // ########################################

    public function initialize(array $data)
    {
        // ------------------
        $this->setData('order_id', $data['order_id']);
        $this->setData('transaction_id', $data['transaction_id']);
        $this->setData('selling_manager_id', $data['selling_manager_id']);
        // ------------------

        // ------------------
        $this->setData('item_id', $data['item_id']);
        $this->setData('title', $data['title']);
        $this->setData('sku', $data['sku']);
        // ------------------

        // ------------------
        $this->setData('price', (float)$data['selling']['price']);
        $this->setData('qty_purchased', (int)$data['selling']['qty_purchased']);
        $this->setData('tax_details', json_encode($data['selling']['tax_details']));
        $this->setData('final_fee', (float)$data['selling']['final_fee']);
        // ------------------

        // ------------------
        $this->setData('variation_details', json_encode($data['variation_details']));
        // ------------------

        // ------------------
        $this->setData('tracking_details', json_encode($data['tracking_details']));
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
        $item = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order_Item')
            ->addFieldToFilter('order_id', $this->getData('order_id'))
            ->addFieldToFilter('item_id', $this->getData('item_id'))
            ->addFieldToFilter('transaction_id', $this->getData('transaction_id'))
            ->getFirstItem();

        $item->addData($this->getData());
        $item->save();

        return $item;
    }

    // ########################################
}