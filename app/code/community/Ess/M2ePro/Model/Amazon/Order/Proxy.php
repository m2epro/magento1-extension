<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    /** @var $order Ess_M2ePro_Model_Amazon_Order */
    protected $order = NULL;

    // ########################################

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

    // ########################################

    public function getCheckoutMethod()
    {
        if ($this->order->getAmazonAccount()->isMagentoOrdersCustomerPredefined() ||
            $this->order->getAmazonAccount()->isMagentoOrdersCustomerNew()) {
            return self::CHECKOUT_REGISTER;
        }

        return self::CHECKOUT_GUEST;
    }

    // ########################################

    public function isOrderNumberPrefixSourceChannel()
    {
        return $this->order->getAmazonAccount()->isMagentoOrdersNumberSourceChannel();
    }

    public function isOrderNumberPrefixSourceMagento()
    {
        return $this->order->getAmazonAccount()->isMagentoOrdersNumberSourceMagento();
    }

    public function getChannelOrderNumber()
    {
        return $this->order->getAmazonOrderId();
    }

    public function getOrderNumberPrefix()
    {
        if (!$this->order->getAmazonAccount()->isMagentoOrdersNumberPrefixEnable()) {
            return '';
        }

        return $this->order->getAmazonAccount()->getMagentoOrdersNumberPrefix();
    }

    // ########################################

    public function getBuyerEmail()
    {
        return $this->order->getBuyerEmail();
    }

    // ########################################

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
//            $customerInfo['is_subscribed'] = $this->order->getAmazonAccount()->isMagentoOrdersCustomerNewSubscribed();

            /** @var $customerBuilder Ess_M2ePro_Model_Magento_Customer */
            $customerBuilder = Mage::getModel('M2ePro/Magento_Customer')->setData($customerInfo);
            $customerBuilder->buildCustomer();

            $customer = $customerBuilder->getCustomer();

//            if ($this->order->getAmazonAccount()->isMagentoOrdersCustomerNewNotifyWhenCreated()) {
//                $customer->sendNewAccountEmail();
//            }
        }

        return $customer;
    }

    // ########################################

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

    // ########################################

    public function getCurrency()
    {
        return $this->order->getCurrency();
    }

    // ########################################

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

    // ########################################

    public function getShippingData()
    {
        return array(
            'shipping_method' => $this->order->getShippingService(),
            'shipping_price'  => $this->getBaseShippingPrice(),
            'carrier_title'   => Mage::helper('M2ePro')->__('Amazon Shipping')
        );
    }

    protected function getShippingPrice()
    {
        $price = $this->order->getShippingPrice() - $this->order->getShippingDiscountAmount();

        if ($this->isTaxModeNone() && $this->getShippingPriceTaxRate() > 0) {
            $price += $this->order->getShippingPriceTaxAmount();
        }

        return $price;
    }

    // ########################################

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
        // ---------------------------------------------------
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
        // ---------------------------------------------------

        return $comments;
    }

    // ########################################

    public function hasTax()
    {
        return $this->order->getProductPriceTaxRate() > 0;
    }

    public function isSalesTax()
    {
        return $this->hasTax();
    }

    public function isVatTax()
    {
        return false;
    }

    // ----------------------------------------

    public function getProductPriceTaxRate()
    {
        return $this->order->getProductPriceTaxRate();
    }

    public function getShippingPriceTaxRate()
    {
        return $this->order->getShippingPriceTaxRate();
    }

    // ----------------------------------------

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

    // ----------------------------------------

    public function isTaxModeNone()
    {
        return $this->order->getAmazonAccount()->isMagentoOrdersTaxModeNone();
    }

    public function isTaxModeMagento()
    {
        return $this->order->getAmazonAccount()->isMagentoOrdersTaxModeMagento();
    }

    public function isTaxModeChannel()
    {
        return $this->order->getAmazonAccount()->isMagentoOrdersTaxModeChannel();
    }

    // ########################################
}