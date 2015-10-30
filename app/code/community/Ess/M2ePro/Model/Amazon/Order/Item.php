<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Order_Item getParentObject()
 */
class Ess_M2ePro_Model_Amazon_Order_Item extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    // M2ePro_TRANSLATIONS
    // Product Import is disabled in Amazon Account Settings.
    // Product for Amazon Item "%id%" was Created in Magento Catalog.
    // Product for Amazon Item "%title%" was Created in Magento Catalog.

    /** @var $channelItem Ess_M2ePro_Model_Amazon_Item */
    private $channelItem = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Order_Item');
    }

    //########################################

    public function getProxy()
    {
        return Mage::getModel('M2ePro/Amazon_Order_Item_Proxy', $this);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Order
     */
    public function getAmazonOrder()
    {
        return $this->getParentObject()->getOrder()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account
     */
    public function getAmazonAccount()
    {
        return $this->getAmazonOrder()->getAmazonAccount();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Item|null
     */
    public function getChannelItem()
    {
        if (is_null($this->channelItem)) {
            $this->channelItem = Mage::getModel('M2ePro/Amazon_Item')->getCollection()
                ->addFieldToFilter('account_id', $this->getParentObject()->getOrder()->getAccountId())
                ->addFieldToFilter('marketplace_id', $this->getParentObject()->getOrder()->getMarketplaceId())
                ->addFieldToFilter('sku', $this->getSku())
                ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                ->getFirstItem();
        }

        return !is_null($this->channelItem->getId()) ? $this->channelItem : NULL;
    }

    //########################################

    public function getAmazonOrderItemId()
    {
        return $this->getData('amazon_order_item_id');
    }

    // ---------------------------------------

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getGeneralId()
    {
        return $this->getData('general_id');
    }

    /**
     * @return int
     */
    public function getIsIsbnGeneralId()
    {
        return (int)$this->getData('is_isbn_general_id');
    }

    // ---------------------------------------

    /**
     * @return float
     */
    public function getPrice()
    {
        return (float)$this->getData('price');
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->getData('currency');
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
     * @return float
     */
    public function getGiftPrice()
    {
        return (float)$this->getData('gift_price');
    }

    public function getGiftType()
    {
        return $this->getData('gift_type');
    }

    public function getGiftMessage()
    {
        return $this->getData('gift_message');
    }

    // ---------------------------------------

    public function getTaxDetails()
    {
        return $this->getSettings('tax_details');
    }

    public function getTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();
        return isset($taxDetails['product']['value']) ? (float)$taxDetails['product']['value'] : 0.0;
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getDiscountDetails()
    {
        return $this->getSettings('discount_details');
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        $discountDetails = $this->getDiscountDetails();
        return !empty($discountDetails['promotion']['value'])
            ? ($discountDetails['promotion']['value'] / $this->getQtyPurchased()) : 0.0;
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getVariationProductOptions()
    {
        $channelItem = $this->getChannelItem();

        if (is_null($channelItem)) {
            return array();
        }

        return $channelItem->getVariationProductOptions();
    }

    /**
     * @return array
     */
    public function getVariationChannelOptions()
    {
        $channelItem = $this->getChannelItem();

        if (is_null($channelItem)) {
            return array();
        }

        return $channelItem->getVariationChannelOptions();
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
            return $this->getAmazonAccount()->isMagentoOrdersListingsStoreCustom()
                ? $this->getAmazonAccount()->getMagentoOrdersListingsStoreId()
                : $this->getChannelItem()->getStoreId();
        }
        // ---------------------------------------

        return $this->getAmazonAccount()->getMagentoOrdersListingsOtherStoreId();
    }

    //########################################

    /**
     * @return int|mixed
     * @throws Ess_M2ePro_Model_Exception
     */
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
        if ($sku != '' && strlen($sku) <= Ess_M2ePro_Helper_Magento_Product::SKU_MAX_LENGTH) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getAmazonOrder()->getAssociatedStoreId())
                ->getCollection()
                    ->addAttributeToSelect('sku')
                    ->addAttributeToFilter('sku', $sku)
                    ->getFirstItem();

            if ($product->getId()) {
                Mage::dispatchEvent('m2epro_associate_amazon_order_item_to_product', array(
                    'product'    => $product,
                    'order_item' => $this->getParentObject(),
                ));

                return $product->getId();
            }
        }
        // ---------------------------------------

        $product = $this->createProduct();

        Mage::dispatchEvent('m2epro_associate_amazon_order_item_to_product', array(
            'product'    => $product,
            'order_item' => $this->getParentObject(),
        ));

        return $product->getId();
    }

    /**
     * @throws Ess_M2ePro_Model_Exception
     */
    private function validate()
    {
        $channelItem = $this->getChannelItem();

        if (!is_null($channelItem) && !$this->getAmazonAccount()->isMagentoOrdersListingsModeEnabled()) {
            throw new Ess_M2ePro_Model_Exception(
                'Magento Order Creation for Items Listed by M2E Pro is disabled in Account Settings.'
            );
        }

        if (is_null($channelItem) && !$this->getAmazonAccount()->isMagentoOrdersListingsOtherModeEnabled()) {
            throw new Ess_M2ePro_Model_Exception(
                'Magento Order Creation for Items Listed by 3rd party software is disabled in Account Settings.'
            );
        }
    }

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Ess_M2ePro_Model_Exception
     */
    private function createProduct()
    {
        if (!$this->getAmazonAccount()->isMagentoOrdersListingsOtherProductImportEnabled()) {
            throw new Ess_M2ePro_Model_Exception('Product Import is disabled in Amazon Account Settings.');
        }

        $storeId = $this->getAmazonAccount()->getMagentoOrdersListingsOtherStoreId();
        if ($storeId == 0) {
            $storeId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
        }

        $sku = $this->getSku();
        if (strlen($sku) > Ess_M2ePro_Helper_Magento_Product::SKU_MAX_LENGTH) {
            $hashLength = 10;
            $savedSkuLength = Ess_M2ePro_Helper_Magento_Product::SKU_MAX_LENGTH - $hashLength - 1;
            $hash = Mage::helper('M2ePro')->generateUniqueHash($sku, $hashLength);

            $isSaveStart = (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                '/order/magento/settings/', 'save_start_of_long_sku_for_new_product'
            );

            if ($isSaveStart) {
                $sku = substr($sku, 0, $savedSkuLength).'-'.$hash;
            } else {
                $sku = $hash.'-'.substr($sku, strlen($sku) - $savedSkuLength, $savedSkuLength);
            }
        }

        $productData = array(
            'title'             => $this->getTitle(),
            'sku'               => $sku,
            'description'       => '',
            'short_description' => '',
            'qty'               => $this->getQtyForNewProduct(),
            'price'             => $this->getPrice(),
            'store_id'          => $storeId,
            'tax_class_id'      => $this->getAmazonAccount()->getMagentoOrdersListingsOtherProductTaxClassId()
        );

        // Create product in magento
        // ---------------------------------------
        /** @var $productBuilder Ess_M2ePro_Model_Magento_Product_Builder */
        $productBuilder = Mage::getModel('M2ePro/Magento_Product_Builder')->setData($productData);
        $productBuilder->buildProduct();
        // ---------------------------------------

        $this->getParentObject()->getOrder()->addSuccessLog(
            'Product for Amazon Item "%title%" was Created in Magento Catalog.', array('!title' => $this->getTitle())
        );

        return $productBuilder->getProduct();
    }

    private function getQtyForNewProduct()
    {
        $otherListing = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other')
            ->addFieldToFilter('account_id', $this->getParentObject()->getOrder()->getAccountId())
            ->addFieldToFilter('marketplace_id', $this->getParentObject()->getOrder()->getMarketplaceId())
            ->addFieldToFilter('sku', $this->getSku())
            ->getFirstItem();

        if ((int)$otherListing->getOnlineQty() > $this->getQtyPurchased()) {
            return $otherListing->getOnlineQty();
        }

        return $this->getQtyPurchased();
    }

    //########################################
}