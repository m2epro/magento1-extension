<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Amazon_Order extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var $magentoOrder Mage_Sales_Model_Order */
        $magentoOrder = $this->getEvent()->getMagentoOrder();

        foreach ($magentoOrder->getAllItems() as $orderItem) {

            /** @var $orderItem Mage_Sales_Model_Order_Item */

            if ($orderItem->getHasChildren()) {
                continue;
            }

            $stockItem = Mage::getModel('cataloginventory/stock_item')
                ->setStockId(Mage::helper('M2ePro/Magento_Store')->getStockId($orderItem->getProduct()->getStore()))
                ->setProductId($orderItem->getProductId())
                ->loadByProduct($orderItem->getProduct());

            if (!$stockItem->getId()) {
                continue;
            }

            /** @var $magentoStockItem Ess_M2ePro_Model_Magento_Product_StockItem */
            $magentoStockItem = Mage::getSingleton('M2ePro/Magento_Product_StockItem');
            $magentoStockItem->setStockItem($stockItem);
            $magentoStockItem->addQty($orderItem->getQtyOrdered());
        }
    }

    //########################################
}