<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
    protected $observers = array();

    //########################################

    public function applyChanges()
    {
        if (count($this->observers) <= 0) {
            return $this;
        }

        /** @var Ess_M2ePro_Model_Observer_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Observer_Dispatcher');

        foreach ($this->observers as $productObserver) {

            $product = $productObserver->getEvent()->getData('product');
            $stockItemObserver = $this->prepareStockItemObserver($product);

            $dispatcher->catalogInventoryStockItemSaveAfter($stockItemObserver);
            $dispatcher->catalogProductSaveAfter($productObserver);
        }

        return $this->flushObservers();
    }

    /**
     * @return $this
     */
    public function flushObservers()
    {
        $this->observers = array();
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
        $productId = $product instanceof Mage_Catalog_Model_Product ? $product->getId()
                                                                    : $product;
        $key = $productId.'##'.$storeId;

        if (array_key_exists($key, $this->observers)) {
            return $this;
        }

        if (!($product instanceof Mage_Catalog_Model_Product)) {

            $product = Mage::getModel('catalog/product')
                ->setStoreId($storeId)
                ->load($product);
        }

        $observer = $this->prepareProductObserver($product);

        /** @var Ess_M2ePro_Model_Observer_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Observer_Dispatcher');
        $dispatcher->catalogProductSaveBefore($observer);

        $this->observers[$key] = $observer;
        return $this;
    }

    // ---------------------------------------

    private function prepareProductObserver(Mage_Catalog_Model_Product $product)
    {
        $event = new Varien_Event();
        $event->setProduct($product);

        $observer = new Varien_Event_Observer();
        $observer->setEvent($event);

        return $observer;
    }

    private function prepareStockItemObserver(Mage_Catalog_Model_Product $product)
    {
        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = Mage::getModel('cataloginventory/stock_item');

        $stockItem->loadByProduct($product->getId())
                  ->setProductId($product->getId());

        foreach ($product->getData('stock_item')->getData() as $key => $value) {
            $stockItem->setOrigData($key, $value);
        }

        $observer = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Event());
        $observer->setData('item', $stockItem);

        return $observer;
    }

    //########################################
}