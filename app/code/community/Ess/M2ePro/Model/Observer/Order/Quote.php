<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ChangeProcessorAbstract;

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

        $this->addListingProductInstructions();

        $this->processQty();
        $this->processStockAvailability();
    }

    // ---------------------------------------

    private function processQty()
    {
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        $quoteItem = $this->getEvent()->getItem();

        if ($quoteItem->getHasChildren()) {
            return;
        }

        $oldValue = (int)$this->getStockItem()->getQty();
        $newValue = $oldValue - (int)$quoteItem->getTotalQty();

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $this->logListingProductMessage($listingProduct,
                                            Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_QTY,
                                            $oldValue, $newValue);
        }
    }

    private function processStockAvailability()
    {
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        $quoteItem = $this->getEvent()->getItem();

        if ($quoteItem->getHasChildren()) {
            return;
        }

        $oldQty = (int)$this->getStockItem()->getQty();
        $newQty = $oldQty - (int)$quoteItem->getTotalQty();

        $oldValue = (bool)$this->getStockItem()->getIsInStock();
        $newValue = !($newQty <= (int)$this->getStockItem()->getMinQty());

        // M2ePro_TRANSLATIONS
        // IN Stock
        // OUT of Stock

        $oldValue = $oldValue ? 'IN Stock' : 'OUT of Stock';
        $newValue = $newValue ? 'IN Stock' : 'OUT of Stock';

        if ($oldValue == $newValue) {
            return;
        }

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $this->logListingProductMessage($listingProduct,
                                            Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
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

        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        $quoteItem = $this->getEvent()->getItem();

        $this->stockItem = Mage::getModel('cataloginventory/stock_item')
            ->setStockId(Mage::helper('M2ePro/Magento_Store')->getStockId($quoteItem->getStoreId()))
            ->setProductId($this->getProduct()->getId())
            ->loadByProduct($this->getProduct());

        return $this->stockItem;
    }

    private function addListingProductInstructions()
    {
        $synchronizationInstructionsData = array();

        foreach ($this->getAffectedListingsProducts() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract $changeProcessor */
            $changeProcessor = Mage::getModel(
                'M2ePro/'.ucfirst($listingProduct->getComponentMode()).'_Magento_Product_ChangeProcessor'
            );
            $changeProcessor->setListingProduct($listingProduct);
            $changeProcessor->setDefaultInstructionTypes(array(
                ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_STATUS_DATA_POTENTIALLY_CHANGED,
                ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
            ));
            $changeProcessor->process();
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add(
            $synchronizationInstructionsData
        );
    }

    //########################################

    private function areThereAffectedItems()
    {
        return count($this->getAffectedListingsProducts()) > 0;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    private function getAffectedListingsProducts()
    {
        if (!empty($this->affectedListingsProducts)) {
            return $this->affectedListingsProducts;
        }

        return $this->affectedListingsProducts = Mage::getResourceModel('M2ePro/Listing_Product')
                                                            ->getItemsByProductId($this->getProduct()->getId());
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
            Mage::helper('M2ePro/Module_Log')->encodeDescription(
                'From [%from%] to [%to%].',
                array('!from'=>$oldValue,'!to'=>$newValue)
            ),
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
        );
    }

    //########################################
}