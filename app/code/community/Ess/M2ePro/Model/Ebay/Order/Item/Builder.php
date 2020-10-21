<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_Item_Builder extends Mage_Core_Model_Abstract
{
    //########################################

    public function initialize(array $data)
    {
        // ---------------------------------------
        $this->setData('order_id', $data['order_id']);
        $this->setData('transaction_id', $data['transaction_id']);
        $this->setData('selling_manager_id', $data['selling_manager_id']);
        // ---------------------------------------

        // ---------------------------------------
        $this->setData('item_id', $data['item_id']);
        $this->setData('title', $data['title']);
        $this->setData('sku', $data['sku']);
        // ---------------------------------------

        // ---------------------------------------
        $this->setData('price', (float)$data['selling']['price']);
        $this->setData('qty_purchased', (int)$data['selling']['qty_purchased']);
        $this->setData('tax_details', Mage::helper('M2ePro')->jsonEncode($data['selling']['tax_details']));
        $this->setData('final_fee', (float)$data['selling']['final_fee']);
        $this->setData('waste_recycling_fee', (float)$data['selling']['waste_recycling_fee']);
        // ---------------------------------------

        // ---------------------------------------
        $this->setData('variation_details', Mage::helper('M2ePro')->jsonEncode($data['variation_details']));
        // ---------------------------------------

        // ---------------------------------------
        $this->setData('tracking_details', Mage::helper('M2ePro')->jsonEncode($data['tracking_details']));
        // ---------------------------------------
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order_Item
     */
    public function process()
    {
        /** @var Ess_M2ePro_Model_Order_Item $item */
        $item = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order_Item')
            ->addFieldToFilter('order_id', $this->getData('order_id'))
            ->addFieldToFilter('item_id', $this->getData('item_id'))
            ->addFieldToFilter('transaction_id', $this->getData('transaction_id'))
            ->getFirstItem();

        foreach ($this->getData() as $key => $value) {
            if (!$item->getId() || ($item->hasData($key) && $item->getData($key) != $value)) {
                $item->addData($this->getData());
                $item->save();
                break;
            }
        }

        return $item;
    }

    //########################################
}
