<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Order_Proxy
{
    const CHECKOUT_GUEST    = 'guest';
    const CHECKOUT_REGISTER = 'register';

    /** @var Ess_M2ePro_Model_Ebay_Order|Ess_M2ePro_Model_Amazon_Order|Ess_M2ePro_Model_Walmart_Order */
    protected $_order;

    protected $_items;

    /** @var Mage_Core_Model_Store */
    protected $_store;

    protected $_addressData = array();

    //########################################

    public function __construct(Ess_M2ePro_Model_Component_Child_Abstract $order)
    {
        $this->_order = $order;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order_Item_Proxy[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getItems()
    {
        if ($this->_items === null) {
            $items = array();

            foreach ($this->_order->getParentObject()->getItemsCollection()->getItems() as $item) {
                $proxyItem = $item->getProxy();
                if ($proxyItem->getQty() <= 0) {
                    continue;
                }

                $items[] = $proxyItem;
            }

            $this->_items = $this->mergeItems($items);
        }

        return $this->_items;
    }

    /**
     * Order may have multiple items ordered, but some of them may be mapped to single product in magento.
     * We have to merge them to avoid qty and price calculation issues.
     *
     * @param Ess_M2ePro_Model_Order_Item_Proxy[] $items
     * @return Ess_M2ePro_Model_Order_Item_Proxy[]
     */
    protected function mergeItems(array $items)
    {
        $unsetItems = array();

        foreach ($items as $key => &$item) {
            if (in_array($key, $unsetItems)) {
                continue;
            }

            foreach ($items as $nestedKey => $nestedItem) {
                if ($key == $nestedKey) {
                    continue;
                }

                if (!$item->equals($nestedItem)) {
                    continue;
                }

                $item->merge($nestedItem);

                $unsetItems[] = $nestedKey;
            }
        }

        foreach ($unsetItems as $key) {
            unset($items[$key]);
        }

        return $items;
    }

    //########################################

    /**
     * @param Mage_Core_Model_Store $store
     * @return $this
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * @return Mage_Core_Model_Store
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getStore()
    {
        if ($this->_store === null) {
            throw new Ess_M2ePro_Model_Exception('Store is not set.');
        }

        return $this->_store;
    }

    //########################################

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getCheckoutMethod()
    {
        if ($this->_order->getParentObject()->getAccount()->getChildObject()->isMagentoOrdersCustomerPredefined() ||
            $this->_order->getParentObject()->getAccount()->getChildObject()->isMagentoOrdersCustomerNew()) {
            return self::CHECKOUT_REGISTER;
        }

        return self::CHECKOUT_GUEST;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isCheckoutMethodGuest()
    {
        return $this->getCheckoutMethod() == self::CHECKOUT_GUEST;
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isOrderNumberPrefixSourceMagento()
    {
        return $this->_order->getParentObject()->getAccount()->getChildObject()->isMagentoOrdersNumberSourceMagento();
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isOrderNumberPrefixSourceChannel()
    {
        return $this->_order->getParentObject()->getAccount()->getChildObject()->isMagentoOrdersNumberSourceChannel();
    }

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOrderNumberPrefix()
    {
        return $this->_order->getParentObject()->getAccount()->getChildObject()->getMagentoOrdersNumberRegularPrefix();
    }

    abstract public function getChannelOrderNumber();

    //########################################

    /**
     * @return false|Mage_Core_Model_Abstract|Mage_Customer_Model_Customer
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getCustomer()
    {
        $customer = Mage::getModel('customer/customer');
        $accountModel = $this->_order->getParentObject()->getAccount()->getChildObject();

        if ($accountModel->isMagentoOrdersCustomerPredefined()) {
            $customer->load($accountModel->getMagentoOrdersCustomerId());

            if ($customer->getId() === null) {
                throw new Ess_M2ePro_Model_Exception(
                    "Customer with ID specified in {$this->_order->getParentObject()->getComponentTitle()} Account
                    Settings does not exist."
                );
            }
        }

        if ($accountModel->isMagentoOrdersCustomerNew()) {
            $customerInfo = $this->getAddressData();

            $customer->setWebsiteId($accountModel->getMagentoOrdersCustomerNewWebsiteId());
            $customer->loadByEmail($customerInfo['email']);

            if ($customer->getId() !== null) {
                return $customer;
            }

            $customerInfo['website_id'] = $accountModel->getMagentoOrdersCustomerNewWebsiteId();
            $customerInfo['group_id'] = $accountModel->getMagentoOrdersCustomerNewGroupId();

            /** @var $customerBuilder Ess_M2ePro_Model_Magento_Customer */
            $customerBuilder = Mage::getModel('M2ePro/Magento_Customer')->setData($customerInfo);
            $customerBuilder->buildCustomer();

            $customer = $customerBuilder->getCustomer();
        }

        return $customer;
    }

    /**
     * @return string
     */
    public function getCustomerFirstName()
    {
        $addressData = $this->getAddressData();

        return $addressData['firstname'];
    }

    /**
     * @return string
     */
    public function getCustomerLastName()
    {
        $addressData = $this->getAddressData();

        return $addressData['lastname'];
    }

    /**
     * @return string
     */
    public function getBuyerEmail()
    {
        $addressData = $this->getAddressData();

        return $addressData['email'];
    }

    //########################################

    /**
     * @return array
     */
    public function getAddressData()
    {
        if (empty($this->_addressData)) {
            $rawAddressData = $this->_order->getShippingAddress()->getRawData();

            $recipientNameParts = $this->getNameParts($rawAddressData['recipient_name']);
            $this->_addressData['prefix'] = $recipientNameParts['prefix'];
            $this->_addressData['firstname'] = $recipientNameParts['firstname'];
            $this->_addressData['middlename'] = $recipientNameParts['middlename'];
            $this->_addressData['lastname'] = $recipientNameParts['lastname'];
            $this->_addressData['suffix'] = $recipientNameParts['suffix'];

            $customerNameParts = $this->getNameParts($rawAddressData['buyer_name']);
            $this->_addressData['customer_prefix'] = $customerNameParts['prefix'];
            $this->_addressData['customer_firstname'] = $customerNameParts['firstname'];
            $this->_addressData['customer_middlename'] = $customerNameParts['middlename'];
            $this->_addressData['customer_lastname'] = $customerNameParts['lastname'];
            $this->_addressData['customer_suffix'] = $customerNameParts['suffix'];

            $this->_addressData['email']                = $rawAddressData['email'];
            $this->_addressData['country_id']           = $rawAddressData['country_id'];
            $this->_addressData['region']               = $rawAddressData['region'];
            $this->_addressData['region_id']            = $this->_order->getShippingAddress()->getRegionId();
            $this->_addressData['city']                 = $rawAddressData['city'];
            $this->_addressData['postcode']             = $rawAddressData['postcode'];
            $this->_addressData['telephone']            = $rawAddressData['telephone'];
            $this->_addressData['street'] = !empty($rawAddressData['street']) ? $rawAddressData['street'] : array();
            $this->_addressData['company'] = !empty($rawAddressData['company']) ? $rawAddressData['company'] : '';
            $this->_addressData['save_in_address_book'] = 0;
        }

        return $this->_addressData;
    }

    /**
     * @return array
     */
    public function getBillingAddressData()
    {
        return $this->getAddressData();
    }

    /**
     * @return bool
     */
    public function shouldIgnoreBillingAddressValidation()
    {
        return false;
    }

    //########################################

    /**
     * @param $fullName
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getNameParts($fullName)
    {
        $fullName = trim($fullName);
        $parts = explode(' ', $fullName);

        $currentInfo = array(
            'prefix'     => null,
            'middlename' => null,
            'suffix'     => null
        );

        if (count($parts) > 2) {
            $prefixOptions = Mage::helper('customer')->getNamePrefixOptions($this->getStore());
            if (is_array($prefixOptions) && isset($prefixOptions[$parts[0]])) {
                $currentInfo['prefix'] = array_shift($parts);
            }
        }

        $partsCount = count($parts);
        if ($partsCount > 2) {
            $suffixOptions = Mage::helper('customer')->getNameSuffixOptions($this->getStore());
            if (is_array($suffixOptions) && isset($suffixOptions[$parts[$partsCount - 1]])) {
                $currentInfo['suffix'] = array_pop($parts);
            }
        }

        $partsCount = count($parts);
        if ($partsCount > 2) {
            $middleName = array_slice($parts, 1, $partsCount - 2);
            $currentInfo['middlename'] = implode(' ', $middleName);
            $parts = array($parts[0], $parts[$partsCount - 1]);
        }

        $currentInfo['firstname'] = isset($parts[0]) ? $parts[0] : 'NA';
        $currentInfo['lastname'] = isset($parts[1]) ? $parts[1] : $currentInfo['firstname'];

        return $currentInfo;
    }

    //########################################

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->_order->getCurrency();
    }

    /**
     * @param $price
     * @return mixed
     * @throws Ess_M2ePro_Model_Exception
     */
    public function convertPrice($price)
    {
        return Mage::getSingleton('M2ePro/Currency')
            ->convertPrice($price, $this->getCurrency(), $this->getStore());
    }

    /**
     * @param $price
     * @return mixed
     * @throws Ess_M2ePro_Model_Exception
     */
    public function convertPriceToBase($price)
    {
        return Mage::getSingleton('M2ePro/Currency')
            ->convertPriceToBaseCurrency($price, $this->getCurrency(), $this->getStore());
    }

    //########################################

    abstract public function getPaymentData();

    //########################################

    abstract public function getShippingData();

    abstract protected function getShippingPrice();

    /**
     * @return mixed
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getBaseShippingPrice()
    {
        return $this->convertPriceToBase($this->getShippingPrice());
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getComments()
    {
        return array_merge($this->getGeneralComments(), $this->getChannelComments());
    }

    /**
     * @return array
     */
    public function getChannelComments()
    {
        return array();
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getGeneralComments()
    {
        $store = $this->getStore();

        /** @var $currencyHelper Ess_M2ePro_Model_Currency */
        $currencyHelper = Mage::getSingleton('M2ePro/Currency');
        $currencyConvertRate = $currencyHelper->getConvertRateFromBase($this->getCurrency(), $store, 4);

        if ($currencyHelper->isBase($this->getCurrency(), $store)) {
            return array();
        }

        $comments = array();

        if (!$currencyHelper->isAllowed($this->getCurrency(), $store)) {
            $comments[] = <<<COMMENT
<b>Attention!</b> The Order Prices are incorrect.
Conversion was not performed as "{$this->getCurrency()}" Currency is not enabled.
Default Currency "{$store->getBaseCurrencyCode()}" was used instead.
Please, enable Currency in System > Configuration > Currency Setup.
COMMENT;
        } elseif ($currencyConvertRate == 0) {
            $comments[] = <<<COMMENT
<b>Attention!</b> The Order Prices are incorrect.
Conversion was not performed as there's no rate for "{$this->getCurrency()}".
Default Currency "{$store->getBaseCurrencyCode()}" was used instead.
Please, add Currency convert rate in System > Manage Currency > Rates.
COMMENT;
        } else {
            $comments[] = <<<COMMENT
Because the Order Currency is different from the Store Currency,
the conversion from <b>"{$this->getCurrency()}" to "{$store->getBaseCurrencyCode()}"</b> was performed
using <b>{$currencyConvertRate}</b> as a rate.
COMMENT;
        }

        return $comments;
    }

    //########################################

    abstract public function hasTax();

    abstract public function isSalesTax();

    abstract public function isVatTax();

    // ---------------------------------------

    abstract public function getProductPriceTaxRate();

    abstract public function getShippingPriceTaxRate();

    // ---------------------------------------

    /**
     * @return bool|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isProductPriceIncludeTax()
    {
        return $this->isPriceIncludeTax('product');
    }

    /**
     * @return bool|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isShippingPriceIncludeTax()
    {
        return $this->isPriceIncludeTax('shipping');
    }

    /**
     * @param $priceType
     * @return bool|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     *
     * List of config keys for comfortable search:
     * /ebay/order/tax/product_price/
     * /ebay/order/tax/shipping_price/
     * /amazon/order/tax/product_price/
     * /amazon/order/tax/shipping_price/
     * /walmart/order/tax/product_price/
     * /walmart/order/tax/shipping_price/
     */
    protected function isPriceIncludeTax($priceType)
    {
        $componentMode = $this->_order->getParentObject()->getComponentMode();
        $configValue = Mage::helper('M2ePro/Module')
            ->getConfig()
            ->getGroupValue("/{$componentMode}/order/tax/{$priceType}_price/", 'is_include_tax');

        if ($configValue !== null) {
            return (bool)$configValue;
        }

        if ($this->isTaxModeChannel() || ($this->isTaxModeMixed() && $this->hasTax())) {
            return $this->isVatTax();
        }

        return null;
    }

    // ---------------------------------------

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isTaxModeNone()
    {
        return $this->_order->getParentObject()->getAccount()->getChildObject()->isMagentoOrdersTaxModeNone();
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isTaxModeChannel()
    {
        return $this->_order->getParentObject()->getAccount()->getChildObject()->isMagentoOrdersTaxModeChannel();
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isTaxModeMagento()
    {
        return $this->_order->getParentObject()->getAccount()->getChildObject()->isMagentoOrdersTaxModeMagento();
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isTaxModeMixed()
    {
        return !$this->isTaxModeNone() &&
               !$this->isTaxModeChannel() &&
               !$this->isTaxModeMagento();
    }

    //########################################

    public function getWasteRecyclingFee()
    {
        $resultFee = 0.0;

        foreach ($this->getItems() as $item) {
            $resultFee += $item->getWasteRecyclingFee();
        }

        return $resultFee;
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception
     */
    public function initializeShippingMethodDataPretendedToBeSimple()
    {
        foreach ($this->_order->getParentObject()->getItemsCollection() as $item) {
            /** @var Ess_M2ePro_Model_Order_Item $item */
            if (!$item->pretendedToBeSimple()) {
                continue;
            }

            $shippingItems = array();
            foreach ($item->getMagentoProduct()->getTypeInstance()->getAssociatedProducts() as $associatedProduct) {
                /** @var Mage_Catalog_Model_Product $associatedProduct */
                if ($associatedProduct->getQty() <= 0) { // skip product if default qty zero
                    continue;
                }

                $total = (int)($associatedProduct->getQty() * $item->getChildObject()->getQtyPurchased());
                $shippingItems[$associatedProduct->getId()]['total'] = $total;
                $shippingItems[$associatedProduct->getId()]['shipped'] = array();
            }

            $shippingInfo = array();
            $shippingInfo['items'] = $shippingItems;
            $shippingInfo['send'] = $item->getChildObject()->getQtyPurchased();

            $additionalData = $item->getAdditionalData();
            $additionalData['shipping_info'] = $shippingInfo;
            $item->setSettings('additional_data', $additionalData);
            $item->save();
        }
    }

    //########################################
}
