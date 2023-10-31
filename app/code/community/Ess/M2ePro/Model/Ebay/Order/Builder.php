<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Order_Helper as OrderHelper;

class Ess_M2ePro_Model_Ebay_Order_Builder extends Mage_Core_Model_Abstract
{
    const STATUS_NOT_MODIFIED = 0;
    const STATUS_NEW          = 1;
    const STATUS_UPDATED      = 2;

    const UPDATE_COMPLETED_CHECKOUT = 'completed_checkout';
    const UPDATE_COMPLETED_PAYMENT  = 'completed_payment';
    const UPDATE_COMPLETED_SHIPPING = 'completed_shipping';
    const UPDATE_CANCELLATION       = 'cancellation';
    const UPDATE_BUYER_MESSAGE      = 'buyer_message';
    const UPDATE_PAYMENT_DATA       = 'payment_data';
    const UPDATE_SHIPPING_TAX_DATA  = 'shipping_tax_data';
    const UPDATE_ITEMS_COUNT        = 'items_count';
    const UPDATE_EMAIL              = 'email';

    /** @var $_helper Ess_M2ePro_Model_Ebay_Order_Helper */
    protected $_helper;

    /** @var $order Ess_M2ePro_Model_Account */
    protected $_account;

    /** @var $_order Ess_M2ePro_Model_Order */
    protected $_order;

    /** @var Ess_M2ePro_Model_Order[] */
    protected $_relatedOrders = array();

    protected $_items = array();

    protected $_externalTransactions = array();

    protected $_status = self::STATUS_NOT_MODIFIED;

    protected $_updates = array();
    /** @var array<array{type:string, text:string}> */
    protected $_messages = array();

    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::getSingleton('M2ePro/Ebay_Order_Helper');
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @param array $data
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function initialize(Ess_M2ePro_Model_Account $account, array $data = array())
    {
        $this->_account = $account;

        $this->initializeData($data);
        $this->initializeMarketplace();
        $this->initializeOrder();
    }

    //########################################

    /**
     * @param array $data
     */
    protected function initializeData(array $data = array())
    {
        $this->setData('account_id', $this->_account->getId());

        $this->setData('ebay_order_id', $data['identifiers']['ebay_order_id']);
        $this->setData('selling_manager_id', $data['identifiers']['selling_manager_id']);

        $this->setData('order_status', $data['statuses']['order']);
        $this->setData('checkout_status', $this->_helper->getCheckoutStatus($data['statuses']['checkout']));

        $this->setData('purchase_update_date', $data['purchase_update_date']);
        $this->setData('purchase_create_date', $data['purchase_create_date']);

        $this->setData('paid_amount', (float)$data['selling']['paid_amount']);
        $this->setData('saved_amount', (float)$data['selling']['saved_amount']);
        $this->setData('currency', $data['selling']['currency']);
        $this->setData('tax_reference', $data['selling']['tax_reference']);

        if (!empty($data['messages']) && is_array($data['messages'])) {
            foreach ($data['messages'] as $message) {
                if (
                    empty($message['text'])
                    || empty($message['type'])
                ) {
                    continue;
                }

                $this->_messages[] = array(
                    'text' => $message['text'],
                    'type' => $message['type'],
                );
            }
        }

        if (empty($data['selling']['tax_details']) || !is_array($data['selling']['tax_details'])) {
            $this->setData('tax_details', null);
        } else {
            $this->setData('tax_details', $data['selling']['tax_details']);
        }

        $this->setData('buyer_user_id', trim($data['buyer']['user_id']));
        $this->setData('buyer_name', trim($data['buyer']['name']));
        $this->setData('buyer_email', trim($data['buyer']['email']));
        $this->setData('buyer_message', $data['buyer']['message']);
        $this->setData('buyer_tax_id', trim($data['buyer']['tax_id']));

        $this->_externalTransactions = $data['payment']['external_transactions'];
        unset($data['payment']['external_transactions']);

        $this->setData('payment_details', $data['payment']);

        $paymentStatus = $this->_helper->getPaymentStatus(
            $data['payment']['method'], $data['payment']['date'], $data['payment']['status']
        );
        $this->setData('payment_status', $paymentStatus);

        $this->setData('shipping_details', $data['shipping']);

        $this->setData('shipping_date_to', $data['handle_by_time']);

        $shippingStatus = $this->_helper->getShippingStatus(
            $data['shipping']['date'], !empty($data['shipping']['service'])
        );
        $this->setData('shipping_status', $shippingStatus);

        // ---------------------------------------

        $cancellationStatus = $data['statuses']['order'] == OrderHelper::EBAY_ORDER_STATUS_CANCELLED ? 1 : 0;
        $this->setData('cancellation_status', $cancellationStatus);

        // ---------------------------------------
        $this->_items = $data['items'];
    }

    //########################################

    protected function initializeMarketplace()
    {
        // Get first order item
        $item = reset($this->_items);

        if (empty($item['site'])) {
            return;
        }

        $shippingDetails = $this->getData('shipping_details');
        $paymentDetails = $this->getData('payment_details');

        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Marketplace', $item['site'], 'code');

        $shippingDetails['service'] = $this->_helper->getShippingServiceNameByCode(
            $shippingDetails['service'], $marketplace->getId()
        );
        $paymentDetails['method'] = $this->_helper->getPaymentMethodNameByCode(
            $paymentDetails['method'], $marketplace->getId()
        );

        $this->setData('marketplace_id', $marketplace->getId());
        $this->setData('shipping_details', $shippingDetails);
        $this->setData('payment_details', $paymentDetails);
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function initializeOrder()
    {
        $this->_status = self::STATUS_NOT_MODIFIED;

        $existOrders = $this->getExistedOrders();

        // New order
        // ---------------------------------------
        if (count($existOrders) == 0) {
            $this->_status = self::STATUS_NEW;
            $this->_order  = Mage::helper('M2ePro/Component_Ebay')->getModel('Order');
            $this->_order->setStatusUpdateRequired(true);

            if ($this->isCombined()) {
                $this->_relatedOrders = Mage::getResourceModel('M2ePro/Ebay_Order')->getOrdersContainingItemsFromOrder(
                    $this->_account->getId(), $this->_items
                );
            }

            return;
        }

        // ---------------------------------------

        // duplicated M2ePro orders. remove M2E Pro order without magento order id or newest order
        // ---------------------------------------
        if (count($existOrders) > 1) {
            $isDeleted = false;

            foreach ($existOrders as $key => $order) {

                $magentoOrderId = $order->getData('magento_order_id');
                if (!empty($magentoOrderId)) {
                    continue;
                }

                $order->deleteInstance();
                unset($existOrders[$key]);
                $isDeleted = true;
                break;
            }

            if (!$isDeleted) {
                $orderForRemove = reset($existOrders);
                $orderForRemove->deleteInstance();
            }
        }

        // ---------------------------------------

        // Already exist order
        // ---------------------------------------
        $this->_order  = reset($existOrders);
        $this->_status = self::STATUS_UPDATED;

        if ($this->_order->getMagentoOrderId() === null) {
            $this->_order->setStatusUpdateRequired(true);
        }

        // ---------------------------------------
    }

    /**
     * @return Ess_M2ePro_Model_Order[]
     */
    protected function getExistedOrders()
    {
        $orderIds = array($this->getData('ebay_order_id'));
        if ($this->getData('selling_manager_id') && $oldFormatId = $this->getOldFormatId()) {
            $orderIds[] = $oldFormatId;
        }

        /** @var Ess_M2ePro_Model_Resource_Order_Collection $existed */
        $existed = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order')
            ->addFieldToFilter('account_id', $this->_account->getId())
            ->setOrder('id', Varien_Data_Collection_Db::SORT_ORDER_DESC);

        $whereExpression = sprintf(
            "ebay_order_id IN (%s)",
            implode(',', array_map(function ($orderId) {
                return "'$orderId'";
            }, $orderIds))
        );

        if ($this->getData('selling_manager_id')) {
            $whereExpression .= sprintf(' OR selling_manager_id = %s', $this->getData('selling_manager_id'));
        }

        $existed->getSelect()->where($whereExpression);

        return $existed->getItems();
    }

    /**
     * @return string|null
     */
    private function getOldFormatId()
    {
        $transactionIds = array();
        foreach ($this->_items as $item) {
            $transactionIds[] = $item['transaction_id'];
        }

        /**
         * Transaction ID will be 0 for an auction item
         */
        $transactionIds = array_filter($transactionIds);
        if (empty($transactionIds)) {
            return null;
        }

        /** @var Ess_M2ePro_Model_Resource_Order_Item_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order_Item');
        $collection->getSelect()->joinInner(
            array('e_order' => Mage::getResourceModel('M2ePro/Ebay_Order')->getMainTable()),
            'e_order.order_id = main_table.order_id',
            array('ebay_order_id' => 'ebay_order_id')
        );
        $collection->addFieldToFilter('ebay_order_id', array('neq' => $this->getData('ebay_order_id')));
        $collection->addFieldToFilter('e_order.selling_manager_id', array('neq' => 0));
        $collection->addFieldToFilter('transaction_id', array('in' => $transactionIds));

        $possibleOldFormatIds = array_unique($collection->getColumnValues('ebay_order_id'));
        if (count($possibleOldFormatIds) === 1) {
            return reset($possibleOldFormatIds);
        }

        return null;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function process()
    {
        if (!$this->canCreateOrUpdateOrder()) {
            return null;
        }

        $this->checkUpdates();

        $this->createOrUpdateOrder();
        $this->createOrUpdateItems();
        $this->createOrUpdateExternalTransactions();

        $finalFee = $this->_order->getChildObject()->getFinalFee();
        $magentoOrder = $this->_order->getMagentoOrder();

        if (!empty($finalFee) && !empty($magentoOrder) && $magentoOrder->getPayment()) {
            $paymentAdditionalData = Mage::helper('M2ePro')->unserialize(
                $magentoOrder->getPayment()->getAdditionalData()
            );
            if (is_array($paymentAdditionalData) && !empty($paymentAdditionalData)) {
                $paymentAdditionalData['channel_final_fee'] = $finalFee;
                $magentoOrder->getPayment()->setAdditionalData(
                    Mage::helper('M2ePro')->serialize($paymentAdditionalData)
                );
                $magentoOrder->getPayment()->save();
            }
        }

        if ($this->isNew()) {
            $this->processNew();
        }

        if ($this->isUpdated()) {
            $this->processOrderUpdates();
            $this->processMagentoOrderUpdates();
        }

        return $this->_order;
    }

    //########################################

    protected function createOrUpdateItems()
    {
        $itemsCollection = $this->_order->getItemsCollection();
        $itemsCollection->load();

        foreach ($this->_items as $itemData) {
            $itemData['order_id'] = $this->_order->getId();

            /** @var $itemBuilder Ess_M2ePro_Model_Ebay_Order_Item_Builder */
            $itemBuilder = Mage::getModel('M2ePro/Ebay_Order_Item_Builder');
            $itemBuilder->initialize($itemData);

            $item = $itemBuilder->process();
            $item->setOrder($this->_order);

            $itemsCollection->removeItemByKey($item->getId());
            $itemsCollection->addItem($item);
        }
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function createOrUpdateExternalTransactions()
    {
        $externalTransactionsCollection = $this->_order->getChildObject()->getExternalTransactionsCollection();
        $externalTransactionsCollection->load();

        $paymentTransactionId = '';
        foreach ($this->_externalTransactions as $transactionData) {
            if (!empty($transactionData['transaction_id'])) {
                $paymentTransactionId = $transactionData['transaction_id'];
                break;
            }
        }

        $postfix = 0;
        foreach ($this->_externalTransactions as $transactionData) {
            $transactionData['order_id'] = $this->_order->getId();
            // transaction_id may be empty for refunded transaction
            if (empty($transactionData['transaction_id'])) {
                $transactionData['transaction_id'] = $paymentTransactionId . '-' . ++$postfix;
            }

            /** @var $transactionBuilder Ess_M2ePro_Model_Ebay_Order_ExternalTransaction_Builder */
            $transactionBuilder = Mage::getModel('M2ePro/Ebay_Order_ExternalTransaction_Builder');
            $transactionBuilder->initialize($transactionData);

            $transaction = $transactionBuilder->process();
            $transaction->setOrder($this->_order);

            $externalTransactionsCollection->removeItemByKey($transaction->getId());
            $externalTransactionsCollection->addItem($transaction);
        }
    }

    //########################################

    /**
     * @return bool
     */
    public function isSingle()
    {
        return count($this->_items) == 1;
    }

    /**
     * @return bool
     */
    public function isCombined()
    {
        return count($this->_items) > 1;
    }

    // ---------------------------------------

    protected function hasExternalTransactions()
    {
        return !empty($this->_externalTransactions);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->_status == self::STATUS_NEW;
    }

    /**
     * @return bool
     */
    public function isUpdated()
    {
        return $this->_status == self::STATUS_UPDATED;
    }

    //########################################

    /**
     * @return bool
     */
    protected function canCreateOrUpdateOrder()
    {
        if ($this->_order->getId()) {
            $newPurchaseUpdateDate = new DateTime(
                $this->getData('purchase_update_date'),
                new DateTimeZone('UTC')
            );
            $oldPurchaseUpdateDate = new DateTime(
                $this->_order->getChildObject()->getPurchaseUpdateDate(),
                new DateTimeZone('UTC')
            );

            if ($newPurchaseUpdateDate < $oldPurchaseUpdateDate) {
                return false;
            }
        }

        /**
         * Don't create combined order without payment
         */
        if ($this->isCombined()
            && $this->getData('payment_status') === Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_NOT_SELECTED
        ) {
            return false;
        }

        if ($this->getData('order_status') == OrderHelper::EBAY_ORDER_STATUS_CANCELLED &&
            $this->_order->getId() &&
            !$this->_order->getChildObject()->isCanceled()) {
            return true;
        }

        if ($this->getData('checkout_status') == Ess_M2ePro_Model_Ebay_Order::CHECKOUT_STATUS_COMPLETED) {
            return true;
        }

        if ($this->getData('order_status') == OrderHelper::EBAY_ORDER_STATUS_INACTIVE) {
            return false;
        }

        if (empty($this->relatedOrders)) {
            return true;
        }

        if (count($this->_relatedOrders) == 1) {
            /** @var Ess_M2ePro_Model_Order $relatedOrder */
            $relatedOrder = reset($this->_relatedOrders);

            if ($relatedOrder->getItemsCollection()->getSize() == count($this->_items)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function createOrUpdateOrder()
    {
        $this->prepareShippingAddress();

        $this->setData('tax_details', Mage::helper('M2ePro')->jsonEncode($this->getData('tax_details')));
        $this->setData('shipping_details', Mage::helper('M2ePro')->jsonEncode($this->getData('shipping_details')));
        $this->setData('payment_details', Mage::helper('M2ePro')->jsonEncode($this->getData('payment_details')));

        foreach ($this->getData() as $key => $value) {
            if (!$this->_order->getId() || ($this->_order->hasData($key) && $this->_order->getData($key) != $value)) {
                $this->_order->addData($this->getData());
                $this->_order->save();
                break;
            }
        }

        if (
            $this->_order->getId()
            && !empty($this->_messages)
        ) {
            $orderLog = $this->_order->getLog();

            foreach ($this->_messages as $message) {
                $orderLog->addMessage(
                    $this->_order,
                    $message['text'],
                    $orderLog->convertServerMessageTypeToExtensionMessageType($message['type']),
                    array(),
                    true
                );
            }
        }

        $this->_order->setAccount($this->_account);

        if ($this->getData('order_status') == OrderHelper::EBAY_ORDER_STATUS_CANCELLED) {
            if ($this->_order->getReserve()->isPlaced()) {
                $this->_order->getReserve()->cancel();
            }

            if ($this->_order->getMagentoOrder() !== null && !$this->_order->getMagentoOrder()->isCanceled()) {
                $this->_order->cancelMagentoOrder();
            }
        }
    }

    protected function prepareShippingAddress()
    {
        $shippingDetails = $this->getData('shipping_details');
        $shippingAddress = $shippingDetails['address'];

        $shippingAddress['company'] = '';

        if (!isset($shippingAddress['street']) || !is_array($shippingAddress['street'])) {
            $shippingAddress['street'] = array();
        }

        $shippingAddress['street'] = array_filter($shippingAddress['street']);

        $group = '/ebay/order/settings/marketplace_'.(int)$this->getData('marketplace_id').'/';
        $useFirstStreetLineAsCompany = Mage::helper('M2ePro/Module')
            ->getConfig()
                ->getGroupValue($group, 'use_first_street_line_as_company');

        if ($useFirstStreetLineAsCompany && count($shippingAddress['street']) > 1) {
            $shippingAddress['company'] = array_shift($shippingAddress['street']);
        }

        $shippingDetails['address'] = $shippingAddress;
        $this->setData('shipping_details', $shippingDetails);
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processNew()
    {
        if (!$this->isNew()) {
            return;
        }

        if ($this->isCombined()) {
            $this->processOrdersContainingItemsFromCurrentOrder();
        }

        /** @var $ebayAccount Ess_M2ePro_Model_Ebay_Account */
        $ebayAccount = $this->_account->getChildObject();

        if ($this->_order->hasListingItems() && !$ebayAccount->isMagentoOrdersListingsModeEnabled()) {
            return;
        }

        if ($this->_order->hasOtherListingItems() && !$ebayAccount->isMagentoOrdersListingsOtherModeEnabled()) {
            return;
        }

        if (!$this->_order->getChildObject()->canCreateMagentoOrder()) {
            $this->_order->addWarningLog(
                'Magento Order was not created. Reason: %msg%', array(
                'msg' => 'Order Creation Rules were not met. ' .
                         'Press Create Order Button at Order View Page to create it anyway.'
                )
            );
            return;
        }
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processOrdersContainingItemsFromCurrentOrder()
    {
        foreach ($this->_relatedOrders as $relatedOrder) {
            if ($relatedOrder->canCancelMagentoOrder() === true) {
                $relatedOrder->addWarningLog(
                    'Magento Order #%order_id% should be canceled ' .
                    'as new combined eBay order #%new_id% was created.',
                    array(
                        '!order_id' => $relatedOrder->getMagentoOrder()->getRealOrderId(),
                        '!new_id' => $this->_order->getData('ebay_order_id')
                    )
                );

                try {
                    $relatedOrder->cancelMagentoOrder();
                } catch (Exception $e) {
                    Mage::helper('M2ePro/Module_Exception')->process($e);
                }
            }

            $relatedOrder->getReserve()->cancel();
            $relatedOrder->getChildObject()->setData('cancellation_status', 1);
            $relatedOrder->getChildObject()->save();

            $relatedOrder->addWarningLog(
                'eBay order #%old_id% was canceled. A new combined eBay order #%new_id% was created.',
                array(
                    '!old_id' => $relatedOrder->getChildObject()->getEbayOrderId(),
                    '!new_id' => $this->_order->getChildObject()->getEbayOrderId(),
                )
            );
        }

        $this->logOrdersContainingItemsFromCurrentOrder();
        $this->logUnpaidOrdersContainingItemsFromCurrentOrder();
    }

    private function logOrdersContainingItemsFromCurrentOrder()
    {
        $ebayOrderIds = array_map(function (Ess_M2ePro_Model_Order $order) {
            return $order->getChildObject()->getEbayOrderId();
        }, $this->_relatedOrders);

        if (!empty($ebayOrderIds)) {
            $this->_order->addWarningLog(
                'Combined eBay order #%new_id% was created for canceled eBay order(s) #%old_ids%.',
                array(
                    '!old_ids' => implode(', ', $ebayOrderIds),
                    '!new_id' => $this->_order->getChildObject()->getEbayOrderId(),
                )
            );
        }
    }

    private function logUnpaidOrdersContainingItemsFromCurrentOrder()
    {
        $ebayOrderIds = array();
        foreach ($this->_relatedOrders as $relatedOrder) {
            if ($this->_order->getChildObject()->isPaymentCompleted() &&
                !$relatedOrder->getChildObject()->isPaymentCompleted()
            ) {
                $ebayOrderIds[] = $relatedOrder->getChildObject()->getEbayOrderId();
            }
        }

        if (!empty($ebayOrderIds)) {
            $this->_order->addWarningLog(
                'This combined order was created for unpaid eBay order(s) #%ids%.' .
                ' Please check the order details on eBay.',
                array('!ids' => implode(', ', $ebayOrderIds))
            );
        }
    }

    //########################################

    protected function checkUpdates()
    {
        if (!$this->isUpdated()) {
            return;
        }

        if ($this->hasUpdatedCancellationStatus()) {
            $this->_updates[] = self::UPDATE_CANCELLATION;
        }

        if ($this->hasUpdatedCompletedCheckout()) {
            $this->_updates[] = self::UPDATE_COMPLETED_CHECKOUT;
        }

        if ($this->hasUpdatedBuyerMessage()) {
            $this->_updates[] = self::UPDATE_BUYER_MESSAGE;
        }

        if ($this->hasUpdatedCompletedPayment()) {
            $this->_updates[] = self::UPDATE_COMPLETED_PAYMENT;
        }

        if ($this->hasUpdatedPaymentData()) {
            $this->_updates[] = self::UPDATE_PAYMENT_DATA;
        }

        if ($this->hasUpdatedShippingTaxData()) {
            $this->_updates[] = self::UPDATE_SHIPPING_TAX_DATA;
        }

        if ($this->hasUpdatedCompletedShipping()) {
            $this->_updates[] = self::UPDATE_COMPLETED_SHIPPING;
        }

        if ($this->hasUpdatedItemsCount()) {
            $this->_updates[] = self::UPDATE_ITEMS_COUNT;
        }

        if ($this->hasUpdatedEmail()) {
            $this->_updates[] = self::UPDATE_EMAIL;
        }
    }

    // ---------------------------------------

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function hasUpdatedCompletedCheckout()
    {
        if (!$this->isUpdated() || $this->_order->getChildObject()->isCheckoutCompleted()) {
            return false;
        }

        return $this->getData('checkout_status') == Ess_M2ePro_Model_Ebay_Order::CHECKOUT_STATUS_COMPLETED;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function hasUpdatedBuyerMessage()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        if ($this->getData('buyer_message') == '') {
            return false;
        }

        return $this->getData('buyer_message') != $this->_order->getChildObject()->getBuyerMessage();
    }

    // ---------------------------------------

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function hasUpdatedCompletedPayment()
    {
        if (!$this->isUpdated() || $this->_order->getChildObject()->isPaymentCompleted()) {
            return false;
        }

        return $this->getData('payment_status') == Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED;
    }

    // ---------------------------------------

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function hasUpdatedCompletedShipping()
    {
        if (!$this->isUpdated() || $this->_order->getChildObject()->isShippingCompleted()) {
            return false;
        }

        return $this->getData('shipping_status') == Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED;
    }

    // ---------------------------------------

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function hasUpdatedPaymentData()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        /** @var $ebayOrder Ess_M2ePro_Model_Ebay_Order */
        $ebayOrder = $this->_order->getChildObject();
        $paymentDetails = $this->getData('payment_details');

        if ($ebayOrder->getPaymentMethod() != $paymentDetails['method']) {
            return true;
        }

        if (!$ebayOrder->hasExternalTransactions() && $this->hasExternalTransactions()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function hasUpdatedShippingTaxData()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        /** @var $ebayOrder Ess_M2ePro_Model_Ebay_Order */
        $ebayOrder = $this->_order->getChildObject();
        $shippingDetails = $this->getData('shipping_details');
        $taxDetails      = $this->getData('tax_details');

        if (!empty($shippingDetails['price']) && $shippingDetails['price'] != $ebayOrder->getShippingPrice() ||
            !empty($shippingDetails['service']) && $shippingDetails['service'] != $ebayOrder->getShippingService())
        {
            return true;
        }

        if ((!empty($taxDetails['rate']) && $taxDetails['rate'] != $ebayOrder->getTaxRate()) ||
            (!empty($taxDetails['amount']) && $taxDetails['amount'] != $ebayOrder->getTaxAmount()))
        {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function hasUpdatedCancellationStatus()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        $oldStatus = $this->_order->getChildObject()->getData('cancellation_status');
        $newStatus = $this->getData('cancellation_status');

        if ($newStatus == 1 && ($oldStatus != $newStatus)) {
            return true;
        }

        return false;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    protected function hasUpdatedItemsCount()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        return count($this->_items) != $this->_order->getItemsCollection()->getSize();
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    protected function hasUpdatedEmail()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        $newEmail = $this->getData('buyer_email');
        $oldEmail = $this->_order->getData('buyer_email');

        if ($newEmail == $oldEmail) {
            return false;
        }

        return filter_var($newEmail, FILTER_VALIDATE_EMAIL) !== false;
    }

    //########################################

    /**
     * @return bool
     */
    protected function hasUpdates()
    {
        return !empty($this->_updates);
    }

    /**
     * @param $update
     * @return bool
     */
    protected function hasUpdate($update)
    {
        return in_array($update, $this->_updates);
    }

    protected function processOrderUpdates()
    {
        if (!$this->hasUpdates()) {
            return;
        }

        if ($this->hasUpdate(self::UPDATE_COMPLETED_CHECKOUT)) {
            $this->_order->addSuccessLog('Buyer has completed checkout on eBay.');
            $this->_order->setStatusUpdateRequired(true);
        }

        if ($this->hasUpdate(self::UPDATE_COMPLETED_PAYMENT)) {
            $this->_order->addSuccessLog('Payment status was updated to Paid on eBay.');
            $this->_order->setStatusUpdateRequired(true);
        }

        if ($this->hasUpdate(self::UPDATE_COMPLETED_SHIPPING)) {
            $this->_order->addSuccessLog('Shipping status was updated to Shipped on eBay.');
            $this->_order->setStatusUpdateRequired(true);
        }

        if ($this->hasUpdate(self::UPDATE_CANCELLATION)) {
            $this->_order->addSuccessLog('Seller canceled order on eBay.');
            $this->_order->setStatusUpdateRequired(true);
        }

        if ($this->hasUpdate(self::UPDATE_SHIPPING_TAX_DATA) && $this->_order->getMagentoOrderId()) {
            $message  = 'Attention! Shipping/Tax details have been modified on the channel. ';
            $message .= 'Magento order is already created and cannot be updated to reflect these changes.';
            $this->_order->addWarningLog($message);
        }

        if ($this->hasUpdate(self::UPDATE_ITEMS_COUNT) && $this->_order->getMagentoOrderId()) {
            $message  = 'Attention! The number of ordered Items has been modified on the channel. ';
            $message .= 'Magento order is already created and cannot be updated to reflect these changes.';
            $this->_order->addWarningLog($message);
        }
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processMagentoOrderUpdates()
    {
        if (!$this->hasUpdates()) {
            return;
        }

        $magentoOrder = $this->_order->getMagentoOrder();
        if ($magentoOrder === null) {
            return;
        }

        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($magentoOrder);

        $proxy = $this->_order->getProxy();
        $proxy->setStore($this->_order->getStore());

        if ($this->hasUpdate(self::UPDATE_PAYMENT_DATA)) {
            $magentoOrderUpdater->updatePaymentData($proxy->getPaymentData());
        }

        if ($this->hasUpdate(self::UPDATE_COMPLETED_CHECKOUT)) {
            $magentoOrderUpdater->updateShippingAddress($proxy->getAddressData());
            $magentoOrderUpdater->updateCustomerAddress($proxy->getAddressData());
        }

        if ($this->hasUpdate(self::UPDATE_BUYER_MESSAGE)) {
            $magentoOrderUpdater->updateComments($proxy->getChannelComments());
        }

        if ($this->hasUpdate(self::UPDATE_EMAIL)) {
            $magentoOrderUpdater->updateCustomerEmail($this->_order->getChildObject()->getBuyerEmail());
        }

        $magentoOrderUpdater->finishUpdate();
    }
}
