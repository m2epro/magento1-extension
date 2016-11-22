<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_StockItem
{
    /** @var Mage_CatalogInventory_Model_Stock_Item */
    private $stockItem = null;

    //########################################

    /**
     * @param Mage_CatalogInventory_Model_Stock_Item $stockItem
     * @return $this
     */
    public function setStockItem(Mage_CatalogInventory_Model_Stock_Item $stockItem)
    {
        $this->stockItem = $stockItem;
        return $this;
    }

    /**
     * @return Mage_CatalogInventory_Model_Stock_Item
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getStockItem()
    {
        if (is_null($this->stockItem)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Stock Item is not set.');
        }

        return $this->stockItem;
    }

    public function subtractQty($qty, $save = true)
    {
        $stockItem = $this->getStockItem();

        if ($stockItem->getQty() - $stockItem->getMinQty() - $qty < 0) {
            switch ($stockItem->getBackorders()) {
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                    break;
                default:
                    throw new Ess_M2ePro_Model_Exception('The requested Quantity is not available.');
                    break;
            }
        }

        $stockItem->subtractQty($qty);

        if ($save) {
            $stockItem->save();
        }
    }

    public function addQty($qty, $save = true)
    {
        $stockItem = $this->getStockItem();
        $stockItem->addQty($qty);

        if ($stockItem->getQty() > $stockItem->getMinQty()) {
            $stockItem->setIsInStock(true);
        }

        if ($save) {
            $stockItem->save();
        }
    }

    //########################################
}