<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_StockItem
{
    /** @var Mage_CatalogInventory_Model_Stock_Item */
    protected $_stockItem = null;

    //########################################

    /**
     * @param Mage_CatalogInventory_Model_Stock_Item $stockItem
     * @return $this
     */
    public function setStockItem(Mage_CatalogInventory_Model_Stock_Item $stockItem)
    {
        $this->_stockItem = $stockItem;
        return $this;
    }

    /**
     * @return Mage_CatalogInventory_Model_Stock_Item
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getStockItem()
    {
        if ($this->_stockItem === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Stock Item is not set.');
        }

        return $this->_stockItem;
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

        if (!$this->isAllowedQtyBelowZero() && $this->resultOfSubtractingQtyBelowZero($qty)) {
            return false;
        }

        $this->getStockItem()->subtractQty($qty);

        if ($save) {
            $this->getStockItem()->save();
        }

        return true;
    }

    public function resultOfSubtractingQtyBelowZero($qty)
    {
        return $this->getStockItem()->getQty() - $this->getStockItem()->getMinQty() - $qty < 0;
    }

    public function isAllowedQtyBelowZero()
    {
        $backordersStatus = $this->getStockItem()->getBackorders();
        return $backordersStatus == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY ||
            $backordersStatus == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY;
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