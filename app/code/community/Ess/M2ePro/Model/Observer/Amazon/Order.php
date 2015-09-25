<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Amazon_Order extends Ess_M2ePro_Model_Observer_Abstract
{
    //####################################

    public function process()
    {
        /** @var $magentoOrder Mage_Sales_Model_Order */
        $magentoOrder = $this->getEvent()->getMagentoOrder();

        foreach ($magentoOrder->getAllItems() as $orderItem) {

            /** @var $orderItem Mage_Sales_Model_Order_Item */

            if ($orderItem->getHasChildren()) {
                continue;
            }

            /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            $stockItem = Mage::getModel('cataloginventory/stock_item')
                                    ->loadByProduct($orderItem->getProductId());

            if (!$stockItem->getId()) {
                continue;
            }

            $stockItem->addQty($orderItem->getQtyOrdered())->save();
        }
    }

    //####################################
}