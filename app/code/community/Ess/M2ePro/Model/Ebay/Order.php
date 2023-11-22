<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Ebay as EbayHelper;

/**
 * @method Ess_M2ePro_Model_Order getParentObject()
 * @method Ess_M2ePro_Model_Resource_Ebay_Order getResource()
 */
class Ess_M2ePro_Model_Ebay_Order extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const CHECKOUT_STATUS_INCOMPLETE = 0;
    const CHECKOUT_STATUS_COMPLETED  = 1;

    const PAYMENT_STATUS_NOT_SELECTED = 0;
    const PAYMENT_STATUS_ERROR        = 1;
    const PAYMENT_STATUS_PROCESS      = 2;
    const PAYMENT_STATUS_COMPLETED    = 3;

    const SHIPPING_STATUS_NOT_SELECTED = 0;
    const SHIPPING_STATUS_PROCESSING   = 1;
    const SHIPPING_STATUS_COMPLETED    = 2;

    const STATUS_PENDING   = 0;
    const STATUS_UNSHIPPED = 1;
    const STATUS_SHIPPED   = 2;
    const STATUS_CANCELED  = 3;
    const STATUS_PENDING_RESERVED = 4;

    /** All reasons: https://developer.ebay.com/Devzone/post-order/types/CancelReasonEnum.html */
    const CANCEL_REASON_DEFAULT   = 'OTHER';
    const CANCEL_REASON_BUYER_ASK = 'BUYER_ASKED_CANCEL';

    /** @var $_externalTransactionsCollection Ess_M2ePro_Model_Resource_Ebay_Order_ExternalTransaction_Collection */
    protected $_externalTransactionsCollection = null;

    protected $_subTotalPrice = null;

    protected $_grandTotalPrice = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Order');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Order_Proxy
     */
    public function getProxy()
    {
        return Mage::getModel('M2ePro/Ebay_Order_Proxy', $this);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
    {
        return $this->getParentObject()->getAccount()->getChildObject();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Resource_Ebay_Order_ExternalTransaction_Collection
     */
    public function getExternalTransactionsCollection()
    {
        if ($this->_externalTransactionsCollection === null) {
            $this->_externalTransactionsCollection = Mage::getModel('M2ePro/Ebay_Order_ExternalTransaction')
                                                         ->getCollection()
                                                         ->addFieldToFilter('order_id', $this->getData('order_id'));
        }

        return $this->_externalTransactionsCollection;
    }

    /**
     * @return bool
     */
    public function hasExternalTransactions()
    {
        return $this->getExternalTransactionsCollection()->getSize() > 0;
    }

    //########################################

    public function getEbayOrderId()
    {
        return $this->getData('ebay_order_id');
    }

    public function getSellingManagerId()
    {
        return $this->getData('selling_manager_id');
    }

    // ---------------------------------------

    public function getBuyerName()
    {
        return $this->getData('buyer_name');
    }

    public function getBuyerEmail()
    {
        return $this->getData('buyer_email');
    }

    public function getBuyerUserId()
    {
        return $this->getData('buyer_user_id');
    }

    public function getBuyerMessage()
    {
        return $this->getData('buyer_message');
    }

    public function getBuyerTaxId()
    {
        return $this->getData('buyer_tax_id');
    }

    // ---------------------------------------

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getFinalFee()
    {
        /** @var Ess_M2ePro_Model_Order_Item[] $items */
        $items = $this->getParentObject()->getItemsCollection()->getItems();

        $finalFee = 0;
        foreach ($items as $item) {
            $finalFee += $item->getChildObject()->getFinalFee();
        }

        return $finalFee;
    }

    public function getPaidAmount()
    {
        return $this->getData('paid_amount');
    }

    public function getSavedAmount()
    {
        return $this->getData('saved_amount');
    }

    // ---------------------------------------

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getMagentoOrdersNumberMarketplacePrefix()
    {
        return strtoupper($this->getParentObject()->getMarketplace()->getChildObject()->getOriginCountry());
    }

    // ---------------------------------------

    public function getTaxReference()
    {
        return $this->getData('tax_reference');
    }

    public function getTaxDetails()
    {
        return $this->getSettings('tax_details');
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        return (float)$taxDetails['rate'];
    }

    /**
     * @return float
     */
    public function getTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();
        if (empty($taxDetails)) {
            return 0.0;
        }

        return (float)$taxDetails['amount'];
    }

    /**
     * @return bool
     */
    public function isShippingPriceHasTax()
    {
        if (!$this->hasTax()) {
            return false;
        }

        if ($this->isVatTax()) {
            return true;
        }

        $taxDetails = $this->getTaxDetails();
        return isset($taxDetails['includes_shipping']) ? (bool)$taxDetails['includes_shipping'] : false;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasTax()
    {
        $taxDetails = $this->getTaxDetails();
        return !empty($taxDetails['rate']);
    }

    /**
     * @return bool
     */
    public function isSalesTax()
    {
        if (!$this->hasTax()) {
            return false;
        }

        $taxDetails = $this->getTaxDetails();
        return !$taxDetails['is_vat'];
    }

    /**
     * @return bool
     */
    public function isVatTax()
    {
        if (!$this->hasTax()) {
            return false;
        }

        $taxDetails = $this->getTaxDetails();
        return $taxDetails['is_vat'];
    }

    // ---------------------------------------

    public function getWasteRecyclingFee()
    {
        $resultFee = 0.0;

        foreach ($this->getParentObject()->getItemsCollection() as $item) {
            /** @var Ess_M2ePro_Model_Ebay_Order_Item $ebayItem */
            $ebayItem = $item->getChildObject();

            $resultFee += $ebayItem->getWasteRecyclingFee();
        }

        return $resultFee;
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getShippingDetails()
    {
        return $this->getSettings('shipping_details');
    }

    /**
     * @return string
     */
    public function getShippingService()
    {
        $shippingDetails = $this->getShippingDetails();
        return isset($shippingDetails['service']) ? $shippingDetails['service'] : '';
    }

    /**
     * @return float
     */
    public function getShippingPrice()
    {
        $shippingDetails = $this->getShippingDetails();
        return isset($shippingDetails['price']) ? (float)$shippingDetails['price'] : 0.0;
    }

    /**
     * @return string
     */
    public function getShippingDate()
    {
        $shippingDetails = $this->getShippingDetails();
        return isset($shippingDetails['date']) ? $shippingDetails['date'] : '';
    }

    public function getShippingDateTo()
    {
        return $this->getData('shipping_date_to');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Order_ShippingAddress
     */
    public function getShippingAddress()
    {
        $shippingDetails = $this->getShippingDetails();
        $address = isset($shippingDetails['address']) ? $shippingDetails['address'] : array();

        return Mage::getModel('M2ePro/Ebay_Order_ShippingAddress', $this->getParentObject())
            ->setData($address);
    }

    /**
     * @return array
     */
    public function getShippingTrackingDetails()
    {
        /** @var Ess_M2ePro_Model_Order_Item[] $items */
        $items = $this->getParentObject()->getItemsCollection()->getItems();

        $trackingDetails = array();
        foreach ($items as $item) {
            $trackingDetails = array_merge($trackingDetails, $item->getChildObject()->getTrackingDetails());
        }

        $existedTrackingNumbers = array();

        foreach ($trackingDetails as $key => $trackingDetail) {
            if (in_array($trackingDetail['number'], $existedTrackingNumbers)) {
                unset($trackingDetails[$key]);
                continue;
            }

            $existedTrackingNumbers[] = $trackingDetail['number'];
        }

        return $trackingDetails;
    }

    /**
     * @return array
     */
    public function getGlobalShippingDetails()
    {
        $shippingDetails = $this->getShippingDetails();

        return isset($shippingDetails['global_shipping_details'])
            ? $shippingDetails['global_shipping_details'] : array();
    }

    /**
     * @return bool
     */
    public function isUseGlobalShippingProgram()
    {
        $details = $this->getGlobalShippingDetails();
        return !empty($details);
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Order_ShippingAddress
     */
    public function getGlobalShippingWarehouseAddress()
    {
        if (!$this->isUseGlobalShippingProgram()) {
            return null;
        }

        $globalShippingData = $this->getGlobalShippingDetails();
        $warehouseAddress = is_array($globalShippingData['warehouse_address'])
            ? $globalShippingData['warehouse_address'] : array();

        return Mage::getModel('M2ePro/Ebay_Order_ShippingAddress', $this->getParentObject())
            ->setData($warehouseAddress);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isUseClickAndCollect()
    {
        $clickEndCollectDetails = $this->getClickAndCollectDetails();
        return !empty($clickEndCollectDetails);
    }

    /**
     * @return array
     */
    public function getClickAndCollectDetails()
    {
        $shippingDetails = $this->getShippingDetails();

        return isset($shippingDetails['click_and_collect_details'])
            ? $shippingDetails['click_and_collect_details'] : array();
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getPaymentDetails()
    {
        return $this->getSettings('payment_details');
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        $paymentDetails = $this->getPaymentDetails();
        return isset($paymentDetails['method']) ? $paymentDetails['method'] : '';
    }

    /**
     * @return string
     */
    public function getPaymentDate()
    {
        $paymentDetails = $this->getPaymentDetails();
        return isset($paymentDetails['date']) ? $paymentDetails['date'] : '';
    }

    // ---------------------------------------

    public function getPurchaseUpdateDate()
    {
        return $this->getData('purchase_update_date');
    }

    public function getPurchaseCreateDate()
    {
        return $this->getData('purchase_create_date');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isCheckoutCompleted()
    {
        return (int)$this->getData('checkout_status') == self::CHECKOUT_STATUS_COMPLETED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isPaymentCompleted()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isPaymentMethodNotSelected()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_NOT_SELECTED;
    }

    /**
     * @return bool
     */
    public function isPaymentInProcess()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_PROCESS;
    }

    /**
     * @return bool
     */
    public function isPaymentFailed()
    {
        return (int)$this->getData('payment_status') == self::PAYMENT_STATUS_ERROR;
    }

    /**
     * @return bool
     */
    public function isPaymentStatusUnknown()
    {
        return !$this->isPaymentCompleted() &&
               !$this->isPaymentMethodNotSelected() &&
               !$this->isPaymentInProcess() &&
               !$this->isPaymentFailed();
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isShippingCompleted()
    {
        return (int)$this->getData('shipping_status') == self::SHIPPING_STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isShippingMethodNotSelected()
    {
        return (int)$this->getData('shipping_status') == self::SHIPPING_STATUS_NOT_SELECTED;
    }

    /**
     * @return bool
     */
    public function isShippingInProcess()
    {
        return (int)$this->getData('shipping_status') == self::SHIPPING_STATUS_PROCESSING;
    }

    /**
     * @return bool
     */
    public function isShippingStatusUnknown()
    {
        return !$this->isShippingCompleted() &&
               !$this->isShippingMethodNotSelected() &&
               !$this->isShippingInProcess();
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return (bool)$this->getData('cancellation_status');
    }

    // ---------------------------------------

    /**
     * @return float|int|null
     */
    public function getSubtotalPrice()
    {
        if ($this->_subTotalPrice === null) {
            $subtotal = 0;

            foreach ($this->getParentObject()->getItemsCollection() as $item) {
                /** @var $item Ess_M2ePro_Model_Order_Item */
                $subtotal += $item->getChildObject()->getPrice() * $item->getChildObject()->getQtyPurchased();
            }

            $this->_subTotalPrice = $subtotal;
        }

        return $this->_subTotalPrice;
    }

    /**
     * @return float|null
     */
    public function getGrandTotalPrice()
    {
        if ($this->_grandTotalPrice === null) {
            $this->_grandTotalPrice = $this->getSubtotalPrice();
            $this->_grandTotalPrice += round((float)$this->getShippingPrice(), 2);
            $this->_grandTotalPrice += round((float)$this->getTaxAmount(), 2);
            $this->_grandTotalPrice += round((float)$this->getWasteRecyclingFee(), 2);
        }

        return $this->_grandTotalPrice;
    }

    //########################################

    public function getStatusForMagentoOrder()
    {
        $status = '';
        $this->isCheckoutCompleted() && $status = $this->getEbayAccount()->getMagentoOrdersStatusNew();
        $this->isPaymentCompleted()  && $status = $this->getEbayAccount()->getMagentoOrdersStatusPaid();
        $this->isShippingCompleted() && $status = $this->getEbayAccount()->getMagentoOrdersStatusShipped();

        return $status;
    }

    //########################################

    /**
     * @return int|null
     */
    public function getAssociatedStoreId()
    {
        $storeId = null;

        $channelItems = $this->getParentObject()->getChannelItems();

        if (empty($channelItems)) {
            // Unmanaged order
            // ---------------------------------------
            $storeId = $this->getEbayAccount()->getMagentoOrdersListingsOtherStoreId();
            // ---------------------------------------
        } else {
            // M2E order
            // ---------------------------------------
            if ($this->getEbayAccount()->isMagentoOrdersListingsStoreCustom()) {
                $storeId = $this->getEbayAccount()->getMagentoOrdersListingsStoreId();
            } else {
                $firstChannelItem = reset($channelItems);
                $storeId = $firstChannelItem->getStoreId();
            }

            // ---------------------------------------
        }

        if ($storeId == 0) {
            $storeId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
        }

        return $storeId;
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateMagentoOrder()
    {
        if ($this->isCanceled()) {
            return false;
        }

        $ebayAccount = $this->getEbayAccount();

        if (!$this->isCheckoutCompleted() &&
            ($ebayAccount->shouldCreateMagentoOrderWhenCheckedOut() ||
             $ebayAccount->shouldCreateMagentoOrderWhenCheckedOutAndPaid())
        ) {
            return false;
        }

        if (!$this->isPaymentCompleted() && $ebayAccount->shouldCreateMagentoOrderWhenCheckedOutAndPaid()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isReservable()
    {
        return !$this->isCanceled();
    }

    //########################################

    public function beforeCreateMagentoOrder()
    {
        $buyerName = $this->getBuyerName();
        if (!empty($buyerName)) {
            return;
        }

        $buyerInfo = $this->getBuyerInfo();

        $shippingDetails = $this->getShippingDetails();
        $shippingDetails['address'] = $buyerInfo['address'];

        $buyerName = trim($buyerInfo['name']);

        $this->getParentObject()->setData('buyer_name', $buyerName);
        $this->getParentObject()->setSettings('shipping_details', $shippingDetails);

        $this->getParentObject()->save();
    }

    public function afterCreateMagentoOrder()
    {
        if ($this->getEbayAccount()->isMagentoOrdersCustomerNewNotifyWhenOrderCreated()) {
            if (method_exists($this->getParentObject()->getMagentoOrder(), 'queueNewOrderEmail')) {
                $this->getParentObject()->getMagentoOrder()->queueNewOrderEmail(false);
            } else {
                $this->getParentObject()->getMagentoOrder()->sendNewOrderEmail();
            }
        }
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreatePaymentTransaction()
    {
        if (!$this->hasExternalTransactions()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function createPaymentTransactions()
    {
        if (!$this->canCreatePaymentTransaction()) {
            return null;
        }

        /** @var $proxy Ess_M2ePro_Model_Ebay_Order_Proxy */
        $proxy = $this->getParentObject()->getProxy();
        $proxy->setStore($this->getParentObject()->getStore());

        foreach ($proxy->getPaymentTransactions() as $transaction) {
            try {
                /** @var $paymentTransactionBuilder Ess_M2ePro_Model_Magento_Order_PaymentTransaction */
                $paymentTransactionBuilder = Mage::getModel('M2ePro/Magento_Order_PaymentTransaction');
                $paymentTransactionBuilder->setMagentoOrder($this->getParentObject()->getMagentoOrder());
                $paymentTransactionBuilder->setData($transaction);
                $paymentTransactionBuilder->buildPaymentTransaction();
            } catch (Exception $e) {
                $this->getParentObject()->addErrorLog(
                    'Payment Transaction was not created. Reason: %msg%', array('msg' => $e->getMessage())
                );
            }
        }
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateInvoice()
    {
        if (!$this->isPaymentCompleted()) {
            return false;
        }

        if (!$this->getEbayAccount()->isMagentoOrdersInvoiceEnabled()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if ($magentoOrder->hasInvoices() || !$magentoOrder->canInvoice()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    /**
     * @return Mage_Sales_Model_Order_Invoice|null
     * @throws Exception
     */
    public function createInvoice()
    {
        if (!$this->canCreateInvoice()) {
            return null;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();

        /** @var $invoiceBuilder Ess_M2ePro_Model_Magento_Order_Invoice */
        $invoiceBuilder = Mage::getModel('M2ePro/Magento_Order_Invoice');
        $invoiceBuilder->setMagentoOrder($magentoOrder);
        $invoiceBuilder->buildInvoice();

        $invoice = $invoiceBuilder->getInvoice();

        if ($this->getEbayAccount()->isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()) {
            $invoice->sendEmail();
        }

        return $invoice;
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateShipment()
    {
        if (!$this->isShippingCompleted()) {
            return false;
        }

        if (!$this->getEbayAccount()->isMagentoOrdersShipmentEnabled()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if ($magentoOrder->hasShipments() || !$magentoOrder->canShip()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    /**
     * @return Mage_Sales_Model_Order_Shipment|null
     */
    public function createShipment()
    {
        if (!$this->canCreateShipment()) {
            return null;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();

        /** @var $shipmentBuilder Ess_M2ePro_Model_Magento_Order_Shipment */
        $shipmentBuilder = Mage::getModel('M2ePro/Magento_Order_Shipment');
        $shipmentBuilder->setMagentoOrder($magentoOrder);
        $shipmentBuilder->buildShipment();

        return $shipmentBuilder->getShipment();
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateTracks()
    {
        $trackingDetails = $this->getShippingTrackingDetails();
        if (empty($trackingDetails)) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if (!$magentoOrder->hasShipments()) {
            return false;
        }

        return true;
    }

    /**
     * @return array|null
     */
    public function createTracks()
    {
        if (!$this->canCreateTracks()) {
            return null;
        }

        $tracks = array();

        try {
            /** @var $trackBuilder Ess_M2ePro_Model_Magento_Order_Shipment_Track */
            $trackBuilder = Mage::getModel('M2ePro/Magento_Order_Shipment_Track');
            $trackBuilder->setMagentoOrder($this->getParentObject()->getMagentoOrder());
            $trackBuilder->setTrackingDetails($this->getShippingTrackingDetails());
            $trackBuilder->setSupportedCarriers(Mage::helper('M2ePro/Component_Ebay')->getCarriers());
            $trackBuilder->buildTracks();
            $tracks = $trackBuilder->getTracks();
        } catch (Exception $e) {
            $this->getParentObject()->addErrorLog(
                'Tracking details were not imported. Reason: %msg%', array('msg' => $e->getMessage())
            );
        }

        if (!empty($tracks)) {
            $this->getParentObject()->addSuccessLog('Tracking details were imported.');
        }

        return $tracks;
    }

    //########################################

    /**
     * @return bool
     */
    public function canUpdatePaymentStatus()
    {
        // ebay restriction
        if (stripos($this->getPaymentMethod(), 'paisa') !== false) {
            return false;
        }

        return !$this->isPaymentCompleted() && !$this->isPaymentStatusUnknown();
    }

    /**
     * @param array $params
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function updatePaymentStatus(array $params = array())
    {
        if (!$this->canUpdatePaymentStatus()) {
            return false;
        }

        Mage::getModel('M2ePro/Order_Change')->create(
            $this->getId(),
            Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_PAYMENT,
            $this->getParentObject()->getLog()->getInitiator(),
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $params
        );

        return true;
    }

    // ---------------------------------------

    /**
     * @param array $trackingDetails
     * @return bool
     */
    public function canUpdateShippingStatus(array $trackingDetails = array())
    {
        if ($this->isShippingStatusUnknown()) {
            return false;
        }

        // ebay restriction
        if (stripos($this->getPaymentMethod(), 'paisa') !== false) {
            return false;
        }

        if (!$this->isShippingMethodNotSelected() && !$this->isShippingInProcess() && empty($trackingDetails)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $trackingDetails
     * @param array $items
     *
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function updateShippingStatus(array $trackingDetails = array(), array $items = array())
    {
        if (!$this->canUpdateShippingStatus($trackingDetails)) {
            return false;
        }

        if (!empty($trackingDetails['carrier_code'])) {
            $trackingDetails['carrier_title'] = Mage::helper('M2ePro/Component_Ebay')->getCarrierTitle(
                $trackingDetails['carrier_code'],
                isset($trackingDetails['carrier_title']) ? $trackingDetails['carrier_title'] : ''
            );
        }

        if (!empty($trackingDetails['carrier_title'])) {
            if ($trackingDetails['carrier_title'] == Ess_M2ePro_Model_Order_Shipment_Handler::CUSTOM_CARRIER_CODE &&
                !empty($trackingDetails['shipping_method'])) {
                $trackingDetails['carrier_title'] = $trackingDetails['shipping_method'];
            }

            // remove unsupported by eBay symbols
            $trackingDetails['carrier_title'] = str_replace(
                array('\'', '"', '+', '(', ')'), array(), $trackingDetails['carrier_title']
            );
        }

        $params = $trackingDetails;
        foreach ($items as $item) {
            /** @var Ess_M2ePro_Model_Order_Item $item */
            $params['items'][] = array(
                'item_id' => $item->getId()
            );
        }

        /** @var Ess_M2ePro_Model_Order_Change $change */
        $change = Mage::getModel('M2ePro/Order_Change')->getCollection()
            ->addFieldToFilter('order_id', $this->getParentObject()->getId())
            ->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING)
            ->addFieldToFilter('processing_attempt_count', 0)
            ->getFirstItem();

        $existingParams = $change->getParams();

        $newTrackingNumber = !empty($trackingDetails['tracking_number']) ? $trackingDetails['tracking_number'] : '';
        $oldTrackingNumber = !empty($existingParams['tracking_number']) ? $existingParams['tracking_number'] : '';

        if (!$change->getId() || $newTrackingNumber !== $oldTrackingNumber) {
            $change::create(
                $this->getParentObject()->getId(),
                Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING,
                $this->getParentObject()->getLog()->getInitiator(),
                Ess_M2ePro_Helper_Component_Ebay::NICK,
                $params
            );
            return true;
        }

        $existingItems = array();
        if (isset($existingParams['items'])) {
            $existingItems = $existingParams['items'];
        }

        foreach ($params['items'] as $newItem) {
            foreach ($existingItems as $existingItem) {
                if ($newItem['item_id'] === $existingItem['item_id']) {
                    continue 2;
                }
            }

            $existingItems[] = $newItem;
        }

        $existingParams['items'] = $existingItems;

        $change->setData('params', Mage::helper('M2ePro')->jsonEncode($existingParams));
        $change->save();

        return true;
    }

    //########################################

    protected function getBuyerInfo()
    {
        /** @var Ess_M2ePro_Model_Order_Item $firstItem */
        $firstItem = $this->getParentObject()->getItemsCollection()->getFirstItem();

        $params = array(
            'item_id' => $firstItem->getChildObject()->getItemId(),
            'transaction_id' => $firstItem->getChildObject()->getTransactionId(),
        );

        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'orders', 'get', 'itemTransactions',
            $params, 'buyer_info',
            null, $this->getParentObject()->getAccount(), null
        );

        $dispatcherObj->process($connectorObj);
        $buyerInfo = $connectorObj->getResponseData();

        return $buyerInfo;
    }

    //########################################

    /**
     * @return bool
     */
    public function canRefund()
    {
        if ($this->isCanceled()) {
            return false;
        }

        if (!$this->getEbayAccount()->isRefundEnabled()) {
            return false;
        }

        if ($this->isMarketplaceNotSupportedForRefund()) {
            return false;
        }

        return true;
    }

    private function isMarketplaceNotSupportedForRefund()
    {
        $notSupportedMarketplacesForRefund = array(
            EbayHelper::MARKETPLACE_IN
        );

        return in_array($this->getParentObject()->getMarketplaceId(), $notSupportedMarketplacesForRefund);
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function cancel()
    {
        if (!$this->canRefund()) {
            return false;
        }

        $params = array(
            'channel_order_id' => $this->getEbayOrderId(),
            'reason'           => self::CANCEL_REASON_BUYER_ASK,
        );

        Mage::getModel('M2ePro/Order_Change')->create(
            $this->getParentObject()->getId(),
            Ess_M2ePro_Model_Order_Change::ACTION_CANCEL,
            $this->getParentObject()->getLog()->getInitiator(),
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $params
        );

        return true;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function refund()
    {
        if (!$this->canRefund()) {
            return false;
        }

        $params = array(
            'channel_order_id' => $this->getEbayOrderId(),
            'reason'           => self::CANCEL_REASON_BUYER_ASK,
        );

        Mage::getModel('M2ePro/Order_Change')->create(
            $this->getParentObject()->getId(),
            Ess_M2ePro_Model_Order_Change::ACTION_REFUND,
            $this->getParentObject()->getLog()->getInitiator(),
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $params
        );

        return true;
    }

    //########################################

    public function deleteInstance()
    {
        $table = Mage::getResourceModel('M2ePro/Ebay_Order_ExternalTransaction')->getMainTable();
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($table, array('order_id = ?'=>$this->getData('order_id')));

        return $this->delete();
    }

    //########################################
}
