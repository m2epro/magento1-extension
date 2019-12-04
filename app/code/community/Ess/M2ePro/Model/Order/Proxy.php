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

    abstract public function getCheckoutMethod();

    /**
     * @return bool
     */
    public function isCheckoutMethodGuest()
    {
        return $this->getCheckoutMethod() == self::CHECKOUT_GUEST;
    }

    //########################################

    abstract public function isOrderNumberPrefixSourceMagento();

    abstract public function isOrderNumberPrefixSourceChannel();

    abstract public function getChannelOrderNumber();

    abstract public function getOrderNumberPrefix();

    //########################################

    /**
     * @return Mage_Customer_Model_Customer
     */
    abstract public function getCustomer();

    public function getCustomerFirstName()
    {
        $addressData = $this->getAddressData();

        return $addressData['firstname'];
    }

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

            $recipientNameParts               = $this->getNameParts($rawAddressData['recipient_name']);
            $this->_addressData['firstname']  = $recipientNameParts['firstname'];
            $this->_addressData['lastname']   = $recipientNameParts['lastname'];
            $this->_addressData['middlename'] = $recipientNameParts['middlename'];

            $customerNameParts                         = $this->getNameParts($rawAddressData['buyer_name']);
            $this->_addressData['customer_firstname']  = $customerNameParts['firstname'];
            $this->_addressData['customer_lastname']   = $customerNameParts['lastname'];
            $this->_addressData['customer_middlename'] = $customerNameParts['middlename'];

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

    protected function getNameParts($fullName)
    {
        $fullName = trim($fullName);

        $parts      = explode(' ', $fullName);
        $partsCount = count($parts);

        $firstName  = '';
        $middleName = '';
        $lastName   = '';

        if ($partsCount > 1) {
            $firstName = array_shift($parts);
            $lastName  = array_pop($parts);
            if (!empty($parts)) {
                $middleName = implode(' ', $parts);
            }
        } else {
            $firstName = $fullName;
        }

        return array(
            'firstname'  => $firstName ? $firstName : 'N/A',
            'middlename' => $middleName ? trim($middleName) : '',
            'lastname'   => $lastName ? $lastName : 'N/A'
        );
    }

    //########################################

    abstract public function getCurrency();

    public function convertPrice($price)
    {
        return Mage::getSingleton('M2ePro/Currency')
            ->convertPrice($price, $this->getCurrency(), $this->getStore());
    }

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

    protected function getBaseShippingPrice()
    {
        return $this->convertPriceToBase($this->getShippingPrice());
    }

    //########################################

    /**
     * @return array
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

    abstract public function isProductPriceIncludeTax();

    abstract public function isShippingPriceIncludeTax();

    // ---------------------------------------

    abstract public function isTaxModeNone();

    abstract public function isTaxModeChannel();

    abstract public function isTaxModeMagento();

    /**
     * @return bool
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
}
