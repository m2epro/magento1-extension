<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 */
class Ess_M2ePro_Model_Order_Item extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // M2ePro_TRANSLATIONS
    // Product does not exist.
    // Product is disabled.
    // Order Import does not support product type: %type%.

    /** @var Ess_M2ePro_Model_Order */
    private $order = NULL;

    /** @var Ess_M2ePro_Model_Magento_Product */
    private $magentoProduct = NULL;

    private $proxy = NULL;

    private static $supportedProductTypes = array(
        Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
        Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
        Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
        Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
    );

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

        $this->order = NULL;

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
        $this->order = $order;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Order
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOrder()
    {
        if (is_null($this->order)) {
            $this->order = Mage::helper('M2ePro/Component')
                ->getComponentObject($this->getComponentMode(), 'Order', $this->getOrderId());
        }

        return $this->order;
    }

    //########################################

    public function setProduct($product)
    {
        if (!$product instanceof Mage_Catalog_Model_Product) {
            $this->magentoProduct = null;
            return $this;
        }

        if (is_null($this->magentoProduct)) {
            $this->magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        }
        $this->magentoProduct->setProduct($product);

        return $this;
    }

    public function getProduct()
    {
        if (is_null($this->getProductId())) {
            return NULL;
        }

        return $this->getMagentoProduct()->getProduct();
    }

    public function getMagentoProduct()
    {
        if (is_null($this->getProductId())) {
            return NULL;
        }

        if (is_null($this->magentoProduct)) {
            $this->magentoProduct = Mage::getModel('M2ePro/Magento_Product');
            $this->magentoProduct
                ->setStoreId($this->getOrder()->getStoreId())
                ->setProductId($this->getProductId());
        }

        return $this->magentoProduct;
    }

    //########################################

    public function getProxy()
    {
        if (is_null($this->proxy)) {
            $this->proxy = $this->getChildObject()->getProxy();
        }

        return $this->proxy;
    }

    //########################################

    public function getStoreId()
    {
        $channelItem = $this->getChildObject()->getChannelItem();

        if (is_null($channelItem)) {
            return $this->getOrder()->getStoreId();
        }

        $storeId = $channelItem->getStoreId();

        if ($storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
            return $storeId;
        }

        if (is_null($this->getProductId())) {
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
     * Associate order item with product in magento
     *
     * @throws Ess_M2ePro_Model_Exception
     */
    public function associateWithProduct()
    {
        if (is_null($this->getProductId()) || !$this->getMagentoProduct()->exists()) {
            $this->assignProduct($this->getChildObject()->getAssociatedProductId());
        }

        if (!in_array($this->getMagentoProduct()->getTypeId(), self::$supportedProductTypes)) {
            $message = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
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
    private function associateVariationWithOptions()
    {
        $variationChannelOptions = $this->getChildObject()->getVariationChannelOptions();
        $magentoProduct   = $this->getMagentoProduct();

        // do nothing for amazon & buy order item, if it is mapped to product with required options,
        // but there is no information available about sold variation
        if (empty($variationChannelOptions) && $this->isComponentModeBuy() &&
            ($magentoProduct->isStrictVariationProduct() || $magentoProduct->isProductWithVariations())
        ) {
            return;
        }

        $existOptions  = $this->getAssociatedOptions();
        $existProducts = $this->getAssociatedProducts();

        if (count($existProducts) == 1
            && ($magentoProduct->isGroupedType() || $magentoProduct->isConfigurableType())
        ) {
            // grouped and configurable products can have only one associated product mapped with sold variation
            // so if count($existProducts) == 1 - there is no need for further actions
            return;
        }

        if (!empty($variationChannelOptions)) {
            $matchingHash = Ess_M2ePro_Model_Order_Matching::generateHash($variationChannelOptions);

            /** @var Ess_M2ePro_Model_Mysql4_Order_Matching_Collection $matchingCollection */
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

        $magentoOptions = $this->prepareMagentoOptions($magentoProduct->getVariationInstance()->getVariationsTypeRaw());

        $variationProductOptions = $this->getChildObject()->getVariationProductOptions();

        /** @var $optionsFinder Ess_M2ePro_Model_Order_Item_OptionsFinder */
        $optionsFinder = Mage::getModel('M2ePro/Order_Item_OptionsFinder');
        $optionsFinder->setProductId($magentoProduct->getProductId());
        $optionsFinder->setProductType($magentoProduct->getTypeId());
        $optionsFinder->setChannelOptions($variationProductOptions);
        $optionsFinder->setMagentoOptions($magentoOptions);

        $productDetails = $optionsFinder->getProductDetails();

        if (!isset($productDetails['associated_options'])) {
            return;
        }

        $existOptionsIds = array_keys($existOptions);
        $foundOptionsIds = array_keys($productDetails['associated_options']);

        if (count($existOptions) == 0 && count($existProducts) == 0) {
            // options mapping invoked for the first time, use found options
            $this->setAssociatedOptions($productDetails['associated_options']);

            if (isset($productDetails['associated_products'])) {
                $this->setAssociatedProducts($productDetails['associated_products']);
            }

            if ($optionsFinder->hasFailedOptions()) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    sprintf('Product Option(s) "%s" not found.', implode(', ', $optionsFinder->getFailedOptions()))
                );
            }

            $this->save();

            return;
        }

        if (count(array_diff($foundOptionsIds, $existOptionsIds)) > 0) {
            // options were already mapped, but not all of them
            throw new Ess_M2ePro_Model_Exception_Logic('Selected Options do not match the Product Options.');
        }
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

        if (count($associatedProducts) == 0
            || (!$magentoProduct->isGroupedType() && count($associatedOptions) == 0)
        ) {
            throw new InvalidArgumentException('Required Options were not selected.');
        }

        if ($magentoProduct->isGroupedType()) {
            $associatedOptions = array();
            $associatedProducts = reset($associatedProducts);
        }

        $magentoOptions = $this->prepareMagentoOptions($magentoProduct->getVariationInstance()->getVariationsTypeRaw());

        /** @var $optionsFinder Ess_M2ePro_Model_Order_Item_OptionsFinder */
        $optionsFinder = Mage::getModel('M2ePro/Order_Item_OptionsFinder');
        $optionsFinder->setProductId($magentoProduct->getProductId());
        $optionsFinder->setProductType($magentoProduct->getTypeId());
        $optionsFinder->setMagentoOptions($magentoOptions);

        $associatedProducts = $optionsFinder->prepareAssociatedProducts($associatedProducts);

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
        $this->save();
    }

    //########################################
}
