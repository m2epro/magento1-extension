<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/*
    $model = Mage::getModel('M2ePro/PublicServices_Product_ObjectChange');

    // you have a product ID for observing
    $model->observeProduct(561);

    // you have 'catalog/product' object for observing
    $product = Mage::getModel('catalog/product')
                          ->setStoreId(2)
                          ->load(562);
    $model->observeProduct($product);

   // make changes for these products by direct sql

    $model->applyChanges();
*/

class Ess_M2ePro_Model_PublicServices_Product_ObjectChange
{
    const VERSION = '1.0.1';

    protected $_productObservers   = array();
    protected $_stockItemObservers = array();

    //########################################

    public function applyChanges()
    {
        /** @var Ess_M2ePro_Model_Observer_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Observer_Dispatcher');

        foreach ($this->_productObservers as $productObserver) {
            $dispatcher->catalogProductSaveAfter($productObserver);
        }

        foreach ($this->_stockItemObservers as $stockItemObserver) {

            /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            $stockItem = $stockItemObserver->getData('item');

            $reloadedStockItem = Mage::getModel('cataloginventory/stock_item');
            $reloadedStockItem->setProductId($stockItem->getProductId())
                              ->setStockId($stockItem->getStockId())
                              ->loadByProduct($stockItem->getProductId());

            foreach ($reloadedStockItem->getData() as $key => $value) {
                $stockItem->setData($key, $value);
            }

            $dispatcher->catalogInventoryStockItemSaveAfter($stockItemObserver);
        }

        return $this->flushObservers();
    }

    /**
     * @return $this
     */
    public function flushObservers()
    {
        $this->_productObservers   = array();
        $this->_stockItemObservers = array();

        return $this;
    }

    //########################################

    /**
     * @param Mage_Catalog_Model_Product|int $product
     * @param int $storeId
     * @return $this
     */
    public function observeProduct($product, $storeId = 0)
    {
        if ($this->isProductObserved($product, $storeId)) {
            return $this;
        }

        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($storeId)
                ->load($product);
        }

        $key = $product->getId().'##'.$storeId;

        $productObserver = $this->prepareProductObserver($product);

        /** @var Ess_M2ePro_Model_Observer_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Observer_Dispatcher');
        $dispatcher->catalogProductSaveBefore($productObserver);
        $this->_productObservers[$key] = $productObserver;

        $stockItemObserver               = $this->prepareStockItemObserver($product);
        $this->_stockItemObservers[$key] = $stockItemObserver;

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product|int $product
     * @param int $storeId
     * @return bool
     */
    public function isProductObserved($product, $storeId = 0)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ? $product->getId()
                                                                    : $product;
        $key = $productId.'##'.$storeId;

        if (array_key_exists($key, $this->_productObservers) ||
            array_key_exists($key, $this->_stockItemObservers)) {
            return true;
        }

        return false;
    }

    // ---------------------------------------

    protected function prepareProductObserver(Mage_Catalog_Model_Product $product)
    {
        $event = new Varien_Event();
        $event->setProduct($product);

        $observer = new Varien_Event_Observer();
        $observer->setEvent($event);

        return $observer;
    }

    protected function prepareStockItemObserver(Mage_Catalog_Model_Product $product)
    {
        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->setStockId($stockItem->getStockId());
        $stockItem->setProductId($product->getId());
        $stockItem->loadByProduct($product->getId());

        $observer = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Event());
        $observer->setData('item', $stockItem);

        return $observer;
    }

    //########################################
}
