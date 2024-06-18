<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Order_Item getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Order_Item extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    /** @var $_channelItem Ess_M2ePro_Model_Ebay_Item */
    protected $_channelItem = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Order_Item');
    }

    //########################################

    /**
     * @return false|Mage_Core_Model_Abstract
     */
    public function getProxy()
    {
        return Mage::getModel('M2ePro/Ebay_Order_Item_Proxy', $this);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Order
     */
    public function getEbayOrder()
    {
        return $this->getParentObject()->getOrder()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
    {
        return $this->getEbayOrder()->getEbayAccount();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Item
     */
    public function getChannelItem()
    {
        if ($this->_channelItem === null) {
            $this->_channelItem = Mage::getModel('M2ePro/Ebay_Item')->getCollection()
                                      ->addFieldToFilter('item_id', $this->getItemId())
                                      ->addFieldToFilter('account_id', $this->getEbayAccount()->getId())
                                      ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                                      ->getFirstItem();
        }

        return $this->_channelItem->getId() !== null ? $this->_channelItem : null;
    }

    //########################################

    public function getTransactionId()
    {
        return $this->getData('transaction_id');
    }

    public function getSellingManagerId()
    {
        return $this->getData('selling_manager_id');
    }

    public function getItemId()
    {
        return $this->getData('item_id');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return (float)$this->getData('price');
    }

    /**
     * @return float
     */
    public function getFinalFee()
    {
        return (float)$this->getData('final_fee');
    }

    /**
     * @return float
     */
    public function getWasteRecyclingFee()
    {
        return (float)$this->getData('waste_recycling_fee');
    }

    /**
     * @return int
     */
    public function getQtyPurchased()
    {
        return (int)$this->getData('qty_purchased');
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getTaxDetails()
    {
        return $this->getSettings('tax_details');
    }

    /**
     * @return float
     */
    public function getTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        return (float)$taxDetails['amount'];
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        return (float)$taxDetails['rate'];
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getVariationDetails()
    {
        return $this->getSettings('variation_details');
    }

    /**
     * @return bool
     */
    public function hasVariation()
    {
        $details = $this->getVariationDetails();
        return !empty($details);
    }

    /**
     * @return string
     */
    public function getVariationTitle()
    {
        $variationDetails = $this->getVariationDetails();

        return isset($variationDetails['title']) ? $variationDetails['title'] : '';
    }

    /**
     * @return string
     */
    public function getVariationSku()
    {
        $variationDetails = $this->getVariationDetails();

        return isset($variationDetails['sku']) ? $variationDetails['sku'] : '';
    }

    /**
     * @return array
     */
    public function getVariationOptions()
    {
        $variationDetails = $this->getVariationDetails();
        return isset($variationDetails['options']) ? $variationDetails['options'] : array();
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getTrackingDetails()
    {
        $trackingDetails = $this->getSettings('tracking_details');
        return is_array($trackingDetails) ? $trackingDetails : array();
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getVariationProductOptions()
    {
        $channelItem = $this->getChannelItem();
        if (empty($channelItem)) {
            return $this->getVariationChannelOptions();
        }

        foreach ($channelItem->getVariations() as $variation) {
            if ($this->isOptionsDifferent($variation['channel_options'], $this->getVariationChannelOptions())) {
                continue;
            }

            return $variation['product_options'];
        }

        return $this->getVariationChannelOptions();
    }


    /**
     * @param array $first
     * @param array $second
     *
     * @return bool
     */
    protected function isOptionsDifferent(array $first, array $second)
    {
        $comparator = function ($a, $b) {
            if ($a === $b) {
                return 0;
            }

            return $a > $b ? 1 : -1;
        };

        $firstDiff = array_udiff_uassoc($first, $second, $comparator, $comparator);
        $secondDiff = array_udiff_uassoc($second, $first, $comparator, $comparator);

        return count($firstDiff) !== 0 || count($secondDiff) !== 0;
    }

    /**
     * @return array
     */
    public function getVariationChannelOptions()
    {
        return $this->getVariationOptions();
    }

    //########################################

    /**
     * @return int
     */
    public function getAssociatedStoreId()
    {
        // Item was listed by M2E
        // ---------------------------------------
        if ($this->getChannelItem() !== null) {
            return $this->getEbayAccount()->isMagentoOrdersListingsStoreCustom()
                ? $this->getEbayAccount()->getMagentoOrdersListingsStoreId()
                : $this->getChannelItem()->getStoreId();
        }

        // ---------------------------------------

        return $this->getEbayAccount()->getMagentoOrdersListingsOtherStoreId();
    }

    //########################################

    public function canCreateMagentoOrder()
    {
        return $this->isOrdersCreationEnabled();
    }

    public function isReservable()
    {
        return $this->isOrdersCreationEnabled();
    }

    // ---------------------------------------

    protected function isOrdersCreationEnabled()
    {
        $channelItem = $this->getChannelItem();

        if ($channelItem !== null && !$this->getEbayAccount()->isMagentoOrdersListingsModeEnabled()) {
            return false;
        }

        if ($channelItem === null && !$this->getEbayAccount()->isMagentoOrdersListingsOtherModeEnabled()) {
            return false;
        }

        return true;
    }

    //########################################

    public function getAssociatedProductId()
    {
        // Item was listed by M2E
        // ---------------------------------------
        if ($this->getChannelItem() !== null) {
            return $this->getChannelItem()->getProductId();
        }

        // ---------------------------------------

        // Unmanaged Item
        // ---------------------------------------
        $sku = $this->getSku();
        if (strlen($this->getVariationSku()) > 0) {
            $sku = $this->getVariationSku();
        }

        if ($sku != '' && strlen($sku) <= Ess_M2ePro_Helper_Magento_Product::SKU_MAX_LENGTH) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getEbayOrder()->getAssociatedStoreId())
                ->getCollection()
                    ->addAttributeToSelect('sku')
                    ->addAttributeToFilter('sku', $sku)
                    ->getFirstItem();

            if ($product->getId()) {
                $this->associateWithProduct($product);
                return $product->getId();
            }
        }

        // ---------------------------------------

        $product = $this->createProduct();
        $this->associateWithProduct($product);

        return $product->getId();
    }

    public function prepareMagentoOptions($options)
    {
        return Mage::helper('M2ePro/Component_Ebay')->prepareOptionsForOrders($options);
    }

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function createProduct()
    {
        if (!$this->getEbayAccount()->isMagentoOrdersListingsOtherProductImportEnabled()) {
            throw new Ess_M2ePro_Model_Order_Exception_ProductCreationDisabled(
                Mage::helper('M2ePro')->__('Product creation is disabled in "Account > Orders > Product Not Found".')
            );
        }

        $order = $this->getParentObject()->getOrder();

        /** @var $itemImporter Ess_M2ePro_Model_Ebay_Order_Item_Importer */
        $itemImporter = Mage::getModel('M2ePro/Ebay_Order_Item_Importer', $this);

        $rawItemData = $itemImporter->getDataFromChannel();

        if (empty($rawItemData)) {
            throw new Ess_M2ePro_Model_Exception('Data obtaining for eBay Item failed. Please try again later.');
        }

        $productData = $itemImporter->prepareDataForProductCreation($rawItemData);

        // Try to find exist product with sku from eBay
        // ---------------------------------------
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getEbayOrder()->getAssociatedStoreId())
            ->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToFilter('sku', $productData['sku'])
                ->getFirstItem();

        if ($product->getId()) {
            return $product;
        }

        // ---------------------------------------

        $storeId = $this->getEbayAccount()->getMagentoOrdersListingsOtherStoreId();
        if ($storeId == 0) {
            $storeId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
        }

        $productData['store_id'] = $storeId;
        $productData['tax_class_id'] = $this->getEbayAccount()->getMagentoOrdersListingsOtherProductTaxClassId();

        // Create product in magento
        // ---------------------------------------
        /** @var $productBuilder Ess_M2ePro_Model_Magento_Product_Builder */
        $productBuilder = Mage::getModel('M2ePro/Magento_Product_Builder')->setData($productData);
        $productBuilder->buildProduct();
        // ---------------------------------------

        $order->addSuccessLog(
            'Product for eBay Item #%id% was created in Magento Catalog.', array('!id' => $this->getItemId())
        );

        return $productBuilder->getProduct();
    }

    protected function associateWithProduct(Mage_Catalog_Model_Product $product)
    {
        if (!$this->hasVariation()) {
            Mage::dispatchEvent(
                'm2epro_associate_ebay_order_item_to_product', array(
                'product'    => $product,
                'order_item' => $this->getParentObject(),
                )
            );
        }
    }

    //########################################

    public function deleteInstance()
    {
        return $this->delete();
    }

    //########################################
}
