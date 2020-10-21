<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Item_Builder extends Mage_Core_Model_Abstract
{
    //########################################

    public function initialize(array $data)
    {
        // Init general data
        // ---------------------------------------
        $this->setData('amazon_order_item_id', $data['amazon_order_item_id']);
        $this->setData('order_id', $data['order_id']);
        $this->setData('sku', trim($data['sku']));
        $this->setData('general_id', trim($data['general_id']));
        $this->setData('is_isbn_general_id', (int)$data['is_isbn_general_id']);
        $this->setData('title', trim($data['title']));
        $this->setData('gift_type', trim($data['gift_type']));
        $this->setData('gift_message', trim($data['gift_message']));
        // ---------------------------------------

        // Init sale data
        // ---------------------------------------
        $this->setData('price', (float)$data['price']);
        $this->setData('shipping_price', (float)$data['shipping_price']);
        $this->setData('gift_price', (float)$data['gift_price']);
        $this->setData('currency', trim($data['currency']));
        $this->setData('discount_details', Mage::helper('M2ePro')->jsonEncode($data['discount_details']));
        $this->setData('qty_purchased', (int)$data['qty_purchased']);
        $this->setData('qty_shipped', (int)$data['qty_shipped']);
        $this->setData('tax_details', Mage::helper('M2ePro')->jsonEncode($this->prepareTaxDetails($data)));
        // ---------------------------------------
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order_Item
     */
    public function process()
    {
        /** @var Ess_M2ePro_Model_Order_Item $existItem */
        $existItem = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order_Item')
            ->addFieldToFilter('amazon_order_item_id', $this->getData('amazon_order_item_id'))
            ->addFieldToFilter('order_id', $this->getData('order_id'))
            ->addFieldToFilter('sku', $this->getData('sku'))
            ->getFirstItem();

        foreach ($this->getData() as $key => $value) {
            if (!$existItem->getId() || ($existItem->hasData($key) && $existItem->getData($key) != $value)) {
                $existItem->addData($this->getData());
                $existItem->save();
                break;
            }
        }

        return $existItem;
    }

    //########################################

    protected function prepareTaxDetails($data)
    {
        if ($this->isTaxSkippedInOrder($data)) {
            $data['tax_details']['product']['value'] = 0;
            $data['tax_details']['shipping']['value'] = 0;
            $data['tax_details']['gift']['value'] = 0;
            $data['tax_details']['total']['value'] = 0;
        }

        return $data['tax_details'];
    }

    protected function isTaxSkippedInOrder($data)
    {
        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', $data['order_id']);

        foreach ($order->getChildObject()->getTaxDetails() as $tax) {
            if ($tax != 0) {
                return false;
            }
        }

        return true;
    }

    //########################################
}
