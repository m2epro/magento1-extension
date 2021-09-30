<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Builds the quote object, which then can be converted to magento order
 */
class Ess_M2ePro_Model_Magento_Quote
{
    /** @var Ess_M2ePro_Model_Order_Proxy */
    protected $_proxyOrder = null;

    /** @var Mage_Sales_Model_Quote */
    protected $_quote = null;

    protected $_originalStoreConfig = array();

    //########################################

    public function __construct(Ess_M2ePro_Model_Order_Proxy $proxyOrder)
    {
        $this->_proxyOrder = $proxyOrder;
    }

    public function __destruct()
    {
        if ($this->_quote === null) {
            return;
        }

        $store = $this->_quote->getStore();

        foreach ($this->_originalStoreConfig as $key => $value) {
            $store->setConfig($key, $value);
        }
    }

    //########################################

    public function getQuote()
    {
        return $this->_quote;
    }

    //########################################

    public function buildQuote()
    {
        try {
            // do not change invoke order
            // ---------------------------------------
            $this->initializeQuote();
            $this->initializeCustomer();
            $this->initializeAddresses();

            $this->configureStore();
            $this->configureTaxCalculation();

            $this->initializeCurrency();
            $this->initializeShippingMethodData();
            $this->initializeQuoteItems();
            $this->initializePaymentMethodData();

            $this->_quote->collectTotals()->save();

            $this->prepareOrderNumber();
            // ---------------------------------------
        } catch (Exception $e) {
            // Remove ordered items from customer cart
            $this->_quote->setIsActive(false);
            $this->_quote->removeAllAddresses();
            $this->_quote->removeAllItems();

            $this->_quote->save();
            throw $e;
        }
    }

    //########################################

    protected function initializeQuote()
    {
        $this->_quote = Mage::getModel('sales/quote');

        $this->_quote->setCheckoutMethod($this->_proxyOrder->getCheckoutMethod());
        $this->_quote->setStore($this->_proxyOrder->getStore());
        $this->_quote->getStore()->setData('current_currency', $this->_quote->getStore()->getBaseCurrency());
        $this->_quote->save();

        $this->_quote->setIsM2eProQuote(true);
        $this->_quote->setNeedProcessChannelTaxes(
            $this->_proxyOrder->isTaxModeChannel() ||
            ($this->_proxyOrder->isTaxModeMixed() &&
             ($this->_proxyOrder->hasTax() || $this->_proxyOrder->getWasteRecyclingFee()))
        );

        Mage::getSingleton('checkout/session')->replaceQuote($this->_quote);
    }

    //########################################

    protected function initializeCustomer()
    {
        if ($this->_proxyOrder->isCheckoutMethodGuest()) {
            $this->_quote
                ->setCustomerId(null)
                ->setCustomerEmail($this->_proxyOrder->getBuyerEmail())
                ->setCustomerFirstname($this->_proxyOrder->getCustomerFirstName())
                ->setCustomerLastname($this->_proxyOrder->getCustomerLastName())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        }

        $this->_quote->assignCustomer($this->_proxyOrder->getCustomer());
    }

    //########################################

    protected function initializeAddresses()
    {
        $billingAddress = $this->_quote->getBillingAddress();
        $billingAddress->addData($this->_proxyOrder->getBillingAddressData());
        $billingAddress->implodeStreetAddress();

        $billingAddress->setLimitCarrier('m2eproshipping');
        $billingAddress->setShippingMethod('m2eproshipping_m2eproshipping');
        $billingAddress->setCollectShippingRates(true);
        $billingAddress->setShouldIgnoreValidation($this->_proxyOrder->shouldIgnoreBillingAddressValidation());

        // ---------------------------------------

        $shippingAddress = $this->_quote->getShippingAddress();
        $shippingAddress->setSameAsBilling(0); // maybe just set same as billing?
        $shippingAddress->addData($this->_proxyOrder->getAddressData());
        $shippingAddress->implodeStreetAddress();

        $shippingAddress->setLimitCarrier('m2eproshipping');
        $shippingAddress->setShippingMethod('m2eproshipping_m2eproshipping');
        $shippingAddress->setCollectShippingRates(true);

        // ---------------------------------------
    }

    //########################################

    protected function initializeCurrency()
    {
        /** @var $currencyHelper Ess_M2ePro_Model_Currency */
        $currencyHelper = Mage::getSingleton('M2ePro/Currency');

        if ($currencyHelper->isConvertible($this->_proxyOrder->getCurrency(), $this->_quote->getStore())) {
            $currentCurrency = Mage::getModel('directory/currency')->load($this->_proxyOrder->getCurrency());
        } else {
            $currentCurrency = $this->_quote->getStore()->getBaseCurrency();
        }

        $this->_quote->getStore()->setData('current_currency', $currentCurrency);
    }

    //########################################

    /**
     * Configure store (invoked only after address, customer and store initialization and before price calculations)
     */
    protected function configureStore()
    {
        /** @var $storeConfigurator Ess_M2ePro_Model_Magento_Quote_Store_Configurator */
        $storeConfigurator = Mage::getModel('M2ePro/Magento_Quote_Store_Configurator');
        $storeConfigurator->init($this->_quote, $this->_proxyOrder);

        $this->_originalStoreConfig = $storeConfigurator->getOriginalStoreConfig();

        $storeConfigurator->prepareStoreConfigForOrder();
    }

    //########################################

    protected function configureTaxCalculation()
    {
        // this prevents customer session initialization (which affects cookies)
        // see Mage_Tax_Model_Calculation::getCustomer()
        Mage::getSingleton('tax/calculation')->setCustomer($this->_quote->getCustomer());
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order_Item_Proxy $item
     * @param Ess_M2ePro_Model_Magento_Quote_Item $quoteItemBuilder
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Object $request
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function initializeQuoteItem($item, $quoteItemBuilder, $product, $request)
    {
        // ---------------------------------------
        $productOriginalPrice = (float)$product->getPrice();

        $price = $item->getBasePrice();
        $product->setPrice($price);
        $product->setSpecialPrice($price);
        // ---------------------------------------

        // see Mage_Sales_Model_Observer::substractQtyFromQuotes
        $this->_quote->setItemsCount($this->_quote->getItemsCount() + 1);
        $this->_quote->setItemsQty((float)$this->_quote->getItemsQty() + $request->getQty());

        $result = $this->_quote->addProduct($product, $request);
        if (is_string($result)) {
            throw new Ess_M2ePro_Model_Exception($result);
        }

        $quoteItem = $this->_quote->getItemByProduct($product);
        if ($quoteItem === false) {
            return;
        }

        $weight = $product->getTypeInstance()->getWeight();
        if ($product->isConfigurable()) {
            // hack: for child product weight was not load
            $simpleProductId = $product->getCustomOption('simple_product')->getProductId();
            $weight = Mage::getResourceModel('catalog/product')->getAttributeRawValue(
                $simpleProductId, 'weight', 0
            );
        }

        $quoteItem->setStoreId($this->_quote->getStoreId());
        $quoteItem->setOriginalCustomPrice($item->getPrice());
        $quoteItem->setOriginalPrice($productOriginalPrice);
        $quoteItem->setBaseOriginalPrice($productOriginalPrice);
        $quoteItem->setWeight($weight);
        $quoteItem->setNoDiscount(1);

        $giftMessageId = $quoteItemBuilder->getGiftMessageId();
        if (!empty($giftMessageId)) {
            $quoteItem->setGiftMessageId($giftMessageId);
        }

        $quoteItem->setAdditionalData($quoteItemBuilder->getAdditionalData($quoteItem));

        $quoteItem->setWasteRecyclingFee($item->getWasteRecyclingFee() / $item->getQty());
        $quoteItem->save();
    }

    /**
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function initializeQuoteItems()
    {
        foreach ($this->_proxyOrder->getItems() as $item) {
            $this->clearQuoteItemsCache();

            /** @var Ess_M2ePro_Model_Magento_Quote_Item $quoteItemBuilder */
            $quoteItemBuilder = Mage::getModel('M2ePro/Magento_Quote_Item');
            $quoteItemBuilder->init($this->_quote, $item);

            $product = $quoteItemBuilder->getProduct();

            if (!$item->pretendedToBeSimple()) {
                $this->initializeQuoteItem($item, $quoteItemBuilder, $product, $quoteItemBuilder->getRequest());
                continue;
            }

            // ---------------------------------------

            $totalPrice = 0;
            $products = array();
            foreach ($product->getTypeInstance()->getAssociatedProducts() as $associatedProduct) {
                /** @var Mage_Catalog_Model_Product $associatedProduct */
                if ($associatedProduct->getQty() <= 0) { // skip product if default qty zero
                    continue;
                }

                $totalPrice += $associatedProduct->getPrice();
                $products[] = $associatedProduct;
            }

            // ---------------------------------------

            foreach ($products as $associatedProduct) {
                $item->setQty($associatedProduct->getQty() * $item->getOriginalQty());

                $productPriceInSetPercent = ($associatedProduct->getPrice() / $totalPrice) * 100;
                $productPriceInItem = (($item->getOriginalPrice() * $productPriceInSetPercent) / 100);
                $item->setPrice($productPriceInItem / $associatedProduct->getQty());

                $quoteItemBuilder->init($this->_quote, $item);

                $this->initializeQuoteItem(
                    $item,
                    $quoteItemBuilder,
                    $quoteItemBuilder->setTaxClassIntoProduct($associatedProduct),
                    $quoteItemBuilder->getRequest()
                );
            }
        }
    }

    /**
     * Mage_Sales_Model_Quote_Address caches items after each collectTotals call. Some extensions calls collectTotals
     * after adding new item to quote in observers. So we need clear this cache before adding new item to quote.
     */
    protected function clearQuoteItemsCache()
    {
        foreach ($this->_quote->getAllAddresses() as $address) {

            /** @var $address Mage_Sales_Model_Quote_Address */

            $address->unsetData('cached_items_all');
            $address->unsetData('cached_items_nominal');
            $address->unsetData('cached_items_nonnominal');
        }
    }

    //########################################

    protected function initializeShippingMethodData()
    {
        Mage::helper('M2ePro/Data_Global')->unsetValue('shipping_data');
        Mage::helper('M2ePro/Data_Global')->setValue('shipping_data', $this->_proxyOrder->getShippingData());

        $this->_proxyOrder->initializeShippingMethodDataPretendedToBeSimple();
    }

    //########################################

    protected function initializePaymentMethodData()
    {
        $quotePayment = $this->_quote->getPayment();
        $quotePayment->importData($this->_proxyOrder->getPaymentData());
    }

    //########################################

    protected function prepareOrderNumber()
    {
        if ($this->_proxyOrder->isOrderNumberPrefixSourceChannel()) {
            $orderNumber = $this->_proxyOrder->getOrderNumberPrefix() . $this->_proxyOrder->getChannelOrderNumber();
            $this->_quote->getResource()->isOrderIncrementIdUsed($orderNumber) && $orderNumber .= '(1)';

            $this->_quote->setReservedOrderId($orderNumber);
            return;
        }

        $orderNumber = $this->_quote->getReservedOrderId();
        empty($orderNumber) && $orderNumber = $this->_quote->getResource()->getReservedOrderId($this->_quote);
        $orderNumber = $this->_proxyOrder->getOrderNumberPrefix() . $orderNumber;

        if ($this->_quote->getResource()->isOrderIncrementIdUsed($orderNumber)) {
            $orderNumber = $this->_quote->getResource()->getReservedOrderId($this->_quote);
        }

        $this->_quote->setReservedOrderId($orderNumber);
    }

    //########################################
}
