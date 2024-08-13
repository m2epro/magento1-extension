<?php

/**
 * @method Ess_M2ePro_Model_Order getParentObject()
 * @method Ess_M2ePro_Model_Resource_Amazon_Order getResource()
 */
class Ess_M2ePro_Model_Amazon_Order extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const STATUS_PENDING                = 0;
    const STATUS_UNSHIPPED              = 1;
    const STATUS_SHIPPED_PARTIALLY      = 2;
    const STATUS_SHIPPED                = 3;
    const STATUS_UNFULFILLABLE          = 4;
    const STATUS_CANCELED               = 5;
    const STATUS_INVOICE_UNCONFIRMED    = 6;
    const STATUS_PENDING_RESERVED       = 7;
    const STATUS_CANCELLATION_REQUESTED = 8;

    const INVOICE_SOURCE_MAGENTO = 'magento';
    const INVOICE_SOURCE_EXTENSION = 'extension';

    const DATE_INTERVAL_IN_HOURS_FOR_INVOICE_SENDING_FROM_REPORT  = 24;

    protected $_subTotalPrice = null;

    protected $_grandTotalPrice = null;

    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Order');
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Order_Proxy
     */
    public function getProxy()
    {
        return Mage::getModel('M2ePro/Amazon_Order_Proxy', $this);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Account
     */
    public function getAmazonAccount()
    {
        return $this->getParentObject()->getAccount()->getChildObject();
    }

    //########################################

    public function getAmazonOrderId()
    {
        return $this->getData('amazon_order_id');
    }

    public function getBuyerName()
    {
        return $this->getData('buyer_name');
    }

    public function getBuyerEmail()
    {
        return $this->getData('buyer_email');
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getShippingService()
    {
        return $this->getData('shipping_service');
    }

    /**
     * @return float
     */
    public function getShippingPrice()
    {
        return (float)$this->getData('shipping_price');
    }

    public function getShippingAddress()
    {
        $address = Mage::helper('M2ePro')->jsonDecode($this->getData('shipping_address'));

        return Mage::getModel('M2ePro/Amazon_Order_ShippingAddress', $this->getParentObject())
            ->setData($address);
    }

    /**
     * @return array
     */
    public function getMerchantFulfillmentData()
    {
        return $this->getSettings('merchant_fulfillment_data');
    }

    //########################################

    public function getShippingDateTo()
    {
        return $this->getData('shipping_date_to');
    }

    public function getDeliveryDateTo()
    {
        return $this->getData('delivery_date_to');
    }

    //########################################

    /**
     * @return float
     */
    public function getPaidAmount()
    {
        return (float)$this->getData('paid_amount');
    }

    //########################################

    public function getIossNumber()
    {
        return $this->getData('ioss_number');
    }

    public function getTaxRegistrationId()
    {
        return $this->getData('tax_registration_id');
    }

    /**
     * @return bool
     */
    public function isBuyerRequestedCancel()
    {
        return (bool)$this->getData('is_buyer_requested_cancel');
    }

    public function getBuyerCancelReason()
    {
        return $this->getData('buyer_cancel_reason');
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getTaxDetails()
    {
        return $this->getSettings('tax_details');
    }

    /**
     * @return float
     */
    public function getProductPriceTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();
        return !empty($taxDetails['product']) ? (float)$taxDetails['product'] : 0.0;
    }

    /**
     * @return float
     */
    public function getShippingPriceTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();
        return !empty($taxDetails['shipping']) ? (float)$taxDetails['shipping'] : 0.0;
    }

    /**
     * @return float
     */
    public function getGiftPriceTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();
        return !empty($taxDetails['gift']) ? (float)$taxDetails['gift'] : 0.0;
    }

    /**
     * @return float|int
     */
    public function getProductPriceTaxRate()
    {
        $taxAmount = $this->getProductPriceTaxAmount() + $this->getGiftPriceTaxAmount();
        if ($taxAmount <= 0) {
            return 0;
        }

        if ($this->getSubtotalPrice() - $this->getPromotionDiscountAmount() <= 0) {
            return 0;
        }

        $taxRate = ($taxAmount / ($this->getSubtotalPrice() - $this->getPromotionDiscountAmount())) * 100;

        return round($taxRate, 4);
    }

    /**
     * @return float|int
     */
    public function getShippingPriceTaxRate()
    {
        $taxAmount = $this->getShippingPriceTaxAmount();
        if ($taxAmount <= 0) {
            return 0;
        }

        if ($this->getShippingPrice() - $this->getShippingDiscountAmount() <= 0) {
            return 0;
        }

        $taxRate = ($taxAmount / ($this->getShippingPrice() - $this->getShippingDiscountAmount())) * 100;

        return round($taxRate, 4);
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getDiscountDetails()
    {
        return $this->getSettings('discount_details');
    }

    /**
     * @return float
     */
    public function getPromotionDiscountAmount()
    {
        $discountDetails = $this->getDiscountDetails();
        return !empty($discountDetails['promotion']) ? $discountDetails['promotion'] : 0.0;
    }

    /**
     * @return float
     */
    public function getShippingDiscountAmount()
    {
        $discountDetails = $this->getDiscountDetails();
        return !empty($discountDetails['shipping']) ? $discountDetails['shipping'] : 0.0;
    }

    //########################################

    /**
     * @return bool
     */
    public function isFulfilledByAmazon()
    {
        return (bool)$this->getData('is_afn_channel');
    }

    //########################################

    public function isEligibleForMerchantFulfillment()
    {
        if ($this->isFulfilledByAmazon()) {
            return false;
        }

        if ($this->isPending() || $this->isCanceled()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Marketplace $amazonMarketplace */
        $amazonMarketplace = $this->getAmazonAccount()->getMarketplace()->getChildObject();
        if (!$amazonMarketplace->isMerchantFulfillmentAvailable()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isMerchantFulfillmentApplied()
    {
        $info = $this->getMerchantFulfillmentData();
        return !empty($info);
    }

    //########################################

    /**
     * @return bool
     */
    public function isPrime()
    {
        return (bool)$this->getData('is_prime');
    }

    /**
     * @return bool
     */
    public function isBusiness()
    {
        return (bool)$this->getData('is_business');
    }

    public function isReplacement()
    {
        return (bool)$this->getData('is_replacement');
    }

    //########################################

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->getStatus() == self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isUnshipped()
    {
        $status = $this->getStatus();

        return $status == self::STATUS_UNSHIPPED || $status == self::STATUS_CANCELLATION_REQUESTED;
    }

    /**
     * @return bool
     */
    public function isPartiallyShipped()
    {
        return $this->getStatus() == self::STATUS_SHIPPED_PARTIALLY;
    }

    /**
     * @return bool
     */
    public function isShipped()
    {
        return $this->getStatus() == self::STATUS_SHIPPED;
    }

    /**
     * @return bool
     */
    public function isUnfulfillable()
    {
        return $this->getStatus() == self::STATUS_UNFULFILLABLE;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->getStatus() == self::STATUS_CANCELED;
    }

    /**
     * @return bool
     */
    public function isInvoiceUnconfirmed()
    {
        return $this->getStatus() == self::STATUS_INVOICE_UNCONFIRMED;
    }

    /**
     * @return bool
     */
    public function isCancellationRequested()
    {
        return $this->getStatus() == self::STATUS_CANCELLATION_REQUESTED;
    }

    //----------------------------------

    /**
     * @return bool
     */
    public function isInvoiceSent()
    {
        return (bool)$this->getData(Ess_M2ePro_Model_Resource_Amazon_Order::COLUMN_IS_INVOICE_SENT);
    }

    /**
     * @return bool
     */
    public function isExistsDateOfInvoiceSent()
    {
        return $this->getData(Ess_M2ePro_Model_Resource_Amazon_Order::COLUMN_DATE_OF_INVOICE_SENDING) !== null;
    }

    /**
     * @return \DateTime
     */
    public function getDateOfInvoiceSent()
    {
        if (!$this->isExistsDateOfInvoiceSent()) {
            throw new \LogicException('Date Of Invoice Sent is not set');
        }

        return Mage::helper('M2ePro')->createGmtDateTime(
            $this->getData(Ess_M2ePro_Model_Resource_Amazon_Order::COLUMN_DATE_OF_INVOICE_SENDING)
        );
    }

    /**
     * @return $this
     */
    public function markThatInvoiceSentToChannel()
    {
        $this->setData(Ess_M2ePro_Model_Resource_Amazon_Order::COLUMN_IS_INVOICE_SENT, 1);
        $this->setData(
            Ess_M2ePro_Model_Resource_Amazon_Order::COLUMN_DATE_OF_INVOICE_SENDING,
            Mage::helper('M2ePro')->createCurrentGmtDateTime()->format('Y-m-d H:i:s')
        );

        return $this;
    }


    //----------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrderIdAppliedToAmazonOrder()
    {
        $realMagentoOrderId = $this->getData('seller_order_id');
        return empty($realMagentoOrderId);
    }

    /**
     * @return string
     */
    public function getSellerOrderId()
    {
        return $this->getData('seller_order_id');
    }

    //########################################

    /**
     * @return float|null
     */
    public function getSubtotalPrice()
    {
        if ($this->_subTotalPrice === null) {
            $this->_subTotalPrice = $this->getResource()->getItemsTotal($this->getId());
        }

        return $this->_subTotalPrice;
    }

    /**
     * @return float
     */
    public function getGrandTotalPrice()
    {
        if ($this->_grandTotalPrice === null) {
            $this->_grandTotalPrice = $this->getSubtotalPrice();
            $this->_grandTotalPrice += $this->getProductPriceTaxAmount();
            $this->_grandTotalPrice += $this->getShippingPrice();
            $this->_grandTotalPrice += $this->getShippingPriceTaxAmount();
            $this->_grandTotalPrice += $this->getGiftPriceTaxAmount();
            $this->_grandTotalPrice -= $this->getPromotionDiscountAmount();
            $this->_grandTotalPrice -= $this->getShippingDiscountAmount();
        }

        return round($this->_grandTotalPrice, 2);
    }

    //########################################

    public function getStatusForMagentoOrder()
    {
        $status = '';
        $this->isUnshipped()        && $status = $this->getAmazonAccount()->getMagentoOrdersStatusProcessing();
        $this->isPartiallyShipped() && $status = $this->getAmazonAccount()->getMagentoOrdersStatusProcessing();
        $this->isShipped()          && $status = $this->getAmazonAccount()->getMagentoOrdersStatusShipped();

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
            $storeId = $this->getAmazonAccount()->getMagentoOrdersListingsOtherStoreId();
            // ---------------------------------------
        } else {
            // M2E Pro order
            // ---------------------------------------
            if ($this->getAmazonAccount()->isMagentoOrdersListingsStoreCustom()) {
                $storeId = $this->getAmazonAccount()->getMagentoOrdersListingsStoreId();
            } else {
                $firstChannelItem = reset($channelItems);
                $storeId = $firstChannelItem->getStoreId();
            }

            // ---------------------------------------
        }

        if ($this->isFulfilledByAmazon() && $this->getAmazonAccount()->isMagentoOrdersFbaStoreModeEnabled()) {
            $storeId = $this->getAmazonAccount()->getMagentoOrdersFbaStoreId();
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
    public function isReservable()
    {
        if ($this->isCanceled()) {
            return false;
        }

        if ($this->isFulfilledByAmazon() &&
            (!$this->getAmazonAccount()->isMagentoOrdersFbaModeEnabled() ||
             !$this->getAmazonAccount()->isMagentoOrdersFbaStockEnabled())
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check possibility for magento order creation
     *
     * @return bool
     */
    public function canCreateMagentoOrder()
    {
        if ($this->isPending() || $this->isCanceled()) {
            return false;
        }

        if ($this->isFulfilledByAmazon() && !$this->getAmazonAccount()->isMagentoOrdersFbaModeEnabled()) {
            return false;
        }

        return true;
    }

    //########################################

    public function beforeCreateMagentoOrder()
    {
        if ($this->isPending() || $this->isCanceled()) {
            throw new Ess_M2ePro_Model_Exception(
                'Magento Order Creation is not allowed for pending and canceled Amazon Orders.'
            );
        }
    }

    public function afterCreateMagentoOrder()
    {
        if ($this->getAmazonAccount()->isMagentoOrdersCustomerNewNotifyWhenOrderCreated()) {
            if (method_exists($this->getParentObject()->getMagentoOrder(), 'queueNewOrderEmail')) {
                $this->getParentObject()->getMagentoOrder()->queueNewOrderEmail(false);
            } else {
                $this->getParentObject()->getMagentoOrder()->sendNewOrderEmail();
            }
        }

        if ($this->isFulfilledByAmazon() && !$this->getAmazonAccount()->isMagentoOrdersFbaStockEnabled()) {
            Mage::dispatchEvent(
                'm2epro_amazon_fba_magento_order_place_after', array(
                    'magento_order' => $this->getParentObject()->getMagentoOrder()
                )
            );
        }
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateInvoice()
    {
        if (!$this->getAmazonAccount()->isMagentoOrdersInvoiceEnabled()) {
            return false;
        }

        if ($this->isPending() || $this->isCanceled() || $this->isUnfulfillable() || $this->isInvoiceUnconfirmed()) {
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

        // Create invoice
        // ---------------------------------------
        /** @var $invoiceBuilder Ess_M2ePro_Model_Magento_Order_Invoice */
        $invoiceBuilder = Mage::getModel('M2ePro/Magento_Order_Invoice');
        $invoiceBuilder->setMagentoOrder($magentoOrder);
        $invoiceBuilder->buildInvoice();
        // ---------------------------------------

        $invoice = $invoiceBuilder->getInvoice();

        if ($this->getAmazonAccount()->isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()) {
            $invoice->sendEmail();
        }

        $this->sendInvoice();

        return $invoice;
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateShipment()
    {
        if (!$this->getAmazonAccount()->isMagentoOrdersShipmentEnabled()) {
            return false;
        }

        if (!$this->isShipped()) {
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

        // Create shipment
        // ---------------------------------------
        /** @var $shipmentBuilder Ess_M2ePro_Model_Magento_Order_Shipment */
        $shipmentBuilder = Mage::getModel('M2ePro/Magento_Order_Shipment');
        $shipmentBuilder->setMagentoOrder($magentoOrder);
        $shipmentBuilder->buildShipment();
        // ---------------------------------------

        return $shipmentBuilder->getShipment();
    }

    //########################################

    /**
     * @param array $trackingDetails
     * @return bool
     */
    public function canUpdateShippingStatus(array $trackingDetails = array())
    {
        if ($this->isFulfilledByAmazon()) {
            return false;
        }

        if ($this->isPending() || $this->isCanceled()) {
            return false;
        }

        if ($this->isUnshipped() || $this->isPartiallyShipped()) {
            return true;
        }

        if (empty($trackingDetails)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $trackingDetails
     * @param array $items
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function updateShippingStatus(array $trackingDetails = array(), array $items = array())
    {
        if (!$this->canUpdateShippingStatus($trackingDetails)) {
            return false;
        }

        if (empty($trackingDetails['carrier_code'])
            && !$this->getAmazonAccount()->isUpdateWithoutTrackToMagentoOrder()) {
            return false;
        }

        if (!isset($trackingDetails['fulfillment_date'])) {
            $trackingDetails['fulfillment_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        }

        if (!empty($trackingDetails['carrier_code'])) {
            $trackingDetails['carrier_title'] = Mage::helper('M2ePro/Component_Amazon')->getCarrierTitle(
                $trackingDetails['carrier_code'],
                isset($trackingDetails['carrier_title']) ? $trackingDetails['carrier_title'] : ''
            );
        }

        if (
            !empty($trackingDetails['carrier_title'])
            && $trackingDetails['carrier_title'] == Ess_M2ePro_Model_Order_Shipment_Handler::CUSTOM_CARRIER_CODE
            && !empty($trackingDetails['shipping_method'])
        ) {
            $trackingDetails['carrier_title'] = $trackingDetails['shipping_method'];
        }

        $params = array_merge(
            array(
                'amazon_order_id'  => $this->getAmazonOrderId(),
                'fulfillment_date' => $trackingDetails['fulfillment_date'],
                'items'            => array()
            ),
            $trackingDetails
        );

        foreach ($items as $item) {
            if (!isset($item['amazon_order_item_id']) || !isset($item['qty'])) {
                continue;
            }

            if ((int)$item['qty'] <= 0) {
                continue;
            }

            $params['items'][] = array(
                'amazon_order_item_id' => $item['amazon_order_item_id'],
                'qty' => (int)$item['qty']
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
                Ess_M2ePro_Helper_Component_Amazon::NICK,
                $params
            );
            return true;
        }

        foreach ($params['items'] as $newItem) {
            foreach ($existingParams['items'] as &$existingItem) {
                if ($newItem['amazon_order_item_id'] === $existingItem['amazon_order_item_id']) {
                    $newQtyTotal = $newItem['qty'] + $existingItem['qty'];

                    $maxQtyTotal  = Mage::getModel('M2ePro/Amazon_Order_Item')->getCollection()
                        ->addFieldToFilter(
                            'amazon_order_item_id',
                            $existingItem['amazon_order_item_id']
                        )
                        ->getFirstItem()
                        ->getQtyPurchased();
                    $newQtyTotal >= $maxQtyTotal && $newQtyTotal = $maxQtyTotal;
                    $existingItem['qty'] = $newQtyTotal;
                    continue 2;
                }
            }

            unset($existingItem);
            $existingParams['items'][] = $newItem;
        }

        $change->setData('params', Mage::helper('M2ePro')->jsonEncode($existingParams));
        $change->save();

        return true;
    }

    //########################################

    /**
     * @return bool
     */
    public function canRefund()
    {
        if ($this->getStatus() == self::STATUS_CANCELED) {
            return false;
        }

        if (!$this->getAmazonAccount()->isRefundEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $items
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function refund(array $items = array())
    {
        if (!$this->canRefund()) {
            return false;
        }

        $params = array(
            'order_id' => $this->getAmazonOrderId(),
            'currency' => $this->getCurrency(),
            'items'    => $items,
        );

        $orderId     = $this->getParentObject()->getId();

        $action = Ess_M2ePro_Model_Order_Change::ACTION_CANCEL;
        if ($this->isShipped() || $this->isPartiallyShipped() ||
            $this->getParentObject()->isStatusUpdatingToShipped()
        ) {
            if (empty($items)) {
                $this->getParentObject()->addErrorLog(
                    'Amazon Order was not refunded. Reason: %msg%',
                    array('msg' => 'Refund request was not submitted.
                                    To be processed through Amazon API, the refund must be applied to certain products
                                    in an order. Please indicate the number of each line item, that need to be refunded,
                                    in Credit Memo form.')
                );
                return false;
            }

            $action = Ess_M2ePro_Model_Order_Change::ACTION_REFUND;
        }

        if ($action == Ess_M2ePro_Model_Order_Change::ACTION_CANCEL && $this->isCancellationRequested()) {
            $params['cancel_reason'] =
                Ess_M2ePro_Model_Amazon_Order_Creditmemo_Handler::AMAZON_REFUND_REASON_BUYER_CANCELED;
        }

        Mage::getModel('M2ePro/Order_Change')->create(
            $orderId,
            $action,
            $this->getParentObject()->getLog()->getInitiator(),
            Ess_M2ePro_Helper_Component_Amazon::NICK,
            $params
        );

        return true;
    }

    //########################################

    /**
     * @return bool
     */
    public function canSendMagentoCreditmemo()
    {
        if (!$this->getAmazonAccount()->getMarketplace()->getChildObject()->isVatCalculationServiceAvailable()) {
            return false;
        }

        if ($this->getAmazonAccount()->isAutoInvoicingDisabled()) {
            return false;
        }

        if (!$this->getAmazonAccount()->isUploadMagentoInvoices()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if (!$this->getParentObject()->getMagentoOrder()->hasCreditmemos()) {
            return false;
        }

        /** @var Mage_Sales_Model_Resource_Order_Creditmemo_Collection $creditmemos */
        $creditmemos = $this->getParentObject()->getMagentoOrder()->getCreditmemosCollection();
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = $creditmemos->getLastItem();

        if ($this->getGrandTotalPrice() !== round($creditmemo->getGrandTotal(), 2)) {
            return false;
        }

        return true;
    }

    public function sendCreditmemo()
    {
        if (!$this->canSendMagentoCreditmemo()) {
            return false;
        }

        $params = array(
            'invoice_source' => self::INVOICE_SOURCE_MAGENTO,
            'document_type' => Ess_M2ePro_Model_Amazon_Order_Invoice::DOCUMENT_TYPE_CREDIT_NOTE
        );

        $this->sendInvoiceDocument($params);

        return true;
    }

    //########################################

    /**
     * @return bool
     */
    public function canSendMagentoInvoice()
    {
        if (!$this->getAmazonAccount()->getMarketplace()->getChildObject()->isVatCalculationServiceAvailable()) {
            return false;
        }

        if ($this->getAmazonAccount()->isAutoInvoicingDisabled()) {
            return false;
        }

        if (!$this->getAmazonAccount()->isUploadMagentoInvoices()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if (!$magentoOrder->hasInvoices()) {
            return false;
        }

        /** @var Mage_Sales_Model_Resource_Order_Invoice_Collection $invoices */
        $invoices = $magentoOrder->getInvoiceCollection();
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = $invoices->getLastItem();

        if ($this->getGrandTotalPrice() !== round($invoice->getGrandTotal(), 2)) {
            return false;
        }

        return true;
    }

    public function sendInvoice()
    {
        if (!$this->canSendMagentoInvoice()) {
            return false;
        }

        $params = array(
            'invoice_source' => self::INVOICE_SOURCE_MAGENTO,
            'document_type' => Ess_M2ePro_Model_Amazon_Order_Invoice::DOCUMENT_TYPE_INVOICE
        );

        $this->sendInvoiceDocument($params);

        return true;
    }

    //########################################

    public function canSendInvoiceFromReport()
    {
        if (!$this->getAmazonAccount()->getMarketplace()->getChildObject()->isVatCalculationServiceAvailable()) {
            return false;
        }

        if (!$this->getAmazonAccount()->isVatCalculationServiceEnabled()) {
            return false;
        }

        if (!$this->getAmazonAccount()->isInvoiceGenerationByExtension()) {
            return false;
        }

        $reportData = $this->getSettings('invoice_data_report');
        if (empty($reportData)) {
            return false;
        }

        if (!$this->canSendInvoiceFromReportAgain()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function canSendInvoiceFromReportAgain()
    {
        if (!$this->isInvoiceSent()) {
            return true;
        }

        $limitDateOfInvoiceSent = Mage::helper('M2ePro')
            ->createCurrentGmtDateTime()
            ->modify(
                sprintf('-%d hours', self::DATE_INTERVAL_IN_HOURS_FOR_INVOICE_SENDING_FROM_REPORT)
            );

        if (
            $this->isExistsDateOfInvoiceSent()
            && $this->getDateOfInvoiceSent() > $limitDateOfInvoiceSent
        ) {
            return false;
        }

        return true;
    }

    public function sendInvoiceFromReport()
    {
        if (!$this->canSendInvoiceFromReport()) {
            return false;
        }

        $params = array(
            'invoice_source' => self::INVOICE_SOURCE_EXTENSION,
            'document_type' => ''
        );

        $this->sendInvoiceDocument($params);

        return true;
    }

    //########################################

    public function sendInvoiceDocument(array $params)
    {
        if (empty($params['invoice_source'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('invoice source param not found.');
        }

        Mage::getModel('M2ePro/Order_Change')->create(
            $this->getParentObject()->getId(),
            Ess_M2ePro_Model_Order_Change::ACTION_SEND_INVOICE,
            $this->getParentObject()->getLog()->getInitiator(),
            Ess_M2ePro_Helper_Component_Amazon::NICK,
            $params
        );

        return true;
    }

    //########################################

    public function deleteInstance()
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Order_Invoice_Collection $invoiceCollection */
        $invoiceCollection = $this->_activeRecordFactory->getObjectCollection('Amazon_Order_Invoice');
        $invoiceCollection->addFieldToFilter('order_id', $this->getId());
        foreach ($invoiceCollection->getItems() as $invoice) {
            $invoice->deleteInstance();
        }

        return $this->delete();
    }

    //########################################
}
