<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Order_Quote extends Ess_M2ePro_Model_Observer_Abstract
{
    /**
     * @var null|Mage_Catalog_Model_Product
     */
    private $product = NULL;

    /**
     * @var null|Mage_CatalogInventory_Model_Stock_Item
     */
    private $stockItem = NULL;

    private $affectedListingsProducts = array();
    private $affectedOtherListings = array();

    //########################################

    public function beforeProcess()
    {
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        $quoteItem = $this->getEvent()->getItem();

        /* @var $product Mage_Catalog_Model_Product */
        $product = $quoteItem->getProduct();

        if (!($product instanceof Mage_Catalog_Model_Product) || (int)$product->getId() <= 0) {
            throw new Ess_M2ePro_Model_Exception('Product ID should be greater than 0.');
        }

        $this->product = $product;
    }

    public function process()
    {
        if (!$this->areThereAffectedItems()) {
            return;
        }

        Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
            $this->getProduct()->getId(),
            Ess_M2ePro_Model_ProductChange::INITIATOR_OBSERVER
        );

        $this->processQty();
        $this->processStockAvailability();
    }

    // ---------------------------------------

    private function processQty()
    {
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        $quoteItem = $this->getEvent()->getItem();

        $oldValue = (int)$this->getStockItem()->getQty();
        $newValue = $oldValue - (int)$quoteItem->getTotalQty();

        if (!$this->updateProductChangeRecord('qty',$oldValue,$newValue) || $oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $this->logListingProductMessage($listingProduct,
                                            Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_QTY,
                                            $oldValue, $newValue);
        }

        foreach ($this->getAffectedOtherListings() as $otherListing) {

            /** @var Ess_M2ePro_Model_Listing_Other $otherListing */

            $this->logOtherListingMessage($otherListing,
                                          Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_QTY,
                                          $oldValue, $newValue);
        }
    }

    private function processStockAvailability()
    {
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        $quoteItem = $this->getEvent()->getItem();

        $oldQty = (int)$this->getStockItem()->getQty();
        $newQty = $oldQty - (int)$quoteItem->getTotalQty();

        $oldValue = (bool)$this->getStockItem()->getIsInStock();
        $newValue = !($newQty <= (int)Mage::getModel('cataloginventory/stock_item')->getMinQty());

        // M2ePro_TRANSLATIONS
        // IN Stock
        // OUT of Stock

        $oldValue = $oldValue ? 'IN Stock' : 'OUT of Stock';
        $newValue = $newValue ? 'IN Stock' : 'OUT of Stock';

        if (!$this->updateProductChangeRecord('stock_availability',(int)$oldValue,(int)$newValue) ||
            $oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $this->logListingProductMessage($listingProduct,
                                            Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                                            $oldValue, $newValue);
        }

        foreach ($this->getAffectedOtherListings() as $otherListing) {

            /** @var Ess_M2ePro_Model_Listing_Other $otherListing */

            $this->logOtherListingMessage($otherListing,
                                          Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                                          $oldValue, $newValue);
        }
    }

    //########################################

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function getProduct()
    {
        if (!($this->product instanceof Mage_Catalog_Model_Product)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Property "Product" should be set first.');
        }

        return $this->product;
    }

    /**
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    private function getStockItem()
    {
        if (!is_null($this->stockItem)) {
            return $this->stockItem;
        }

        return $this->stockItem = Mage::getModel('cataloginventory/stock_item')
                                            ->loadByProduct($this->getProduct());
    }

    private function updateProductChangeRecord($attributeCode, $oldValue, $newValue)
    {
        return Mage::getModel('M2ePro/ProductChange')->updateAttribute(
            $this->getProduct()->getId(),
            $attributeCode,
            $oldValue,
            $newValue,
            Ess_M2ePro_Model_ProductChange::INITIATOR_OBSERVER
        );
    }

    //########################################

    private function areThereAffectedItems()
    {
        return count($this->getAffectedListingsProducts()) > 0 ||
               count($this->getAffectedOtherListings()) > 0;
    }

    // ---------------------------------------

    private function getAffectedListingsProducts()
    {
        if (!empty($this->affectedListingsProducts)) {
            return $this->affectedListingsProducts;
        }

        return $this->affectedListingsProducts = Mage::getResourceModel('M2ePro/Listing_Product')
                                                            ->getItemsByProductId($this->getProduct()->getId());
    }

    private function getAffectedOtherListings()
    {
        if (!empty($this->affectedOtherListings)) {
            return $this->affectedOtherListings;
        }

        return $this->affectedOtherListings = Mage::getResourceModel('M2ePro/Listing_Other')->getItemsByProductId(
            $this->getProduct()->getId(), array('component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK)
        );
    }

    //########################################

    private function logListingProductMessage(Ess_M2ePro_Model_Listing_Product $listingProduct, $action,
                                              $oldValue, $newValue)
    {
        // M2ePro_TRANSLATIONS
        // From [%from%] to [%to%].

        $log = Mage::getModel('M2ePro/Listing_Log');
        $log->setComponentMode($listingProduct->getComponentMode());

        $log->addProductMessage(
            $listingProduct->getListingId(),
            $listingProduct->getProductId(),
            $listingProduct->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            NULL,
            $action,
            Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                'From [%from%] to [%to%].',
                array('from'=>$oldValue,'to'=>$newValue)
            ),
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    private function logOtherListingMessage(Ess_M2ePro_Model_Listing_Other $otherListing, $action,
                                            $oldValue, $newValue)
    {
        // M2ePro_TRANSLATIONS
        // From [%from%] to [%to%].

        $log = Mage::getModel('M2ePro/Listing_Other_Log');
        $log->setComponentMode($otherListing->getComponentMode());

        $log->addProductMessage(
            $otherListing->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
            NULL,
            $action,
            Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                'From [%from%] to [%to%]',array('from'=>$oldValue,'to'=>$newValue)
            ),
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    //########################################
}