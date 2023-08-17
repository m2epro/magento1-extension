<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    const USER_ID_ATTRIBUTE_CODE = 'ebay_user_id';

    /** @var $_order Ess_M2ePro_Model_Ebay_Order */
    protected $_order = null;

    //########################################

    public function getChannelOrderNumber()
    {
        return $this->_order->getEbayOrderId();
    }

    //########################################

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOrderNumberPrefix()
    {
        $prefix = $this->_order->getEbayAccount()->getMagentoOrdersNumberRegularPrefix();

        if ($this->_order->getEbayAccount()->isMagentoOrdersNumberMarketplacePrefixUsed()) {
            $prefix .= $this->_order->getMagentoOrdersNumberMarketplacePrefix();
        }

        return $prefix;
    }

    //########################################

    /**
     * @return false|Mage_Customer_Model_Customer
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getCustomer()
    {
        /** @var  $customer Mage_Customer_Model_Customer*/
        $customer = Mage::getModel('customer/customer');

        if ($this->_order->getEbayAccount()->isMagentoOrdersCustomerPredefined()) {
            $customer->load($this->_order->getEbayAccount()->getMagentoOrdersCustomerId());

            if ($customer->getId() === null) {
                throw new Ess_M2ePro_Model_Exception(
                    'Customer with ID specified in eBay Account
                    Settings does not exist.'
                );
            }
        }

        if ($this->_order->getEbayAccount()->isMagentoOrdersCustomerNew()) {
            /** @var $customerBuilder Ess_M2ePro_Model_Magento_Customer */
            $customerBuilder = Mage::getModel('M2ePro/Magento_Customer');

            $userIdAttribute = Mage::getModel('eav/entity_attribute')->loadByCode(
                Mage::getModel('customer/customer')->getEntityTypeId(), self::USER_ID_ATTRIBUTE_CODE
            );

            if (!$userIdAttribute->getId()) {
                $customerBuilder->buildAttribute(self::USER_ID_ATTRIBUTE_CODE, 'eBay User ID');
            }

            $customerInfo = $this->getAddressData();

            $customer = $customer->getCollection()
                ->addAttributeToSelect(self::USER_ID_ATTRIBUTE_CODE)
                ->addAttributeToFilter(
                    'website_id',
                    $this->_order->getEbayAccount()->getMagentoOrdersCustomerNewWebsiteId()
                )
                ->addAttributeToFilter(self::USER_ID_ATTRIBUTE_CODE, $this->_order->getBuyerUserId())->getFirstItem();

            if (!empty($customer) && $customer->getId() !== null) {
                return $customer;
            }

            $customer->setWebsiteId($this->_order->getEbayAccount()->getMagentoOrdersCustomerNewWebsiteId());
            $customer->loadByEmail($customerInfo['email']);

            if ($customer->getId() !== null) {
                $customer->setData(self::USER_ID_ATTRIBUTE_CODE, $this->_order->getBuyerUserId());
                $customer->save();

                return $customer;
            }

            $customerInfo['website_id'] = $this->_order->getEbayAccount()->getMagentoOrdersCustomerNewWebsiteId();
            $customerInfo['group_id'] = $this->_order->getEbayAccount()->getMagentoOrdersCustomerNewGroupId();

            $customerBuilder->setData($customerInfo);
            $customerBuilder->buildCustomer();

            $customer = $customerBuilder->getCustomer();

            $customer->setData(self::USER_ID_ATTRIBUTE_CODE, $this->_order->getBuyerUserId());
            $customer->save();
        }

        return $customer;
    }

    //########################################

    /**
     * @return array
     */
    public function getAddressData()
    {
        if (!$this->_order->isUseGlobalShippingProgram() &&
            !$this->_order->isUseClickAndCollect()
        ) {
            return parent::getAddressData();
        }

        $addressModel = $this->_order->isUseGlobalShippingProgram() ? $this->_order->getGlobalShippingWarehouseAddress()
            : $this->_order->getShippingAddress();

        $rawAddressData = $addressModel->getRawData();

        $addressData = array();

        $recipientNameParts = $this->getNameParts($rawAddressData['recipient_name']);
        $addressData['prefix'] = $recipientNameParts['prefix'];
        $addressData['firstname'] = $recipientNameParts['firstname'];
        $addressData['middlename'] = $recipientNameParts['middlename'];
        $addressData['lastname'] = $recipientNameParts['lastname'];
        $addressData['suffix'] = $recipientNameParts['suffix'];

        $customerNameParts = $this->getNameParts($rawAddressData['buyer_name']);
        $addressData['customer_prefix'] = $customerNameParts['prefix'];
        $addressData['customer_firstname'] = $customerNameParts['firstname'];
        $addressData['customer_middlename'] = $customerNameParts['middlename'];
        $addressData['customer_lastname'] = $customerNameParts['lastname'];
        $addressData['customer_suffix'] = $customerNameParts['suffix'];

        $addressData['email'] = $rawAddressData['email'];
        $addressData['country_id'] = $rawAddressData['country_id'];
        $addressData['region'] = $rawAddressData['region'];
        $addressData['region_id'] = $addressModel->getRegionId();
        $addressData['city'] = $rawAddressData['city'];
        $addressData['postcode'] = $rawAddressData['postcode'];
        $addressData['telephone'] = $rawAddressData['telephone'];
        $addressData['company'] = !empty($rawAddressData['company']) ? $rawAddressData['company'] : '';

        // Adding reference id into street array
        // ---------------------------------------
        $referenceId = '';
        $addressData['street'] = !empty($rawAddressData['street']) ? $rawAddressData['street'] : array();

        if ($this->_order->isUseGlobalShippingProgram()) {
            $details = $this->_order->getGlobalShippingDetails();
            isset($details['warehouse_address']['reference_id']) &&
            $referenceId = 'Ref #'.$details['warehouse_address']['reference_id'];
        }

        if ($this->_order->isUseClickAndCollect()) {
            $details = $this->_order->getClickAndCollectDetails();
            isset($details['reference_id']) && $referenceId = 'Ref #'.$details['reference_id'];
        }

        if (!empty($referenceId)) {
            if (count($addressData['street']) >= 2) {
                $addressData['street'] = array(
                    $referenceId,
                    implode(' ', $addressData['street']),
                );
            } else {
                array_unshift($addressData['street'], $referenceId);
            }
        }

        // ---------------------------------------

        $addressData['save_in_address_book'] = 0;

        return $addressData;
    }

    /**
     * @return array
     */
    public function getBillingAddressData()
    {
        if ($this->_order->getEbayAccount()->useMagentoOrdersShippingAddressAsBillingAlways()) {
            return parent::getAddressData();
        }

        if ($this->_order->getEbayAccount()->useMagentoOrdersShippingAddressAsBillingIfSameCustomerAndRecipient() &&
            $this->_order->getShippingAddress()->hasSameBuyerAndRecipient()
        ) {
            return parent::getAddressData();
        }

        $customerNameParts = $this->getNameParts($this->_order->getBuyerName());

        return array(
            'prefix'     => $customerNameParts['prefix'],
            'firstname'  => $customerNameParts['firstname'],
            'middlename' => $customerNameParts['middlename'],
            'lastname'   => $customerNameParts['lastname'],
            'suffix'     => $customerNameParts['suffix'],
            'country_id' => '',
            'region'     => '',
            'region_id'  => '',
            'city'       => 'eBay does not supply the complete billing Buyer information.',
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
        if ($this->_order->getEbayAccount()->useMagentoOrdersShippingAddressAsBillingAlways()) {
            return false;
        }

        if ($this->_order->getEbayAccount()->useMagentoOrdersShippingAddressAsBillingIfSameCustomerAndRecipient() &&
            $this->_order->getShippingAddress()->hasSameBuyerAndRecipient()
        ) {
            return false;
        }

        return true;
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getPaymentData()
    {
        $paymentMethodTitle = $this->_order->getPaymentMethod();
        $paymentMethodTitle == 'None' && $paymentMethodTitle = Mage::helper('M2ePro')->__('Not Selected Yet');

        $paymentData = array(
            'method'                => Mage::getSingleton('M2ePro/Magento_Payment')->getCode(),
            'component_mode'        => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'payment_method'        => $paymentMethodTitle,
            'channel_order_id'      => $this->_order->getEbayOrderId(),
            'channel_final_fee'     => $this->convertPrice($this->_order->getFinalFee()),
            'transactions'          => $this->getPaymentTransactions(),
            'tax_id'                => $this->_order->getBuyerTaxId(),
        );

        return $paymentData;
    }

    /**
     * @return array
     */
    public function getPaymentTransactions()
    {
        /** @var Ess_M2ePro_Model_Ebay_Order_ExternalTransaction[] $externalTransactions */
        $externalTransactions = $this->_order->getExternalTransactionsCollection()->getItems();

        $paymentTransactions = array();
        foreach ($externalTransactions as $externalTransaction) {
            $paymentTransactions[] = array(
                'transaction_id'   => $externalTransaction->getTransactionId(),
                'sum'              => $externalTransaction->getSum(),
                'fee'              => $externalTransaction->getFee(),
                'transaction_date' => $externalTransaction->getTransactionDate(),
            );
        }

        return $paymentTransactions;
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getShippingData()
    {
        $additionalData = '';

        if ($this->_order->isUseClickAndCollect()) {
            $additionalData .= 'Click And Collect | ';
            $details = $this->_order->getClickAndCollectDetails();

            if (!empty($details['location_id'])) {
                $additionalData .= 'Store ID: ' . $details['location_id'] . ' | ';
            }

            if (!empty($details['reference_id'])) {
                $additionalData .= 'Reference ID: ' . $details['reference_id'] . ' | ';
            }

            if (!empty($details['delivery_date'])) {
                $additionalData .= 'Delivery Date: ' . $details['delivery_date'] . ' | ';
            }
        }

        $shippingDateTo = $this->_order->getShippingDateTo();
        $isImportShipByDate = $this->_order
            ->getEbayAccount()
            ->isImportShipByDateToMagentoOrder();

        if ($shippingDateTo && $isImportShipByDate) {
            $additionalData .= 'Ship By Date: '
                . Mage::helper('core')->formatDate($shippingDateTo, 'medium', true)
                . ' | ';
        }

        if ($taxReference = $this->_order->getTaxReference()) {
            $additionalData .= 'IOSS/OSS Number: ' . $taxReference . ' | ';
        }

        if (!empty($additionalData)) {
            $additionalData = ' | ' . $additionalData;
        }

        $shippingMethod = $this->_order->getShippingService();

        if ($this->_order->isUseGlobalShippingProgram()) {
            $globalShippingDetails = $this->_order->getGlobalShippingDetails();
            $globalShippingDetails = $globalShippingDetails['service_details'];

            if (!empty($globalShippingDetails['service_details']['service'])) {
                $shippingMethod = $globalShippingDetails['service_details']['service'];
            }
        }

        $shippingData = array(
            'carrier_title'   => Mage::helper('M2ePro')->__('eBay Shipping'),
            'shipping_method' => $shippingMethod . $additionalData,
            'shipping_price'  => $this->getBaseShippingPrice()
        );

        return $shippingData;
    }

    /**
     * @return float
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getShippingPrice()
    {
        if ($this->_order->isUseGlobalShippingProgram()) {
            $globalShippingDetails = $this->_order->getGlobalShippingDetails();
            $price = (float)$globalShippingDetails['service_details']['price'];
        } else {
            $price = $this->_order->getShippingPrice();
        }

        if ($this->isTaxModeNone() && !$this->isShippingPriceIncludeTax()) {
            $taxAmount = Mage::getSingleton('tax/calculation')
                ->calcTaxAmount($price, $this->getShippingPriceTaxRate(), false, false);

            $price += $taxAmount;
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

        if ($this->_order->isUseGlobalShippingProgram()) {
            $comments[] = '<b>'.
                          Mage::helper('M2ePro')->__('Global Shipping Program is used for this Order').
                          '</b><br/>';
        }

        $buyerMessage = $this->_order->getBuyerMessage();
        if (!empty($buyerMessage)) {
            $comment = '<b>' . Mage::helper('M2ePro')->__('Checkout Message From Buyer') . ': </b>';
            $comment .= $buyerMessage . '<br/>';

            $comments[] = $comment;
        }

        return $comments;
    }

    //########################################

    /**
     * @return bool
     */
    public function hasTax()
    {
        return $this->_order->hasTax();
    }

    /**
     * @return bool
     */
    public function isSalesTax()
    {
        return $this->_order->isSalesTax();
    }

    /**
     * @return bool
     */
    public function isVatTax()
    {
        return $this->_order->isVatTax();
    }

    // ---------------------------------------

    /**
     * @return float|int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getProductPriceTaxRate()
    {
        if (!$this->hasTax()) {
            return 0;
        }

        if ($this->isTaxModeNone() || $this->isTaxModeMagento()) {
            return 0;
        }

        return $this->_order->getTaxRate();
    }

    /**
     * @return float|int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getShippingPriceTaxRate()
    {
        if (!$this->hasTax()) {
            return 0;
        }

        if ($this->isTaxModeNone() || $this->isTaxModeMagento()) {
            return 0;
        }

        if (!$this->_order->isShippingPriceHasTax()) {
            return 0;
        }

        return $this->getProductPriceTaxRate();
    }

    // ---------------------------------------

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isTaxModeNone()
    {
        if ($this->_order->isUseGlobalShippingProgram()) {
            return true;
        }

        return parent::isTaxModeNone();
    }

    //########################################
}
