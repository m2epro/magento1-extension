<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Order_Item getParentObject()
 */
class Ess_M2ePro_Model_Buy_Order_Item extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    /** @var $channelItem Ess_M2ePro_Model_Buy_Item */
    private $channelItem = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Order_Item');
    }

    //########################################

    public function getProxy()
    {
        return Mage::getModel('M2ePro/Buy_Order_Item_Proxy', $this);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Order
     */
    public function getBuyOrder()
    {
        return $this->getParentObject()->getOrder()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Account
     */
    public function getBuyAccount()
    {
        return $this->getBuyOrder()->getBuyAccount();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Item|null
     */
    public function getChannelItem()
    {
        if (is_null($this->channelItem)) {
            $this->channelItem = Mage::getModel('M2ePro/Buy_Item')->getCollection()
                ->addFieldToFilter('account_id', $this->getParentObject()->getOrder()->getAccountId())
                ->addFieldToFilter('marketplace_id', $this->getParentObject()->getOrder()->getMarketplaceId())
                ->addFieldToFilter('sku', $this->getSku())
                ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                ->getFirstItem();
        }

        return !is_null($this->channelItem->getId()) ? $this->channelItem : NULL;
    }

    //########################################

    public function getBuyOrderItemId()
    {
        return $this->getData('buy_order_item_id');
    }

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
     * @return float
     */
    public function getPrice()
    {
        return (float)$this->getData('price');
    }

    /**
     * @return float
     */
    public function getTaxAmount()
    {
        return (float)$this->getData('tax_amount');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    /**
     * @return int
     */
    public function getQtyPurchased()
    {
        return (int)$this->getData('qty');
    }

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
        return array();
    }

    //########################################

    /**
     * @return int
     */
    public function getAssociatedStoreId()
    {
        /** @var $buyAccount Ess_M2ePro_Model_Buy_Account */
        $buyAccount = $this->getBuyOrder()->getBuyAccount();

        if (!is_null($this->getChannelItem())) {
            return $buyAccount->isMagentoOrdersListingsStoreCustom()
                ? $buyAccount->getMagentoOrdersListingsStoreId()
                : $this->getChannelItem()->getStoreId();
        }

        return $buyAccount->getMagentoOrdersListingsOtherStoreId();
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
        if ($sku != '' && strlen($sku) <= Ess_M2ePro_Helper_Magento_Product::SKU_MAX_LENGTH) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId($this->getBuyOrder()->getAssociatedStoreId())
                ->getCollection()
                    ->addAttributeToSelect('sku')
                    ->addAttributeToFilter('sku', $sku)
                    ->getFirstItem();

            if ($product->getId()) {
                Mage::dispatchEvent('m2epro_associate_buy_order_item_to_product', array(
                    'product'    => $product,
                    'order_item' => $this->getParentObject(),
                ));

                return $product->getId();
            }
        }
        // ---------------------------------------

        $product = $this->createProduct();

        Mage::dispatchEvent('m2epro_associate_buy_order_item_to_product', array(
            'product'    => $product,
            'order_item' => $this->getParentObject(),
        ));

        return $product->getId();
    }

    private function validate()
    {
        /** @var $buyAccount Ess_M2ePro_Model_Buy_Account */
        $buyAccount = $this->getParentObject()->getOrder()->getAccount()->getChildObject();
        $channelItem = $this->getChannelItem();

        if (!is_null($channelItem) && !$buyAccount->isMagentoOrdersListingsModeEnabled()) {
            throw new Ess_M2ePro_Model_Exception(
                'Magento Order Creation for Items Listed by M2E Pro is disabled in Account Settings.'
            );
        }

        if (is_null($channelItem) && !$buyAccount->isMagentoOrdersListingsOtherModeEnabled()) {
            throw new Ess_M2ePro_Model_Exception(
                'Magento Order Creation for Items Listed by 3rd party software is disabled in Account Settings.'
            );
        }
    }

    private function createProduct()
    {
        if (!$this->getBuyOrder()->getBuyAccount()->isMagentoOrdersListingsOtherProductImportEnabled()) {
            throw new Ess_M2ePro_Model_Exception('Product Import is disabled in Rakuten.com Account Settings.');
        }

        $storeId = $this->getBuyOrder()->getBuyAccount()->getMagentoOrdersListingsOtherStoreId();
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
            'tax_class_id'      => $this->getBuyAccount()->getMagentoOrdersListingsOtherProductTaxClassId()
        );

        // Create product in magento
        // ---------------------------------------
        /** @var $productBuilder Ess_M2ePro_Model_Magento_Product_Builder */
        $productBuilder = Mage::getModel('M2ePro/Magento_Product_Builder')->setData($productData);
        $productBuilder->buildProduct();
        // ---------------------------------------

        $this->getParentObject()->getOrder()->addSuccessLog(
        'Product for Rakuten.com Item "%title%" was Created in Magento Catalog.', array('!title' => $this->getTitle())
        );

        return $productBuilder->getProduct();
    }

    private function getQtyForNewProduct()
    {
        $otherListing = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other')
            ->addFieldToFilter('account_id', $this->getParentObject()->getOrder()->getAccountId())
            ->addFieldToFilter('marketplace_id', $this->getParentObject()->getOrder()->getMarketplaceId())
            ->addFieldToFilter('sku', $this->getSku())
            ->getFirstItem();

        if ((int)$otherListing->getOnlineQty() > $this->getQtyPurchased()) {
            return (int)$otherListing->getOnlineQty();
        }

        return $this->getQtyPurchased();
    }

    //########################################
}