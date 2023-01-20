<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Order_Item getParentObject()
 */
class Ess_M2ePro_Model_Walmart_Order_Item extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    const STATUS_CREATED           = 'created';
    const STATUS_ACKNOWLEDGED      = 'acknowledged';
    const STATUS_SHIPPED           = 'shipped';
    const STATUS_SHIPPED_PARTIALLY = 'shippedPartially';
    const STATUS_CANCELLED         = 'cancelled';

    /** @var $_channelItem Ess_M2ePro_Model_Walmart_Item */
    protected $_channelItem = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Order_Item');
    }

    //########################################

    public function getProxy()
    {
        return Mage::getModel('M2ePro/Walmart_Order_Item_Proxy', $this);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Order
     */
    public function getWalmartOrder()
    {
        return $this->getParentObject()->getOrder()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Account
     */
    public function getWalmartAccount()
    {
        return $this->getWalmartOrder()->getWalmartAccount();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Item|null
     */
    public function getChannelItem()
    {
        if ($this->_channelItem === null) {
            $this->_channelItem = Mage::getModel('M2ePro/Walmart_Item')->getCollection()
                 ->addFieldToFilter('account_id', $this->getParentObject()->getOrder()->getAccountId())
                 ->addFieldToFilter('marketplace_id', $this->getParentObject()->getOrder()->getMarketplaceId())
                 ->addFieldToFilter('sku', $this->getSku())
                 ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                 ->getFirstItem();
        }

        return $this->_channelItem->getId() !== null ? $this->_channelItem : null;
    }

    //########################################

    public function getWalmartOrderItemId()
    {
        return $this->getData('walmart_order_item_id');
    }

    public function getMergedWalmartOrderItemIds()
    {
        return $this->getSettings('merged_walmart_order_item_ids');
    }

    // ---------------------------------------

    public function getStatus()
    {
        return $this->getData('status');
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
     * @return array
     */
    public function getVariationProductOptions()
    {
        $channelItem = $this->getChannelItem();

        if ($channelItem === null) {
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

        if ($channelItem === null) {
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
        if ($this->getChannelItem() !== null) {
            return $this->getWalmartAccount()->isMagentoOrdersListingsStoreCustom()
                ? $this->getWalmartAccount()->getMagentoOrdersListingsStoreId()
                : $this->getChannelItem()->getStoreId();
        }

        // ---------------------------------------

        return $this->getWalmartAccount()->getMagentoOrdersListingsOtherStoreId();
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

        if ($channelItem !== null && !$this->getWalmartAccount()->isMagentoOrdersListingsModeEnabled()) {
            return false;
        }

        if ($channelItem === null && !$this->getWalmartAccount()->isMagentoOrdersListingsOtherModeEnabled()) {
            return false;
        }

        return true;
    }

    //########################################

    /**
     * @return int|mixed
     * @throws Ess_M2ePro_Model_Exception
     */
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
        if ($sku != '' && strlen($sku) <= Ess_M2ePro_Helper_Magento_Product::SKU_MAX_LENGTH) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getWalmartOrder()->getAssociatedStoreId())
                ->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToFilter('sku', $sku)
                ->getFirstItem();

            if ($product->getId()) {
                Mage::dispatchEvent(
                    'm2epro_associate_walmart_order_item_to_product', array(
                    'product'    => $product,
                    'order_item' => $this->getParentObject(),
                    )
                );

                return $product->getId();
            }
        }

        // ---------------------------------------

        $product = $this->createProduct();

        Mage::dispatchEvent(
            'm2epro_associate_walmart_order_item_to_product', array(
            'product'    => $product,
            'order_item' => $this->getParentObject(),
            )
        );

        return $product->getId();
    }

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function createProduct()
    {
        if (!$this->getWalmartAccount()->isMagentoOrdersListingsOtherProductImportEnabled()) {
            throw new Ess_M2ePro_Model_Order_Exception_ProductCreationDisabled(
                Mage::helper('M2ePro')->__('Product creation is disabled in "Account > Orders > Product Not Found".')
            );
        }

        $storeId = $this->getWalmartAccount()->getMagentoOrdersListingsOtherStoreId();
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
            'tax_class_id'      => $this->getWalmartAccount()->getMagentoOrdersListingsOtherProductTaxClassId()
        );

        // Create product in magento
        // ---------------------------------------
        /** @var $productBuilder Ess_M2ePro_Model_Magento_Product_Builder */
        $productBuilder = Mage::getModel('M2ePro/Magento_Product_Builder')->setData($productData);
        $productBuilder->buildProduct();
        // ---------------------------------------

        $this->getParentObject()->getOrder()->addSuccessLog(
            'Product for Walmart Item "%title%" was Created in Magento Catalog.', array('!title' => $this->getTitle())
        );

        return $productBuilder->getProduct();
    }

    protected function getQtyForNewProduct()
    {
        $otherListing = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other')
            ->addFieldToFilter('account_id', $this->getParentObject()->getOrder()->getAccountId())
            ->addFieldToFilter('marketplace_id', $this->getParentObject()->getOrder()->getMarketplaceId())
            ->addFieldToFilter('sku', $this->getSku())
            ->getFirstItem();

        if ((int)$otherListing->getOnlineQty() > $this->getQtyPurchased()) {
            return $otherListing->getOnlineQty();
        }

        return $this->getQtyPurchased();
    }

    /**
     * @return bool
     */
    public function isBuyerCancellationRequested()
    {
        return $this->getData('buyer_cancellation_requested') == '1';
    }

    /**
     * @return bool
     */
    public function isBuyerCancellationPossible()
    {
        $status = $this->getStatus();
        return $status === self::STATUS_CREATED || $status === self::STATUS_ACKNOWLEDGED;
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getTrackingDetails()
    {
        $trackingDetails = $this->getSettings('tracking_details');

        return is_array($trackingDetails) ? $trackingDetails : array();
    }
}
