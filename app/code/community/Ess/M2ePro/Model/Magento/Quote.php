<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * Builds the quote object, which then can be converted to magento order
 */
class Ess_M2ePro_Model_Magento_Quote
{
    /** @var Ess_M2ePro_Model_Order_Proxy */
    private $proxyOrder = NULL;

    /** @var Mage_Sales_Model_Quote */
    private $quote = NULL;

    private $originalStoreConfig = array();

    //########################################

    public function __construct(Ess_M2ePro_Model_Order_Proxy $proxyOrder)
    {
        $this->proxyOrder = $proxyOrder;
    }

    public function __destruct()
    {
        if (is_null($this->quote)) {
            return;
        }

        $store = $this->quote->getStore();

        foreach ($this->originalStoreConfig as $key => $value) {
            $store->setConfig($key, $value);
        }
    }

    //########################################

    public function getQuote()
    {
        return $this->quote;
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

            $this->quote->collectTotals()->save();

            $this->prepareOrderNumber();
            // ---------------------------------------
        } catch (Exception $e) {
            $this->quote->setIsActive(false)->save();
            throw $e;
        }
    }

    //########################################

    private function initializeQuote()
    {
        $this->quote = Mage::getModel('sales/quote');

        $this->quote->setCheckoutMethod($this->proxyOrder->getCheckoutMethod());
        $this->quote->setStore($this->proxyOrder->getStore());
        $this->quote->getStore()->setData('current_currency', $this->quote->getStore()->getBaseCurrency());
        $this->quote->save();

        Mage::getSingleton('checkout/session')->replaceQuote($this->quote);
    }

    //########################################

    private function initializeCustomer()
    {
        if ($this->proxyOrder->isCheckoutMethodGuest()) {
            $this->quote
                ->setCustomerId(null)
                ->setCustomerEmail($this->proxyOrder->getBuyerEmail())
                ->setCustomerFirstname($this->proxyOrder->getCustomerFirstName())
                ->setCustomerLastname($this->proxyOrder->getCustomerLastName())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        }

        $this->quote->assignCustomer($this->proxyOrder->getCustomer());
    }

    //########################################

    private function initializeAddresses()
    {
        $billingAddress = $this->quote->getBillingAddress();
        $billingAddress->addData($this->proxyOrder->getBillingAddressData());
        $billingAddress->implodeStreetAddress();

        $billingAddress->setLimitCarrier('m2eproshipping');
        $billingAddress->setShippingMethod('m2eproshipping_m2eproshipping');
        $billingAddress->setCollectShippingRates(true);
        $billingAddress->setShouldIgnoreValidation($this->proxyOrder->shouldIgnoreBillingAddressValidation());

        // ---------------------------------------

        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->setSameAsBilling(0); // maybe just set same as billing?
        $shippingAddress->addData($this->proxyOrder->getAddressData());
        $shippingAddress->implodeStreetAddress();

        $shippingAddress->setLimitCarrier('m2eproshipping');
        $shippingAddress->setShippingMethod('m2eproshipping_m2eproshipping');
        $shippingAddress->setCollectShippingRates(true);

        // ---------------------------------------
    }

    //########################################

    private function initializeCurrency()
    {
        /** @var $currencyHelper Ess_M2ePro_Model_Currency */
        $currencyHelper = Mage::getSingleton('M2ePro/Currency');

        if ($currencyHelper->isConvertible($this->proxyOrder->getCurrency(), $this->quote->getStore())) {
            $currentCurrency = Mage::getModel('directory/currency')->load($this->proxyOrder->getCurrency());
        } else {
            $currentCurrency = $this->quote->getStore()->getBaseCurrency();
        }

        $this->quote->getStore()->setData('current_currency', $currentCurrency);
    }

    //########################################

    /**
     * Configure store (invoked only after address, customer and store initialization and before price calculations)
     */
    private function configureStore()
    {
        /** @var $storeConfigurator Ess_M2ePro_Model_Magento_Quote_Store_Configurator */
        $storeConfigurator = Mage::getModel('M2ePro/Magento_Quote_Store_Configurator');
        $storeConfigurator->init($this->quote, $this->proxyOrder);

        $this->originalStoreConfig = $storeConfigurator->getOriginalStoreConfig();

        $storeConfigurator->prepareStoreConfigForOrder();
    }

    //########################################

    private function configureTaxCalculation()
    {
        // this prevents customer session initialization (which affects cookies)
        // see Mage_Tax_Model_Calculation::getCustomer()
        Mage::getSingleton('tax/calculation')->setCustomer($this->quote->getCustomer());
    }

    //########################################

    private function initializeQuoteItems()
    {
        foreach ($this->proxyOrder->getItems() as $item) {

            $this->clearQuoteItemsCache();

            /** @var $quoteItemBuilder Ess_M2ePro_Model_Magento_Quote_Item */
            $quoteItemBuilder = Mage::getModel('M2ePro/Magento_Quote_Item');
            $quoteItemBuilder->init($this->quote, $item);

            $product = $quoteItemBuilder->getProduct();
            $request = $quoteItemBuilder->getRequest();

            // ---------------------------------------
            $productOriginalPrice = (float)$product->getPrice();

            $price = $item->getBasePrice();
            $product->setPrice($price);
            $product->setSpecialPrice($price);
            // ---------------------------------------

            // see Mage_Sales_Model_Observer::substractQtyFromQuotes
            $this->quote->setItemsCount($this->quote->getItemsCount() + 1);
            $this->quote->setItemsQty((float)$this->quote->getItemsQty() + $request->getQty());

            $result = $this->quote->addProduct($product, $request);
            if (is_string($result)) {
                throw new Ess_M2ePro_Model_Exception($result);
            }

            $quoteItem = $this->quote->getItemByProduct($product);

            if ($quoteItem !== false) {
                $weight = $product->getTypeInstance()->getWeight();
                if ($product->isConfigurable()) {
                    // hack: for child product weight was not load
                    $simpleProductId = $product->getCustomOption('simple_product')->getProductId();
                    $weight = Mage::getResourceModel('catalog/product')->getAttributeRawValue(
                        $simpleProductId, 'weight', 0
                    );
                }

                $quoteItem->setStoreId($this->quote->getStoreId());
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
            }
        }
    }

    /**
     * Mage_Sales_Model_Quote_Address caches items after each collectTotals call. Some extensions calls collectTotals
     * after adding new item to quote in observers. So we need clear this cache before adding new item to quote.
     */
    private function clearQuoteItemsCache()
    {
        foreach ($this->quote->getAllAddresses() as $address) {

            /** @var $address Mage_Sales_Model_Quote_Address */

            $address->unsetData('cached_items_all');
            $address->unsetData('cached_items_nominal');
            $address->unsetData('cached_items_nonominal');
        }
    }

    //########################################

    private function initializeShippingMethodData()
    {
        Mage::helper('M2ePro/Data_Global')->unsetValue('shipping_data');
        Mage::helper('M2ePro/Data_Global')->setValue('shipping_data', $this->proxyOrder->getShippingData());
    }

    //########################################

    private function initializePaymentMethodData()
    {
        $quotePayment = $this->quote->getPayment();
        $quotePayment->importData($this->proxyOrder->getPaymentData());
    }

    //########################################

    private function prepareOrderNumber()
    {
        if ($this->proxyOrder->isOrderNumberPrefixSourceChannel()) {
            $orderNumber = $this->addPrefixToOrderNumberIfNeed($this->proxyOrder->getChannelOrderNumber());
            if (Mage::helper('M2ePro/Magento')->isMagentoOrderIdUsed($orderNumber)) {
                $orderNumber .= '(1)';
            }

            $this->quote->setReservedOrderId($orderNumber);
            return;
        }

        $orderNumber = $this->quote->getReservedOrderId();
        if (empty($orderNumber)) {
            $orderNumber = $this->quote->getResource()->getReservedOrderId($this->quote);
        }

        $orderNumber = $this->addPrefixToOrderNumberIfNeed($orderNumber);

        if ($this->quote->getResource()->isOrderIncrementIdUsed($orderNumber)) {
            $orderNumber = $this->quote->getResource()->getReservedOrderId($this->quote);
        }

        $this->quote->setReservedOrderId($orderNumber);
    }

    private function addPrefixToOrderNumberIfNeed($orderNumber)
    {
        $prefix = $this->proxyOrder->getOrderNumberPrefix();
        if (empty($prefix)) {
            return $orderNumber;
        }

        return $prefix.$orderNumber;
    }

    //########################################
}