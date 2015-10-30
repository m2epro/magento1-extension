<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Order_Item getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Order_Item extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const UNPAID_ITEM_PROCESS_NOT_OPENED = 0;
    const UNPAID_ITEM_PROCESS_OPENED     = 1;

    const DISPUTE_EXPLANATION_BUYER_HAS_NOT_PAID = 'BuyerNotPaid';
    const DISPUTE_REASON_BUYER_HAS_NOT_PAID      = 'BuyerHasNotPaid';

    //########################################

    // M2ePro_TRANSLATIONS
    // Product Import is disabled in eBay Account Settings.
    // Data obtaining for eBay Item failed. Please try again later.
    // Product for eBay Item #%id% was created in Magento Catalog.

    //########################################

    /** @var $channelItem Ess_M2ePro_Model_Ebay_Item */
    private $channelItem = NULL;

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
        if (is_null($this->channelItem)) {
            $this->channelItem = Mage::getModel('M2ePro/Ebay_Item')->getCollection()
                ->addFieldToFilter('item_id', $this->getItemId())
                ->addFieldToFilter('account_id', $this->getEbayAccount()->getId())
                ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                ->getFirstItem();
        }

        return !is_null($this->channelItem->getId()) ? $this->channelItem : NULL;
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
        return count($this->getVariationDetails()) > 0;
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
            if ($variation['channel_options'] != $this->getVariationChannelOptions()) {
                continue;
            }

            return $variation['product_options'];
        }

        return $this->getVariationChannelOptions();
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
        if (!is_null($this->getChannelItem())) {
            return $this->getEbayAccount()->isMagentoOrdersListingsStoreCustom()
                ? $this->getEbayAccount()->getMagentoOrdersListingsStoreId()
                : $this->getChannelItem()->getStoreId();
        }
        // ---------------------------------------

        return $this->getEbayAccount()->getMagentoOrdersListingsOtherStoreId();
    }

    //########################################

    public function getAssociatedProductId()
    {
        $this->validate();

        // Item was listed by M2E
        // ---------------------------------------
        if (!is_null($this->getChannelItem())) {
            return $this->getChannelItem()->getProductId();
        }
        // ---------------------------------------

        // 3rd party Item
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
        return Mage::helper('M2ePro/Component_Ebay')->reduceOptionsForOrders($options);
    }

    private function validate()
    {
        $ebayItem = $this->getChannelItem();

        if (!is_null($ebayItem) && !$this->getEbayAccount()->isMagentoOrdersListingsModeEnabled()) {
            throw new Ess_M2ePro_Model_Exception(
                'Magento Order Creation for Items Listed by M2E Pro is disabled in Account Settings.'
            );
        }

        if (is_null($ebayItem) && !$this->getEbayAccount()->isMagentoOrdersListingsOtherModeEnabled()) {
            throw new Ess_M2ePro_Model_Exception(
                'Magento Order Creation for Items Listed by 3rd party Software is disabled in Account Settings.'
            );
        }
    }

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Ess_M2ePro_Model_Exception
     */
    private function createProduct()
    {
        if (!$this->getEbayAccount()->isMagentoOrdersListingsOtherProductImportEnabled()) {
            throw new Ess_M2ePro_Model_Exception('Product Import is disabled in Account Settings.');
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

    private function associateWithProduct(Mage_Catalog_Model_Product $product)
    {
        if (!$this->hasVariation()) {
            Mage::dispatchEvent('m2epro_associate_ebay_order_item_to_product', array(
                'product'    => $product,
                'order_item' => $this->getParentObject(),
            ));
        }
    }

    //########################################

    /**
     * @param array $trackingDetails
     * @return bool
     */
    public function updateShippingStatus(array $trackingDetails = array())
    {
        $params = array();

        if (isset($trackingDetails['tracking_number'])) {
            $params['tracking_number'] = $trackingDetails['tracking_number'];
            $params['carrier_code'] = Mage::helper('M2ePro/Component_Ebay')->getCarrierTitle(
                $trackingDetails['carrier_code'], $trackingDetails['carrier_title']
            );

            // remove unsupported by eBay symbols
            $params['carrier_code'] = str_replace(array('\'', '"', '+', '(', ')'), array(), $params['carrier_code']);
        }

        /** @var $dispatcher Ess_M2ePro_Model_Connector_Ebay_OrderItem_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Connector_Ebay_OrderItem_Dispatcher');
        $action = Ess_M2ePro_Model_Connector_Ebay_OrderItem_Dispatcher::ACTION_UPDATE_STATUS;

        return $dispatcher->process($action, $this->getParentObject(), $params);
    }

    //########################################

    public function deleteInstance()
    {
        return $this->delete();
    }

    //########################################
}