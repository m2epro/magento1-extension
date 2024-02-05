<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    /** @var Ess_M2ePro_Model_Amazon_Order */
    protected $_order = null;

    /** @var Ess_M2ePro_Model_Amazon_Order_Item_Proxy[] */
    protected $_removedProxyItems = array();

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order_Item_Proxy[] $items
     * @return Ess_M2ePro_Model_Order_Item_Proxy[]
     * @throws Exception
     */
    protected function mergeItems(array $items)
    {
        // Magento order can be created even it has zero price. Tested on Magento v. 1.7.0.2 and greater.
        // Doest not requires 'Zero Subtotal Checkout enabled'
        $minVersion = Mage::helper('M2ePro/Magento')->isCommunityEdition() ? '1.7.0.2' : '1.12';
        if (version_compare(Mage::helper('M2ePro/Magento')->getVersion(), $minVersion, '>=')) {
            return parent::mergeItems($items);
        }

        foreach ($items as $key => $item) {
            if ($item->getPrice() <= 0) {
                $this->_removedProxyItems[] = $item;
                unset($items[$key]);
            }
        }

        return parent::mergeItems($items);
    }

    //########################################

    /**
     * @return mixed
     */
    public function getChannelOrderNumber()
    {
        return $this->_order->getAmazonOrderId();
    }

    /**
     * @return null|string
     */
    public function getOrderNumberPrefix()
    {
        $amazonAccount = $this->_order->getAmazonAccount();

        $prefix = $amazonAccount->getMagentoOrdersNumberRegularPrefix();

        if ($amazonAccount->getMagentoOrdersNumberAfnPrefix() && $this->_order->isFulfilledByAmazon()) {
            $prefix .= $amazonAccount->getMagentoOrdersNumberAfnPrefix();
        }

        if ($amazonAccount->getMagentoOrdersNumberPrimePrefix() && $this->_order->isPrime()) {
            $prefix .= $amazonAccount->getMagentoOrdersNumberPrimePrefix();
        }

        if ($amazonAccount->getMagentoOrdersNumberB2bPrefix() && $this->_order->isBusiness()) {
            $prefix .= $amazonAccount->getMagentoOrdersNumberB2bPrefix();
        }

        return $prefix;
    }

    //########################################

    /**
     * @return array
     */
    public function getBillingAddressData()
    {
        if ($this->_order->getAmazonAccount()->useMagentoOrdersShippingAddressAsBillingAlways()) {
            return parent::getBillingAddressData();
        }

        if ($this->_order->getAmazonAccount()->useMagentoOrdersShippingAddressAsBillingIfSameCustomerAndRecipient() &&
            $this->_order->getShippingAddress()->hasSameBuyerAndRecipient()
        ) {
            return parent::getBillingAddressData();
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
        if ($this->_order->getAmazonAccount()->useMagentoOrdersShippingAddressAsBillingAlways()) {
            return false;
        }

        if ($this->_order->getAmazonAccount()->useMagentoOrdersShippingAddressAsBillingIfSameCustomerAndRecipient() &&
            $this->_order->getShippingAddress()->hasSameBuyerAndRecipient()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getAddressData()
    {
        parent::getAddressData(); // init $this->_addressData

        $amazonAccount = $this->_order->getAmazonAccount();
        $data = $amazonAccount->getData('magento_orders_settings');
        $data = !empty($data) ? Mage::helper('M2ePro')->jsonDecode($data) : array();

        if (!empty($data['tax']['import_tax_id_in_magento_order'])) {
            $this->_addressData['vat_id'] = $this->_order->getTaxRegistrationId();
        }

        return $this->_addressData;
    }

    public function getCustomerFirstName()
    {
        $addressData = $this->getAddressData();

        return !empty($addressData['customer_firstname'])
            ? $addressData['customer_firstname']
            : $addressData['firstname'];
    }

    public function getCustomerLastName()
    {
        $addressData = $this->getAddressData();

        return !empty($addressData['customer_lastname'])
            ? $addressData['customer_lastname']
            : $addressData['lastname'];
    }

    //########################################

    /**
     * @return array
     */
    public function getPaymentData()
    {
        $paymentData = array(
            'method'                => Mage::getSingleton('M2ePro/Magento_Payment')->getCode(),
            'component_mode'        => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'payment_method'        => '',
            'channel_order_id'      => $this->_order->getAmazonOrderId(),
            'channel_final_fee'     => 0,
            'cash_on_delivery_cost' => 0,
            'transactions'          => array()
        );

        return $paymentData;
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getShippingData()
    {
        $additionalData = '';

        if ($this->_order->isPrime()) {
            $additionalData .= 'Is Prime | ';
        }

        if ($this->_order->isBusiness()) {
            $additionalData .= 'Is Business | ';
        }

        if ($this->_order->isMerchantFulfillmentApplied()) {
            $merchantFulfillmentInfo = $this->_order->getMerchantFulfillmentData();

            $additionalData .= 'Amazon\'s Shipping Services | ';

            if (!empty($merchantFulfillmentInfo['shipping_service']['carrier_name'])) {
                $carrier = $merchantFulfillmentInfo['shipping_service']['carrier_name'];
                $additionalData .= 'Carrier: ' . $carrier . ' | ';
            }

            if (!empty($merchantFulfillmentInfo['shipping_service']['name'])) {
                $service = $merchantFulfillmentInfo['shipping_service']['name'];
                $additionalData .= 'Service: ' . $service . ' | ';
            }

            if (!empty($merchantFulfillmentInfo['shipping_service']['date']['estimated_delivery']['latest'])) {
                $deliveryDate = $merchantFulfillmentInfo['shipping_service']['date']['estimated_delivery']['latest'];
                $additionalData .= 'Delivery Date: ' . $deliveryDate . ' | ';
            }
        }

        $shippingDateTo = $this->_order->getShippingDateTo();
        $isImportShipByDate = $this->_order
            ->getAmazonAccount()
            ->isImportShipByDateToMagentoOrder();

        if ($shippingDateTo && $isImportShipByDate) {
            $additionalData .= 'Ship By Date: '
                . Mage::helper('core')->formatDate($shippingDateTo, 'medium', true)
                . ' | ';
        }

        if ($iossNumber = $this->_order->getIossNumber()) {
            $additionalData .= 'IOSS Number: ' . $iossNumber . ' | ';
        }

        if (!empty($additionalData)) {
            $additionalData = ' | ' . $additionalData;
        }

        $shippingData = array(
            'carrier_title'   => Mage::helper('M2ePro')->__('Amazon Shipping'),
            'shipping_method' => $this->_order->getShippingService() . $additionalData,
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
        $price = $this->_order->getShippingPrice() - $this->_order->getShippingDiscountAmount();

        if ($this->isTaxModeNone() && $this->getShippingPriceTaxRate() > 0) {
            $price += $this->_order->getShippingPriceTaxAmount();
        }

        return $price;
    }

    //########################################

    /**
     * @return string[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getChannelComments()
    {
        return array_merge(
            $this->getDiscountComments(),
            $this->getGiftWrappedComments(),
            $this->getRemovedOrderItemsComments(),
            $this->getAFNWarehouseComments()
        );
    }

    /**
     * @return string[]
     */
    public function getDiscountComments()
    {
        $comments = array();

        if ($this->_order->getPromotionDiscountAmount() > 0) {
            $discount = Mage::getSingleton('M2ePro/Currency')->formatPrice(
                $this->getCurrency(),
                $this->_order->getPromotionDiscountAmount()
            );

            $comments[] =  Mage::helper('M2ePro')->__(
                '%value% promotion discount amount was subtracted from the total amount.',
                $discount
            );
        }

        if ($this->_order->getShippingDiscountAmount() > 0) {
            $discount = Mage::getSingleton('M2ePro/Currency')->formatPrice(
                $this->getCurrency(),
                $this->_order->getShippingDiscountAmount()
            );

            $comments[] = Mage::helper('M2ePro')->__(
                '%value% discount amount was subtracted from the shipping Price.',
                $discount
            );
        }

        return $comments;
    }

    /**
     * @return string[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getGiftWrappedComments()
    {
        $itemsGiftPrices = array();
        foreach ($this->_order->getParentObject()->getItemsCollection() as $item) {
            /** @var Ess_M2ePro_Model_Order_Item $item */

            $giftPrice = $item->getChildObject()->getGiftPrice();
            if (empty($giftPrice)) {
                continue;
            }

            if ($item->getMagentoProduct()) {
                $itemsGiftPrices[] = array(
                    'name'  => $item->getMagentoProduct()->getName(),
                    'type'  => $item->getChildObject()->getGiftType(),
                    'price' => $giftPrice,
                );
            }
        }

        if (empty($itemsGiftPrices)) {
            return array();
        }

        $comment = '<u>'.
            Mage::helper('M2ePro')->__('The following Items are purchased with gift wraps') .
            ':</u><br/>';

        foreach ($itemsGiftPrices as $productInfo) {
            $formattedCurrency = Mage::getSingleton('M2ePro/Currency')->formatPrice(
                $this->getCurrency(),
                $productInfo['price']
            );

            $comment .= "<b>{$productInfo['name']}</b> > {$productInfo['type']} ({$formattedCurrency})<br/>";
        }

        return array($comment);
    }

    /**
     * @return string[]
     */
    public function getRemovedOrderItemsComments()
    {
        if (empty($this->_removedProxyItems)) {
            return array();
        }

        $comment = '<u>'.
            Mage::helper('M2ePro')->__(
                'The following SKUs have zero price and can not be included in Magento order line items'
            ).
            ':</u><br/>';

        foreach ($this->_removedProxyItems as $item) {
            if ($item->getMagentoProduct()) {
                $comment .= "<b>{$item->getMagentoProduct()->getSku()}</b>: {$item->getQty()} QTY<br/>";
            }
        }

        return array($comment);
    }

    /**
     * @return string[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getAFNWarehouseComments()
    {
        if (!$this->_order->isFulfilledByAmazon()) {
            return array();
        }

        $comment = '';
        $helper = Mage::helper('M2ePro');

        foreach ($this->_order->getParentObject()->getItemsCollection() as $item) {
            /** @var Ess_M2ePro_Model_Order_Item $item */

            $fulfillmentCenterId = $item->getChildObject()->getFulfillmentCenterId();
            if (empty($fulfillmentCenterId)) {
                return array();
            }

            if ($item->getMagentoProduct()) {
                $sku = $item->getMagentoProduct()->getSku();
                $comment .= "<b>{$helper->__('SKU')}:</b> {$helper->escapeHtml($sku)}&nbsp;&nbsp;&nbsp;";
            }

            if ($generalId = $item->getChildObject()->getGeneralId()) {
                $generalLabel = $item->getChildObject()->getIsIsbnGeneralId() ? 'ISBN' : 'ASIN';
                $comment .= "<b>{$helper->__($generalLabel)}:</b> {$helper->escapeHtml($generalId)}&nbsp;&nbsp;&nbsp;";
            }

            $comment .= "<b>{$helper->__('AFN Warehouse')}:</b> {$helper->escapeHtml($fulfillmentCenterId)}<br/><br/>";
        }

        return empty($comment) ? array() : array($comment);
    }

    //########################################

    /**
     * @return bool
     */
    public function hasTax()
    {
        return $this->_order->getProductPriceTaxRate() > 0;
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
        return $this->_order->getProductPriceTaxRate();
    }

    /**
     * @return float|int
     */
    public function getShippingPriceTaxRate()
    {
        return $this->_order->getShippingPriceTaxRate();
    }

    //########################################
}
