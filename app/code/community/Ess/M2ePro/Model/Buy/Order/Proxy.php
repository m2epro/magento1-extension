<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    /** @var $order Ess_M2ePro_Model_Buy_Order */
    protected $order = NULL;

    //########################################

    /**
     * @return string
     */
    public function getCheckoutMethod()
    {
        if ($this->order->getBuyAccount()->isMagentoOrdersCustomerPredefined() ||
            $this->order->getBuyAccount()->isMagentoOrdersCustomerNew()) {
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
        return $this->order->getBuyAccount()->isMagentoOrdersNumberSourceChannel();
    }

    /**
     * @return bool
     */
    public function isOrderNumberPrefixSourceMagento()
    {
        return $this->order->getBuyAccount()->isMagentoOrdersNumberSourceMagento();
    }

    public function getChannelOrderNumber()
    {
        return $this->order->getBuyOrderId();
    }

    public function getOrderNumberPrefix()
    {
        if (!$this->order->getBuyAccount()->isMagentoOrdersNumberPrefixEnable()) {
            return '';
        }

        return $this->order->getBuyAccount()->getMagentoOrdersNumberPrefix();
    }

    //########################################

    public function getBuyerEmail()
    {
        return $this->order->getBuyerEmail();
    }

    //########################################

    /**
     * @return Mage_Customer_Model_Customer
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getCustomer()
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer');

        if ($this->order->getBuyAccount()->isMagentoOrdersCustomerPredefined()) {
            $customer->load($this->order->getBuyAccount()->getMagentoOrdersCustomerId());

            if (is_null($customer->getId())) {
                throw new Ess_M2ePro_Model_Exception('Customer with ID specified in Rakuten.com Account Settings
                    does not exist.');
            }
        }

        if ($this->order->getBuyAccount()->isMagentoOrdersCustomerNew()) {
            $customerInfo = $this->getAddressData();

            $customer->setWebsiteId($this->order->getBuyAccount()->getMagentoOrdersCustomerNewWebsiteId());
            $customer->loadByEmail($customerInfo['email']);

            if (!is_null($customer->getId())) {
                return $customer;
            }

            $customerInfo['website_id'] = $this->order->getBuyAccount()->getMagentoOrdersCustomerNewWebsiteId();
            $customerInfo['group_id'] = $this->order->getBuyAccount()->getMagentoOrdersCustomerNewGroupId();

            /** @var $customerBuilder Ess_M2ePro_Model_Magento_Customer */
            $customerBuilder = Mage::getModel('M2ePro/Magento_Customer')->setData($customerInfo);
            $customerBuilder->buildCustomer();

            $customer = $customerBuilder->getCustomer();
        }

        return $customer;
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
            'component_mode'    => Ess_M2ePro_Helper_Component_Buy::NICK,
            'payment_method'    => '',
            'channel_order_id'  => $this->order->getBuyOrderId(),
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
        return array(
            'shipping_method' => $this->order->getShippingMethod(),
            'shipping_price'  => $this->getBaseShippingPrice(),
            'carrier_title'   => Mage::helper('M2ePro')->__('Rakuten.com Shipping')
        );
    }

    /**
     * @return float
     */
    protected function getShippingPrice()
    {
        return $this->order->getShippingPrice();
    }

    //########################################

    /**
     * @return bool
     */
    public function hasTax()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isSalesTax()
    {
        return false;
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
     * @return int
     */
    public function getProductPriceTaxRate()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getShippingPriceTaxRate()
    {
        return 0;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isProductPriceIncludeTax()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isShippingPriceIncludeTax()
    {
        return false;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isTaxModeNone()
    {
        return $this->order->getBuyAccount()->isMagentoOrdersTaxModeNone();
    }

    /**
     * @return bool
     */
    public function isTaxModeChannel()
    {
        return $this->order->getBuyAccount()->isMagentoOrdersTaxModeChannel();
    }

    /**
     * @return bool
     */
    public function isTaxModeMagento()
    {
        return $this->order->getBuyAccount()->isMagentoOrdersTaxModeMagento();
    }

    //########################################
}