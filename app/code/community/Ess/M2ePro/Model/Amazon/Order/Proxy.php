<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    /** @var $order Ess_M2ePro_Model_Amazon_Order */
    protected $order = NULL;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order_Item_Proxy[] $items
     * @return Ess_M2ePro_Model_Order_Item_Proxy[]
     * @throws Exception
     */
    protected function mergeItems(array $items)
    {
        foreach ($items as $key => $item) {
            if ($item->getPrice() <= 0) {
                unset($items[$key]);
            }
        }

        if (count($items) == 0) {
            throw new Ess_M2ePro_Model_Exception('Every Item in Order has zero Price.');
        }

        return parent::mergeItems($items);
    }

    //########################################

    /**
     * @return string
     */
    public function getCheckoutMethod()
    {
        if ($this->order->getAmazonAccount()->isMagentoOrdersCustomerPredefined() ||
            $this->order->getAmazonAccount()->isMagentoOrdersCustomerNew()) {
            return self::CHECKOUT_REGISTER;
        }

        return self::CHECKOUT_GUEST;
    }

    //########################################

    /**
     * @return bool
     */
    public function isOrderNumberPrefixSourceChannel()
    {
        return $this->order->getAmazonAccount()->isMagentoOrdersNumberSourceChannel();
    }

    /**
     * @return bool
     */
    public function isOrderNumberPrefixSourceMagento()
    {
        return $this->order->getAmazonAccount()->isMagentoOrdersNumberSourceMagento();
    }

    /**
     * @return mixed
     */
    public function getChannelOrderNumber()
    {
        return $this->order->getAmazonOrderId();
    }

    /**
     * @return null|string
     */
    public function getOrderNumberPrefix()
    {
        if (!$this->order->getAmazonAccount()->isMagentoOrdersNumberPrefixEnable()) {
            return '';
        }

        return $this->order->getAmazonAccount()->getMagentoOrdersNumberPrefix();
    }

    //########################################

    /**
     * @return mixed
     */
    public function getBuyerEmail()
    {
        return $this->order->getBuyerEmail();
    }

    //########################################

    /**
     * @return false|Mage_Core_Model_Abstract|Mage_Customer_Model_Customer
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getCustomer()
    {
        $customer = Mage::getModel('customer/customer');

        if ($this->order->getAmazonAccount()->isMagentoOrdersCustomerPredefined()) {
            $customer->load($this->order->getAmazonAccount()->getMagentoOrdersCustomerId());

            if (is_null($customer->getId())) {
                throw new Ess_M2ePro_Model_Exception('Customer with ID specified in Amazon Account
                    Settings does not exist.');
            }
        }

        if ($this->order->getAmazonAccount()->isMagentoOrdersCustomerNew()) {
            $customerInfo = $this->getAddressData();

            $customer->setWebsiteId($this->order->getAmazonAccount()->getMagentoOrdersCustomerNewWebsiteId());
            $customer->loadByEmail($customerInfo['email']);

            if (!is_null($customer->getId())) {
                return $customer;
            }

            $customerInfo['website_id'] = $this->order->getAmazonAccount()->getMagentoOrdersCustomerNewWebsiteId();
            $customerInfo['group_id'] = $this->order->getAmazonAccount()->getMagentoOrdersCustomerNewGroupId();

            /** @var $customerBuilder Ess_M2ePro_Model_Magento_Customer */
            $customerBuilder = Mage::getModel('M2ePro/Magento_Customer')->setData($customerInfo);
            $customerBuilder->buildCustomer();

            $customer = $customerBuilder->getCustomer();
        }

        return $customer;
    }

    //########################################

    /**
     * @return array
     */
    public function getBillingAddressData()
    {
        if ($this->order->getAmazonAccount()->isMagentoOrdersBillingAddressSameAsShipping()) {
            return parent::getBillingAddressData();
        }

        if ($this->order->getShippingAddress()->hasSameBuyerAndRecipient()) {
            return parent::getBillingAddressData();
        }

        $customerNameParts = $this->getNameParts($this->order->getBuyerName());

        return array(
            'firstname'  => $customerNameParts['firstname'],
            'lastname'   => $customerNameParts['lastname'],
            'country_id' => '',
            'region'     => '',
            'region_id'  => '',
            'city'       => 'Amazon does not supply the complete billing Buyer information.',
            'postcode'   => '',
            'street'     => array(),
            'company'    => ''
        );
    }

    /**
     * @return bool
     */
    public function shouldIgnoreBillingAddressValidation()
    {
        if ($this->order->getAmazonAccount()->isMagentoOrdersBillingAddressSameAsShipping()) {
            return false;
        }

        if ($this->order->getShippingAddress()->hasSameBuyerAndRecipient()) {
            return false;
        }

        return true;
    }

    //########################################

    public function getCurrency()
    {
        return $this->order->getCurrency();
    }

    //########################################

    /**
     * @return array
     */
    public function getPaymentData()
    {
        $paymentData = array(
            'method'            => Mage::getSingleton('M2ePro/Magento_Payment')->getCode(),
            'component_mode'    => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'payment_method'    => '',
            'channel_order_id'  => $this->order->getAmazonOrderId(),
            'channel_final_fee' => 0,
            'transactions'      => array()
        );

        return $paymentData;
    }

    //########################################

    /**
     * @return array
     */
    public function getShippingData()
    {
        $shippingData = array(
            'shipping_method' => $this->order->getShippingService(),
            'shipping_price'  => $this->getBaseShippingPrice(),
            'carrier_title'   => Mage::helper('M2ePro')->__('Amazon Shipping')
        );

        if ($this->order->isPrime()) {
            $shippingData['shipping_method'] .= ' | Is Prime';
        }

        if ($this->order->isMerchantFulfillmentApplied()) {
            $merchantFulfillmentInfo = $this->order->getMerchantFulfillmentData();

            $shippingData['shipping_method'] .= ' | Amazon\'s Shipping Services';

            if (!empty($merchantFulfillmentInfo['shipping_service']['carrier_name'])) {
                $carrier = $merchantFulfillmentInfo['shipping_service']['carrier_name'];
                $shippingData['shipping_method'] .= ' | Carrier: '.$carrier;
            }

            if (!empty($merchantFulfillmentInfo['shipping_service']['name'])) {
                $service = $merchantFulfillmentInfo['shipping_service']['name'];
                $shippingData['shipping_method'] .= ' | Service: '.$service;
            }

            if (!empty($merchantFulfillmentInfo['shipping_service']['date']['estimated_delivery']['latest'])) {
                $deliveryDate = $merchantFulfillmentInfo['shipping_service']['date']['estimated_delivery']['latest'];
                $shippingData['shipping_method'] .= ' | Delivery Date: '.$deliveryDate;
            }
        }

        return $shippingData;
    }

    /**
     * @return float
     */
    protected function getShippingPrice()
    {
        $price = $this->order->getShippingPrice() - $this->order->getShippingDiscountAmount();

        if ($this->isTaxModeNone() && $this->getShippingPriceTaxRate() > 0) {
            $price += $this->order->getShippingPriceTaxAmount();
        }

        return $price;
    }

    //########################################

    /**
     * @return array
     */
    public function getChannelComments()
    {
        $comments = array();

        if ($this->order->getPromotionDiscountAmount() > 0) {
            $discount = Mage::getSingleton('M2ePro/Currency')
                ->formatPrice($this->getCurrency(), $this->order->getPromotionDiscountAmount());

            $comment = Mage::helper('M2ePro')->__(
                '%value% promotion discount amount was subtracted from the total amount.',
                $discount
            );
            $comment .= '<br/>';

            $comments[] = $comment;
        }

        if ($this->order->getShippingDiscountAmount() > 0) {
            $discount = Mage::getSingleton('M2ePro/Currency')
                ->formatPrice($this->getCurrency(), $this->order->getShippingDiscountAmount());

            $comment = Mage::helper('M2ePro')->__(
                '%value% discount amount was subtracted from the shipping Price.',
                $discount
            );
            $comment .= '<br/>';

            $comments[] = $comment;
        }

        // Gift Wrapped Items
        // ---------------------------------------
        $itemsGiftPrices = array();

        /** @var Ess_M2ePro_Model_Order_Item[] $items */
        $items = $this->order->getParentObject()->getItemsCollection();
        foreach ($items as $item) {
            $giftPrice = $item->getChildObject()->getGiftPrice();
            if (empty($giftPrice)) {
                continue;
            }

            $itemsGiftPrices[] = array(
                'name'  => $item->getMagentoProduct()->getName(),
                'type'  => $item->getChildObject()->getGiftType(),
                'price' => $giftPrice,
            );
        }

        if (!empty($itemsGiftPrices)) {

            $comment = '<u>'.
                Mage::helper('M2ePro')->__('The following Items are purchased with gift wraps') .
                ':</u><br/>';

            foreach ($itemsGiftPrices as $productInfo) {
                $formattedCurrency = Mage::getSingleton('M2ePro/Currency')->formatPrice(
                    $this->getCurrency(), $productInfo['price']
                );

                $comment .= '<b>'.$productInfo['name'].'</b> > '
                    .$productInfo['type'].' ('.$formattedCurrency.')';
            }

            $comments[] = $comment;
        }
        // ---------------------------------------

        return $comments;
    }

    //########################################

    /**
     * @return bool
     */
    public function hasTax()
    {
        return $this->order->getProductPriceTaxRate() > 0;
    }

    /**
     * @return bool
     */
    public function isSalesTax()
    {
        return $this->hasTax();
    }

    /**
     * @return bool
     */
    public function isVatTax()
    {
        return false;
    }

    // ---------------------------------------

    /**
     * @return float|int
     */
    public function getProductPriceTaxRate()
    {
        return $this->order->getProductPriceTaxRate();
    }

    /**
     * @return float|int
     */
    public function getShippingPriceTaxRate()
    {
        return $this->order->getShippingPriceTaxRate();
    }

    // ---------------------------------------

    /**
     * @return bool|null
     */
    public function isProductPriceIncludeTax()
    {
        $configValue = Mage::helper('M2ePro/Module')
            ->getConfig()
            ->getGroupValue('/amazon/order/tax/product_price/', 'is_include_tax');

        if (!is_null($configValue)) {
            return (bool)$configValue;
        }

        if ($this->isTaxModeChannel() || ($this->isTaxModeMixed() && $this->hasTax())) {
            return false;
        }

        return null;
    }

    /**
     * @return bool|null
     */
    public function isShippingPriceIncludeTax()
    {
        $configValue = Mage::helper('M2ePro/Module')
            ->getConfig()
            ->getGroupValue('/amazon/order/tax/shipping_price/', 'is_include_tax');

        if (!is_null($configValue)) {
            return (bool)$configValue;
        }

        if ($this->isTaxModeChannel() || ($this->isTaxModeMixed() && $this->hasTax())) {
            return false;
        }

        return null;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isTaxModeNone()
    {
        return $this->order->getAmazonAccount()->isMagentoOrdersTaxModeNone();
    }

    /**
     * @return bool
     */
    public function isTaxModeMagento()
    {
        return $this->order->getAmazonAccount()->isMagentoOrdersTaxModeMagento();
    }

    /**
     * @return bool
     */
    public function isTaxModeChannel()
    {
        return $this->order->getAmazonAccount()->isMagentoOrdersTaxModeChannel();
    }

    //########################################
}