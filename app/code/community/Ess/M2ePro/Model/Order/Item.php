<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Order_Item as AmazonOrderItem;
use Ess_M2ePro_Model_Ebay_Order_Item as EbayOrderItem;
use Ess_M2ePro_Model_Walmart_Order_Item as WalmartOrderItem;

/**
 * @method AmazonOrderItem|EbayOrderItem|WalmartOrderItem getChildObject()
 */
class Ess_M2ePro_Model_Order_Item extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /** @var Ess_M2ePro_Model_Order */
    protected $_order = null;

    /** @var Ess_M2ePro_Model_Magento_Product */
    protected $_magentoProduct = null;

    protected $_proxy = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Item');
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return $this->getChildObject()->isLocked();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->_order = null;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    //########################################

    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    public function getProductId()
    {
        return $this->getData('product_id');
    }

    /**
     * @return int
     */
    public function getQtyReserved()
    {
        return (int)$this->getData('qty_reserved');
    }

    public function setAssociatedOptions(array $options)
    {
        $this->setSetting('product_details', 'associated_options', $options);
        return $this;
    }

    public function getAssociatedOptions()
    {
        return $this->getSetting('product_details', 'associated_options', array());
    }

    public function setAssociatedProducts(array $products)
    {
        $this->setSetting('product_details', 'associated_products', $products);
        return $this;
    }

    public function getAssociatedProducts()
    {
        return $this->getSetting('product_details', 'associated_products', array());
    }

    public function setReservedProducts(array $products)
    {
        $this->setSetting('product_details', 'reserved_products', $products);
        return $this;
    }

    public function getReservedProducts()
    {
        return $this->getSetting('product_details', 'reserved_products', array());
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @return $this
     */
    public function setOrder(Ess_M2ePro_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Order
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOrder()
    {
        if ($this->_order === null) {
            $this->_order = Mage::helper('M2ePro/Component')
                                ->getComponentObject($this->getComponentMode(), 'Order', $this->getOrderId());
        }

        return $this->_order;
    }

    //########################################

    public function setProduct($product)
    {
        if (!$product instanceof Mage_Catalog_Model_Product) {
            $this->_magentoProduct = null;
            return $this;
        }

        if ($this->_magentoProduct === null) {
            $this->_magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        }

        $this->_magentoProduct->setProduct($product);

        return $this;
    }

    public function getProduct()
    {
        if ($this->getProductId() === null) {
            return null;
        }

        if (!$this->isMagentoProductExists()) {
            return null;
        }

        return $this->getMagentoProduct()->getProduct();
    }

    public function getMagentoProduct()
    {
        if ($this->getProductId() === null) {
            return null;
        }

        if ($this->_magentoProduct === null) {
            $this->_magentoProduct = Mage::getModel('M2ePro/Magento_Product');
            $this->_magentoProduct
                ->setStoreId($this->getOrder()->getStoreId())
                ->setProductId($this->getProductId());
        }

        return $this->_magentoProduct;
    }

    //########################################

    public function getProxy()
    {
        if ($this->_proxy === null) {
            $this->_proxy = $this->getChildObject()->getProxy();
        }

        return $this->_proxy;
    }

    //########################################

    public function getStoreId()
    {
        $channelItem = $this->getChildObject()->getChannelItem();

        if ($channelItem === null) {
            return $this->getOrder()->getStoreId();
        }

        $storeId = $channelItem->getStoreId();

        if ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
            return $storeId;
        }

        if ($this->getProductId() === null) {
            return Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
        }

        $storeIds = Mage::getModel('M2ePro/Magento_Product')
            ->setProductId($this->getProductId())
            ->getStoreIds();

        if (empty($storeIds)) {
            return Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        return array_shift($storeIds);
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateMagentoOrder()
    {
        return $this->getChildObject()->canCreateMagentoOrder();
    }

    /**
     * @return bool
     */
    public function isReservable()
    {
        return $this->getChildObject()->isReservable();
    }

    //########################################

    /**
     * Associate order item with product in magento
     *
     * @throws Ess_M2ePro_Model_Exception
     */
    public function associateWithProduct()
    {
        if ($this->getProductId() === null || !$this->getMagentoProduct()->exists()) {
            $this->assignProduct($this->getChildObject()->getAssociatedProductId());
        }

        $supportedProductTypes = Mage::helper('M2ePro/Magento_Product')->getOriginKnownTypes();

        if (!in_array($this->getMagentoProduct()->getTypeId(), $supportedProductTypes)) {
            $message = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                'Order Import does not support Product type: %type%.', array(
                    'type' => $this->getMagentoProduct()->getTypeId()
                )
            );

            throw new Ess_M2ePro_Model_Exception($message);
        }

        $this->associateVariationWithOptions();

        if (!$this->getMagentoProduct()->isStatusEnabled()) {
            throw new Ess_M2ePro_Model_Exception('Product is disabled.');
        }
    }

    //########################################

    /**
     * Associate order item variation with options of magento product
     *
     * @throws LogicException
     * @throws Exception
     */
    protected function associateVariationWithOptions()
    {
        $variationChannelOptions = $this->getChildObject()->getVariationChannelOptions();
        $magentoProduct   = $this->getMagentoProduct();

        $existOptions  = $this->getAssociatedOptions();
        $existProducts = $this->getAssociatedProducts();

        if (count($existProducts) == 1
            && ($magentoProduct->isDownloadableType() ||
                $magentoProduct->isGroupedType() ||
                $magentoProduct->isConfigurableType())
        ) {
            // grouped and configurable products can have only one associated product mapped with sold variation
            // so if count($existProducts) == 1 - there is no need for further actions
            return;
        }

        if (!empty($variationChannelOptions)) {
            $matchingHash = Ess_M2ePro_Model_Order_Matching::generateHash($variationChannelOptions);

            /** @var Ess_M2ePro_Model_Resource_Order_Matching_Collection $matchingCollection */
            $matchingCollection = Mage::getModel('M2ePro/Order_Matching')->getCollection();
            $matchingCollection->addFieldToFilter('product_id', $this->getProductId());
            $matchingCollection->addFieldToFilter('component', $this->getComponentMode());
            $matchingCollection->addFieldToFilter('hash', $matchingHash);

            /** @var $matching Ess_M2ePro_Model_Order_Matching */
            $matching = $matchingCollection->getFirstItem();

            if ($matching->getId()) {
                $productDetails = $matching->getOutputVariationOptions();

                $this->setAssociatedProducts($productDetails['associated_products']);
                $this->setAssociatedOptions($productDetails['associated_options']);

                $this->save();
                return;
            }
        }

        $productDetails = $this->getAssociatedProductDetails($magentoProduct);

        if (!isset($productDetails['associated_options'])) {
            return;
        }

        $existOptionsIds = array_keys($existOptions);
        $foundOptionsIds = array_keys($productDetails['associated_options']);

        if (empty($existOptions) && empty($existProducts)) {
            // options mapping invoked for the first time, use found options
            $this->setAssociatedOptions($productDetails['associated_options']);

            if (isset($productDetails['associated_products'])) {
                $this->setAssociatedProducts($productDetails['associated_products']);
            }

            $this->save();

            return;
        }

        $diff = array_diff($foundOptionsIds, $existOptionsIds);
        if (!empty($diff)) {
            // options were already mapped, but not all of them
            throw new Ess_M2ePro_Model_Exception_Logic('Selected Options do not match the Product Options.');
        }
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getAssociatedProductDetails(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        if (!$magentoProduct->getTypeId()) {
            return array();
        }

        $magentoOptions = $this->prepareMagentoOptions($magentoProduct->getVariationInstance()->getVariationsTypeRaw());

        $storedItemOptions = (array)$this->getChildObject()->getVariationProductOptions();
        $orderItemOptions  = (array)$this->getChildObject()->getVariationOptions();

        /** @var $optionsFinder Ess_M2ePro_Model_Order_Item_OptionsFinder */
        $optionsFinder = Mage::getModel('M2ePro/Order_Item_OptionsFinder');
        $optionsFinder->setProduct($magentoProduct)
                      ->setMagentoOptions($magentoOptions)
                      ->addChannelOptions($storedItemOptions);

        if ($orderItemOptions !== $storedItemOptions) {
            $optionsFinder->addChannelOptions($orderItemOptions);
        }

        $optionsFinder->find();

        if (!$optionsFinder->hasFailedOptions()) {
            return $optionsFinder->getOptionsData();
        }

        throw new Ess_M2ePro_Model_Exception($optionsFinder->getOptionsNotFoundMessage());
    }

    public function prepareMagentoOptions($options)
    {
        if (method_exists($this->getChildObject(), 'prepareMagentoOptions')) {
            return $this->getChildObject()->prepareMagentoOptions($options);
        }

       return $options;
    }

    //########################################

    public function assignProduct($productId)
    {
        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($productId);

        if (!$magentoProduct->exists()) {
            $this->setData('product_id', null);
            $this->setAssociatedProducts(array());
            $this->setAssociatedOptions(array());
            $this->save();

            throw new InvalidArgumentException('Product does not exist.');
        }

        $this->setData('product_id', (int)$productId);

        $this->save();
    }

    //########################################

    public function assignProductDetails(array $associatedOptions, array $associatedProducts)
    {
        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($this->getProductId());

        if (!$magentoProduct->exists()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Product does not exist.');
        }

        if (empty($associatedProducts)
            || (!$magentoProduct->isGroupedType() && empty($associatedOptions))
        ) {
            throw new InvalidArgumentException('Required Options were not selected.');
        }

        if ($magentoProduct->isGroupedType()) {
            $associatedOptions = array();
            $associatedProducts = reset($associatedProducts);
        }

        $associatedProducts = Mage::helper('M2ePro/Magento_Product')->prepareAssociatedProducts(
            $associatedProducts,
            $magentoProduct
        );

        $this->setAssociatedProducts($associatedProducts);
        $this->setAssociatedOptions($associatedOptions);
        $this->save();
    }

    //########################################

    public function unassignProduct()
    {
        $this->setData('product_id', null);
        $this->setAssociatedProducts(array());
        $this->setAssociatedOptions(array());

        if ($this->getOrder()->getReserve()->isPlaced()) {
            $this->getOrder()->getReserve()->cancel();
        }

        $this->save();
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function pretendedToBeSimple()
    {
        if ($this->getMagentoProduct() === null || $this->getChildObject()->getChannelItem() === null) {
            return false;
        }

        if ($this->getMagentoProduct()->isGroupedType()) {
            return $this->getChildObject()->getChannelItem()->isGroupedProductModeSet();
        }

        return false;
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getAdditionalData()
    {
        return $this->getSettings('additional_data');
    }

    //########################################

    /**
     * @return bool
     */
    public function isMagentoProductExists()
    {
        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($this->getProductId());

        return $magentoProduct->exists();
    }

    //########################################
}
