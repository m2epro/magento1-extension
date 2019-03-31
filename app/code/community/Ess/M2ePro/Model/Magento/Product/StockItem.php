<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
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

    /**
     * @param $qty
     * @param bool $save
     * @return bool
     * @throws Ess_M2ePro_Model_Exception
     */
    public function subtractQty($qty, $save = true)
    {
        if (!$this->canChangeQty()) {
            return false;
        }

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

        return true;
    }

    /**
     * @param $qty
     * @param bool $save
     * @return bool
     */
    public function addQty($qty, $save = true)
    {
        if (!$this->canChangeQty()) {
            return false;
        }

        $stockItem = $this->getStockItem();
        $stockItem->addQty($qty);

        if ($stockItem->getQty() > $stockItem->getMinQty()) {
            $stockItem->setIsInStock(true);
        }

        if ($save) {
            $stockItem->save();
        }

        return true;
    }

    /**
     * @return bool
     */
    public function canChangeQty()
    {
        return Mage::helper('M2ePro/Magento_Stock')->canSubtractQty() && $this->getStockItem()->getManageStock();
    }

    //########################################
}