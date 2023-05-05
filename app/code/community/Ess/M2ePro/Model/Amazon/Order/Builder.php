<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Builder extends Mage_Core_Model_Abstract
{
    const INSTRUCTION_INITIATOR = 'order_builder';

    const STATUS_NOT_MODIFIED = 0;
    const STATUS_NEW          = 1;
    const STATUS_UPDATED      = 2;

    const UPDATE_STATUS = 'status';
    const UPDATE_EMAIL   = 'email';
    const UPDATE_B2B_VAT = 'b2b_vat';

    /** @var $_helper Ess_M2ePro_Model_Amazon_Order_Helper */
    protected $_helper = null;

    /** @var $order Ess_M2ePro_Model_Account */
    protected $_account = null;

    /** @var $_order Ess_M2ePro_Model_Order */
    protected $_order = null;

    protected $_status = self::STATUS_NOT_MODIFIED;

    protected $_items = array();

    protected $_updates = array();

    //########################################

    public function __construct()
    {
        $this->_helper = Mage::getSingleton('M2ePro/Amazon_Order_Helper');
    }

    //########################################

    public function initialize(Ess_M2ePro_Model_Account $account, array $data = array())
    {
        $this->_account = $account;

        $this->initializeData($data);
        $this->initializeOrder();
    }

    //########################################

    protected function initializeData(array $data = array())
    {
        // Init general data
        // ---------------------------------------
        $this->setData('account_id', $this->_account->getId());
        $this->setData('amazon_order_id', $data['amazon_order_id']);
        $this->setData('seller_order_id', $data['seller_order_id']);
        $this->setData('marketplace_id', $data['marketplace_id']);

        $this->setData('status', $this->_helper->getStatus($data['status']));
        $this->setData('is_afn_channel', $data['is_afn_channel']);
        $this->setData('is_prime', $data['is_prime']);
        $this->setData('is_business', $data['is_business']);
        $this->setData('is_replacement', $data['is_replacement']);

        $this->setData('purchase_update_date', $data['purchase_update_date']);
        $this->setData('purchase_create_date', $data['purchase_create_date']);

        $this->setData('is_buyer_requested_cancel', $data['is_buyer_requested_cancel']);
        $this->setData('buyer_cancel_reason', $data['buyer_cancel_reason']);
        if ($data['is_buyer_requested_cancel'] &&
            $this->getData('status') == Ess_M2ePro_Model_Amazon_Order::STATUS_UNSHIPPED
        ) {
            $this->setData('status', Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELLATION_REQUESTED);
        }

        // ---------------------------------------

        // Init sale data
        // ---------------------------------------
        $this->setData('paid_amount', (float)$data['paid_amount']);
        $this->setData('tax_details', Mage::helper('M2ePro')->jsonEncode($this->prepareTaxDetails($data)));
        $this->setData('ioss_number', $data['items'][0]['ioss_number']);
        $this->setData('tax_registration_id', $this->prepareTaxRegistrationId($data));
        $this->setData('discount_details', Mage::helper('M2ePro')->jsonEncode($data['discount_details']));
        $this->setData('currency', $data['currency']);
        $this->setData('qty_shipped', $data['qty_shipped']);
        $this->setData('qty_unshipped', $data['qty_unshipped']);
        // ---------------------------------------

        // Init customer/shipping data
        // ---------------------------------------
        $this->setData('buyer_name', $data['buyer_name']);
        $this->setData('buyer_email', $data['buyer_email']);
        $this->setData('shipping_service', $data['shipping_service']);
        $this->setData('shipping_address', $data['shipping_address']);
        $this->setData('shipping_price', (float)$data['shipping_price']);
        $this->setData('shipping_date_to', $data['shipping_date_to']);
        $this->setData('delivery_date_to', $data['delivery_date_to']);
        // ---------------------------------------

        $this->_items = $data['items'];
    }

    //########################################

    protected function initializeOrder()
    {
        $this->_status = self::STATUS_NOT_MODIFIED;

        $existOrders = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Order')
            ->addFieldToFilter('account_id', $this->_account->getId())
            ->addFieldToFilter('amazon_order_id', $this->getData('amazon_order_id'))
            ->setOrder('id', Varien_Data_Collection_Db::SORT_ORDER_DESC)
            ->getItems();
        $existOrdersNumber = count($existOrders);

        // duplicated M2ePro orders. remove m2e order without magento order id or newest order
        // ---------------------------------------
        if ($existOrdersNumber > 1) {
            $isDeleted = false;

            foreach ($existOrders as $key => $order) {
                /** @var Ess_M2ePro_Model_Order $order */

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

        // New order
        // ---------------------------------------
        if ($existOrdersNumber == 0) {
            $this->_status = self::STATUS_NEW;
            $this->_order = Mage::helper('M2ePro/Component_Amazon')->getModel('Order');
            $this->_order->setStatusUpdateRequired(true);

            return;
        }

        // ---------------------------------------

        // Already exist order
        // ---------------------------------------
        $this->_order = reset($existOrders);
        $this->_status = self::STATUS_UPDATED;

        if ($this->_order->getMagentoOrderId() === null) {
            $this->_order->setStatusUpdateRequired(true);
        }

        // ---------------------------------------
    }

    //########################################

    protected function prepareTaxDetails($data)
    {
        if ($this->isNeedSkipTax($data)) {
            $data['tax_details']['product'] = 0;
            $data['tax_details']['shipping'] = 0;
            $data['tax_details']['gift'] = 0;
        }

        return $data['tax_details'];
    }

    protected function prepareTaxRegistrationId($data)
    {
        if (empty($data['tax_registration_details'][0])) {
            return null;
        }

        $item = $data['tax_registration_details'][0];
        return !empty($item['tax_registration_id']) && is_string($item['tax_registration_id']) ?
            $item['tax_registration_id'] : null;
    }

    protected function isNeedSkipTax($data)
    {
        if ($this->isSkipTaxForUS($data)) {
            return true;
        }

        if ($this->isSkipTaxForUkShipment($data)) {
            return true;
        }

        if ($this->isSkipTaxForEEAShipmentFromUkSite($data['shipping_address']['country_code'])) {
            return true;
        }

        return false;
    }

    /**
     * @param string $countryCode
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function isSkipTaxForEEAShipmentFromUkSite($countryCode)
    {
        if (!$this->_account->getChildObject()->isAmazonCollectsTaxForEEAShipmentFromUkSite()) {
            return false;
        }

        if (!in_array(
            strtoupper($countryCode),
            $this->_account->getChildObject()->getExcludedCountries(),
            true
        )) {
            return false;
        }

        return true;
    }

    protected function isSkipTaxForUkShipment(array $data)
    {
        $countryCode = strtoupper($data['shipping_address']['country_code']);
        if (!in_array($countryCode, array('GB', 'UK'))) {
            return false;
        }

        if ($this->_account->getChildObject()->isAmazonCollectsTaxForUKShipmentAvailable()) {
            return true;
        }

        if ($this->_account->getChildObject()->isAmazonCollectsTaxForUKShipmentWithCertainPrice()
            && $this->isSumOfItemPriceLessThan135GBP(
                $data['items']
            )) {
            return true;
        }

        return false;
    }

    protected function isSkipTaxForUS(array $data)
    {
        if (!$this->_account->getChildObject()->isAmazonCollectsEnabled()) {
            return false;
        }

        $statesList = Mage::helper('M2ePro/Component_Amazon')->getStatesList();
        $excludedStates = $this->_account->getChildObject()->getExcludedStates();

        if (empty($excludedStates) || !isset($data['shipping_address']['state'])) {
            return false;
        }

        $state = strtoupper($data['shipping_address']['state']);

        foreach ($statesList as $code => $title) {
            if (!in_array($code, $excludedStates)) {
                continue;
            }

            if ($state == $code || $state == strtoupper($title)) {
                return true;
            }
        }

        return false;
    }

    protected function calculateItemPrice(array $items)
    {
        $result = 0.0;

        foreach ($items as $item) {
            $result += $this->convertPricetoGBP($item['price'], trim($item['currency']));
        }

        return $result;
    }

    protected function convertPriceToGBP($price, $currency)
    {
        return Mage::getSingleton('M2ePro/Currency')->convertPriceToCurrency($price, $currency, 'GBP');
    }

    protected function isSumOfItemPriceLessThan135GBP($items)
    {
        return $this->calculateItemPrice($items) < 135;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function process()
    {
        $this->checkUpdates();

        $this->createOrUpdateOrder();
        $this->createOrUpdateItems();

        if ($this->isNew() && !$this->getData('is_afn_channel') &&
            $this->getData('status') != Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED
        ) {
            $this->processListingsProductsUpdates();
            $this->processOtherListingsUpdates();
        }

        if ($this->isUpdated()) {
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

            /** @var $itemBuilder Ess_M2ePro_Model_Amazon_Order_Item_Builder */
            $itemBuilder = Mage::getModel('M2ePro/Amazon_Order_Item_Builder');
            $itemBuilder->initialize($itemData);

            $item = $itemBuilder->process();
            $item->setOrder($this->_order);

            $itemsCollection->removeItemByKey($item->getId());
            $itemsCollection->addItem($item);
        }
    }

    //########################################

    /**
     * @return bool
     */
    protected function isNew()
    {
        return $this->_status == self::STATUS_NEW;
    }

    /**
     * @return bool
     */
    protected function isUpdated()
    {
        return $this->_status == self::STATUS_UPDATED;
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function createOrUpdateOrder()
    {
        if (!$this->isNew() && $this->getData('status') == Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED) {
            $this->_order->setData('status', Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED);
            $this->_order->setData('purchase_update_date', $this->getData('purchase_update_date'));
            $this->_order->save();
        } else {
            $this->setData(
                'shipping_address',
                Mage::helper('M2ePro')->jsonEncode($this->getData('shipping_address'))
            );

            foreach ($this->getData() as $key => $value) {
                if (!$this->_order->getId() ||
                    ($this->_order->hasData($key) && $this->_order->getData($key) != $value)
                ) {
                    $this->_order->addData($this->getData());
                    $this->_order->save();
                    break;
                }
            }
        }

        if ($this->getData('status') == Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELLATION_REQUESTED) {
            if ($reason = $this->getData('buyer_cancel_reason')) {
                $noteText = 'A buyer requested order cancellation. Reason: ' .
                    Mage::helper('M2ePro')->escapeHtml($reason);
            } else {
                $noteText = 'A buyer requested order cancellation. The reason was not specified.';
            }

            /** @var Ess_M2ePro_Model_Order_Note $noteModel */
            $noteModel = Mage::getModel('M2ePro/Order_Note');
            $noteModel->setData('note', $noteText);
            $noteModel->setData('order_id', $this->_order->getId());
            $noteModel->save();
        }

        $this->_order->setAccount($this->_account);

        if ($this->_order->getChildObject()->isCanceled() && $this->_order->getReserve()->isPlaced()) {
            $this->_order->getReserve()->cancel();
        }
    }

    //########################################

    protected function checkUpdates()
    {
        if ($this->hasUpdatedStatus()) {
            $this->_updates[] = self::UPDATE_STATUS;
        }

        if ($this->hasUpdatedEmail()) {
            $this->_updates[] = self::UPDATE_EMAIL;
        }

        if ($this->hasUpdatedVat()) {
            $this->_updates[] = self::UPDATE_B2B_VAT;
        }
    }

    protected function hasUpdatedStatus()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        return $this->getData('status') != $this->_order->getData('status');
    }

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

    protected function hasUpdatedVat()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Order $amazonOrder */
        $amazonOrder = $this->_order->getChildObject();
        if (!$amazonOrder->isBusiness()) {
            return false;
        }

        /** @var Ess_M2ePro_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('M2ePro');

        $oldTaxDetails = $dataHelper->jsonDecode($amazonOrder->getData('tax_details'));
        $oldTaxSum = (float)array_sum(array_values($oldTaxDetails));

        $newTaxDetails = $dataHelper->jsonDecode($this->getData('tax_details'));
        $newTaxSum = (float)array_sum(array_values($newTaxDetails));

        return $newTaxSum !== $oldTaxSum;
    }

    //########################################

    protected function hasUpdates()
    {
        return !empty($this->_updates);
    }

    protected function hasUpdate($update)
    {
        return in_array($update, $this->_updates);
    }

    protected function processMagentoOrderUpdates()
    {
        if (!$this->hasUpdates() || $this->_order->getMagentoOrder() === null) {
            return;
        }

        if ($this->hasUpdate(self::UPDATE_STATUS) && $this->_order->getChildObject()->isCanceled()) {
            $this->cancelMagentoOrder();

            return;
        }

        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($this->_order->getMagentoOrder());

        if ($this->hasUpdate(self::UPDATE_STATUS)) {
            $this->_order->setStatusUpdateRequired(true);

            $this->_order->getProxy()->setStore($this->_order->getStore());

            $shippingData = $this->_order->getProxy()->getShippingData();
            $magentoOrderUpdater->updateShippingDescription(
                $shippingData['carrier_title'] . ' - ' . $shippingData['shipping_method']
            );
        }

        if ($this->hasUpdate(self::UPDATE_EMAIL)) {
            $magentoOrderUpdater->updateCustomerEmail($this->_order->getChildObject()->getBuyerEmail());
        }

        if ($this->hasUpdate(self::UPDATE_B2B_VAT)) {
            /** @var Ess_M2ePro_Helper_Data $dataHelper */
            $dataHelper = Mage::helper('M2ePro');

            $this->_order->markAsVatChanged();
            $message = 'Reverse charge (0% VAT) applied on Amazon';
            $magentoOrderUpdater->updateComments(array($dataHelper->__($message)));
            $this->_order->addInfoLog(
                $message,
                array(),
                array(),
                false,
                array(\Ess_M2ePro_Model_Order::ADDITIONAL_DATA_KEY_VAT_REVERSE_CHARGE => true)
            );
        }

        $magentoOrderUpdater->finishUpdate();
    }

    protected function cancelMagentoOrder()
    {
        $magentoOrderComments = array();
        $magentoOrderComments[] = '<b>Attention!</b> Order was canceled on Amazon.';
        $result = $this->_order->canCancelMagentoOrder();
        if ($result === true) {
            try {
                $this->_order->cancelMagentoOrder();
            } catch (\Exception $e) {
                Mage::helper('M2ePro/Module_Exception')->process($e);
            }

            $this->addCommentsToMagentoOrder($this->_order, $magentoOrderComments);
        }

        if ($result === false) {
            return;
        }

        $magentoOrderComments[] = 'Order cannot be canceled in Magento. Reason: ' . $result;
        $this->addCommentsToMagentoOrder($this->_order, $magentoOrderComments);
    }

    private function addCommentsToMagentoOrder(Ess_M2ePro_Model_Order $order, $comments)
    {
        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($order->getMagentoOrder());
        $magentoOrderUpdater->updateComments($comments);
        $magentoOrderUpdater->finishUpdate();
    }

    //########################################

    protected function processListingsProductsUpdates()
    {
        $logger = Mage::getModel('M2ePro/Listing_Log');
        $logger->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();

        $parentsForProcessing = array();

        foreach ($this->_items as $orderItem) {
            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
            $listingProductCollection->getSelect()->join(
                array('l' => Mage::getModel('M2ePro/Listing')->getResource()->getMainTable()),
                'main_table.listing_id=l.id',
                array('account_id')
            );
            $listingProductCollection->addFieldToFilter('sku', $orderItem['sku']);
            $listingProductCollection->addFieldToFilter('l.account_id', $this->_account->getId());

            /** @var Ess_M2ePro_Model_Listing_Product[] $listingsProducts */
            $listingsProducts = $listingProductCollection->getItems();
            if (empty($listingsProducts)) {
                continue;
            }

            foreach ($listingsProducts as $listingProduct) {
                if (!$listingProduct->isListed() && !$listingProduct->isStopped()) {
                    continue;
                }

                /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
                $amazonListingProduct = $listingProduct->getChildObject();

                if ($amazonListingProduct->isAfnChannel()) {
                    continue;
                }

                $currentOnlineQty = $listingProduct->getData('online_qty');

                // if product was linked by sku during list action
                if ($listingProduct->isStopped() && $currentOnlineQty === null) {
                    continue;
                }

                $variationManager = $amazonListingProduct->getVariationManager();

                if ($variationManager->isRelationChildType()) {
                    $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
                    $parentsForProcessing[$parentListingProduct->getId()] = $parentListingProduct;
                }

                $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
                $instruction->setData(
                    array(
                        'listing_product_id' => $listingProduct->getId(),
                        'component'          => Ess_M2ePro_Helper_Component_Amazon::NICK,
                        'type'               =>
                            Ess_M2ePro_Model_Amazon_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
                        'initiator'          => self::INSTRUCTION_INITIATOR,
                        'priority'           => 80,
                    )
                );
                $instruction->save();

                if ($currentOnlineQty > $orderItem['qty_purchased']) {
                    $listingProduct->setData('online_qty', $currentOnlineQty - $orderItem['qty_purchased']);

                    $tempLogMessage = Mage::helper('M2ePro')->__(
                        'Item QTY was changed from %from% to %to% .',
                        $currentOnlineQty,
                        ($currentOnlineQty - $orderItem['qty_purchased'])
                    );

                    $logger->addProductMessage(
                        $listingProduct->getListingId(),
                        $listingProduct->getProductId(),
                        $listingProduct->getId(),
                        Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                        $logsActionId,
                        Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
                        $tempLogMessage,
                        Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
                    );

                    $listingProduct->save();

                    continue;
                }

                $listingProduct->setData('online_qty', 0);

                $tempLogMessages = array(
                    Mage::helper('M2ePro')->__(
                        'Item QTY was changed from %from% to %to% .',
                        $currentOnlineQty,
                        0
                    )
                );

                if (!$listingProduct->isStopped()) {
                    $statusChangedFrom = Mage::helper('M2ePro/Component_Amazon')
                        ->getHumanTitleByListingProductStatus($listingProduct->getStatus());
                    $statusChangedTo = Mage::helper('M2ePro/Component_Amazon')
                        ->getHumanTitleByListingProductStatus(Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED);

                    if (!empty($statusChangedFrom) && !empty($statusChangedTo)) {
                        $tempLogMessages[] = Mage::helper('M2ePro')->__(
                            'Item Status was changed from "%from%" to "%to%" .',
                            $statusChangedFrom,
                            $statusChangedTo
                        );
                    }

                    $listingProduct->setData(
                        'status_changer',
                        Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT
                    );
                    $listingProduct->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED);
                }

                foreach ($tempLogMessages as $tempLogMessage) {
                    $logger->addProductMessage(
                        $listingProduct->getListingId(),
                        $listingProduct->getProductId(),
                        $listingProduct->getId(),
                        Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                        $logsActionId,
                        Ess_M2ePro_Model_Listing_Log::ACTION_CHANNEL_CHANGE,
                        $tempLogMessage,
                        Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
                    );
                }

                $listingProduct->save();
            }
        }

        if (!empty($parentsForProcessing)) {
            $massProcessor = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
            );
            $massProcessor->setListingsProducts($parentsForProcessing);
            $massProcessor->execute();
        }
    }

    protected function processOtherListingsUpdates()
    {
        foreach ($this->_items as $orderItem) {
            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingOtherCollection */
            $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
            $listingOtherCollection->addFieldToFilter('sku', $orderItem['sku']);
            $listingOtherCollection->addFieldToFilter('account_id', $this->_account->getId());

            /** @var Ess_M2ePro_Model_Listing_Other[] $otherListings */
            $otherListings = $listingOtherCollection->getItems();
            if (empty($otherListings)) {
                continue;
            }

            foreach ($otherListings as $otherListing) {
                if (!$otherListing->isListed() && !$otherListing->isStopped()) {
                    continue;
                }

                /** @var Ess_M2ePro_Model_Amazon_Listing_Other $amazonOtherListing */
                $amazonOtherListing = $otherListing->getChildObject();

                if ($amazonOtherListing->isAfnChannel()) {
                    continue;
                }

                $currentOnlineQty = $otherListing->getData('online_qty');

                if ($currentOnlineQty > $orderItem['qty_purchased']) {
                    $otherListing->setData('online_qty', $currentOnlineQty - $orderItem['qty_purchased']);
                    $otherListing->save();

                    continue;
                }

                $otherListing->setData('online_qty', 0);

                if (!$otherListing->isStopped()) {
                    $otherListing->setData(
                        'status_changer',
                        Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT
                    );
                    $otherListing->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED);
                }

                $otherListing->save();
            }
        }
    }

    //########################################
}
