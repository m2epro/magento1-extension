<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Builder extends Mage_Core_Model_Abstract
{
    const STATUS_NOT_MODIFIED = 0;
    const STATUS_NEW          = 1;
    const STATUS_UPDATED      = 2;

    const UPDATE_STATUS = 'status';
    const UPDATE_EMAIL  = 'email';

    // M2ePro_TRANSLATIONS
    // Duplicated Amazon orders with ID #%id%.

    //########################################

    /** @var $helper Ess_M2ePro_Model_Amazon_Order_Helper */
    private $helper = NULL;

    /** @var $order Ess_M2ePro_Model_Account */
    private $account = NULL;

    /** @var $order Ess_M2ePro_Model_Order */
    private $order = NULL;

    private $status = self::STATUS_NOT_MODIFIED;

    private $items = array();

    private $updates = array();

    //########################################

    public function __construct()
    {
        $this->helper = Mage::getSingleton('M2ePro/Amazon_Order_Helper');
    }

    //########################################

    public function initialize(Ess_M2ePro_Model_Account $account, array $data = array())
    {
        $this->account = $account;

        $this->initializeData($data);
        $this->initializeOrder();
    }

    //########################################

    private function initializeData(array $data = array())
    {
        // Init general data
        // ---------------------------------------
        $this->setData('account_id', $this->account->getId());
        $this->setData('amazon_order_id', $data['amazon_order_id']);
        $this->setData('marketplace_id', $data['marketplace_id']);

        $this->setData('status', $this->helper->getStatus($data['status']));
        $this->setData('is_afn_channel', $data['is_afn_channel']);
        $this->setData('is_prime', $data['is_prime']);

        $this->setData('purchase_update_date', $data['purchase_update_date']);
        $this->setData('purchase_create_date', $data['purchase_create_date']);
        // ---------------------------------------

        // Init sale data
        // ---------------------------------------
        $this->setData('paid_amount', (float)$data['paid_amount']);
        $this->setData('tax_details', json_encode($data['tax_details']));
        $this->setData('discount_details', json_encode($data['discount_details']));
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
        $this->setData('shipping_dates', json_encode($data['shipping_dates']));
        // ---------------------------------------

        $this->items = $data['items'];
    }

    //########################################

    private function initializeOrder()
    {
        $this->status = self::STATUS_NOT_MODIFIED;

        $existOrders = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Order')
            ->addFieldToFilter('account_id', $this->account->getId())
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
            $this->status = self::STATUS_NEW;
            $this->order = Mage::helper('M2ePro/Component_Amazon')->getModel('Order');
            $this->order->setStatusUpdateRequired(true);

            return;
        }
        // ---------------------------------------

        // Already exist order
        // ---------------------------------------
        $this->order = reset($existOrders);
        $this->status = self::STATUS_UPDATED;

        if (is_null($this->order->getMagentoOrderId())) {
            $this->order->setStatusUpdateRequired(true);
        }
        // ---------------------------------------
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order
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

        return $this->order;
    }

    //########################################

    private function createOrUpdateItems()
    {
        $itemsCollection = $this->order->getItemsCollection();
        $itemsCollection->load();

        foreach ($this->items as $itemData) {
            $itemData['order_id'] = $this->order->getId();

            /** @var $itemBuilder Ess_M2ePro_Model_Amazon_Order_Item_Builder */
            $itemBuilder = Mage::getModel('M2ePro/Amazon_Order_Item_Builder');
            $itemBuilder->initialize($itemData);

            $item = $itemBuilder->process();
            $item->setOrder($this->order);

            $itemsCollection->removeItemByKey($item->getId());
            $itemsCollection->addItem($item);
        }
    }

    //########################################

    /**
     * @return bool
     */
    private function isNew()
    {
        return $this->status == self::STATUS_NEW;
    }

    /**
     * @return bool
     */
    private function isUpdated()
    {
        return $this->status == self::STATUS_UPDATED;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order
     */
    private function createOrUpdateOrder()
    {
        if (!$this->isNew() && $this->getData('status') == Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED) {
            $this->order->setData('status', Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED);
            $this->order->setData('purchase_update_date', $this->getData('purchase_update_date'));
        } else {
            $this->setData('shipping_address', json_encode($this->getData('shipping_address')));
            $this->order->addData($this->getData());
        }

        $this->order->save();
        $this->order->setAccount($this->account);
    }

    //########################################

    private function checkUpdates()
    {
        if ($this->hasUpdatedStatus()) {
            $this->updates[] = self::UPDATE_STATUS;
        }
        if ($this->hasUpdatedEmail()) {
            $this->updates[] = self::UPDATE_EMAIL;
        }
    }

    private function hasUpdatedStatus()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        return $this->getData('status') != $this->order->getData('status');
    }

    private function hasUpdatedEmail()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        $newEmail = $this->getData('buyer_email');
        $oldEmail = $this->order->getData('buyer_email');

        if ($newEmail == $oldEmail) {
            return false;
        }

        return filter_var($newEmail, FILTER_VALIDATE_EMAIL) !== false;
    }

    //########################################

    private function hasUpdates()
    {
        return !empty($this->updates);
    }

    private function hasUpdate($update)
    {
        return in_array($update, $this->updates);
    }

    private function processMagentoOrderUpdates()
    {
        if (!$this->hasUpdates() || is_null($this->order->getMagentoOrder())) {
            return;
        }

        if ($this->hasUpdate(self::UPDATE_STATUS) && $this->order->getChildObject()->isCanceled()) {
            $this->cancelMagentoOrder();
            return;
        }

        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($this->order->getMagentoOrder());

        if ($this->hasUpdate(self::UPDATE_STATUS)) {
            $this->order->setStatusUpdateRequired(true);

            $this->order->getProxy()->setStore($this->order->getStore());

            $shippingData = $this->order->getProxy()->getShippingData();
            $magentoOrderUpdater->updateShippingDescription(
                $shippingData['carrier_title'].' - '.$shippingData['shipping_method']
            );
        }

        if ($this->hasUpdate(self::UPDATE_EMAIL)) {
            $magentoOrderUpdater->updateCustomerEmail($this->order->getChildObject()->getBuyerEmail());
        }

        $magentoOrderUpdater->finishUpdate();
    }

    private function cancelMagentoOrder()
    {
        if (!$this->order->canCancelMagentoOrder()) {
            return;
        }

        $magentoOrderComments = array();
        $magentoOrderComments[] = '<b>Attention!</b> Order was canceled on Amazon.';

        try {
            $this->order->cancelMagentoOrder();
        } catch (Exception $e) {
            $magentoOrderComments[] = 'Order cannot be canceled in Magento. Reason: ' . $e->getMessage();
        }

        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($this->order->getMagentoOrder());
        $magentoOrderUpdater->updateComments($magentoOrderComments);
        $magentoOrderUpdater->finishUpdate();
    }

    //########################################

    private function processListingsProductsUpdates()
    {
        $logger = Mage::getModel('M2ePro/Listing_Log');
        $logger->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();

        $parentsForProcessing = array();

        foreach ($this->items as $orderItem) {
            /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
            $listingProductCollection->getSelect()->join(
                array('l' => Mage::getModel('M2ePro/Listing')->getResource()->getMainTable()),
                'main_table.listing_id=l.id',
                array('account_id')
            );
            $listingProductCollection->addFieldToFilter('sku', $orderItem['sku']);
            $listingProductCollection->addFieldToFilter('l.account_id', $this->account->getId());

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

                $variationManager = $amazonListingProduct->getVariationManager();

                if ($variationManager->isRelationChildType()) {
                    $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
                    $parentsForProcessing[$parentListingProduct->getId()] = $parentListingProduct;
                }

                Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                    $listingProduct->getProductId(), Ess_M2ePro_Model_ProductChange::INITIATOR_SYNCHRONIZATION
                );

                $currentOnlineQty = $listingProduct->getData('online_qty');

                if ($currentOnlineQty > $orderItem['qty_purchased']) {
                    $listingProduct->setData('online_qty', $currentOnlineQty - $orderItem['qty_purchased']);

                    // M2ePro_TRANSLATIONS
                    // Item QTY was successfully changed from %from% to %to% .
                    $tempLogMessage = Mage::helper('M2ePro')->__(
                        'Item QTY was successfully changed from %from% to %to% .',
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
                        Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                    );

                    $listingProduct->save();

                    continue;
                }

                $listingProduct->setData('online_qty', 0);

                $tempLogMessages = array(Mage::helper('M2ePro')->__(
                    'Item QTY was successfully changed from %from% to %to% .',
                    $currentOnlineQty, 0
                ));

                if (!$listingProduct->isStopped()) {
                    $statusChangedFrom = Mage::helper('M2ePro/Component_Amazon')
                        ->getHumanTitleByListingProductStatus($listingProduct->getStatus());
                    $statusChangedTo = Mage::helper('M2ePro/Component_Amazon')
                        ->getHumanTitleByListingProductStatus(Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED);

                    if (!empty($statusChangedFrom) && !empty($statusChangedTo)) {
                        // M2ePro_TRANSLATIONS
                        // Item Status was successfully changed from "%from%" to "%to%" .
                        $tempLogMessages[] = Mage::helper('M2ePro')->__(
                            'Item Status was successfully changed from "%from%" to "%to%" .',
                            $statusChangedFrom,
                            $statusChangedTo
                        );
                    }

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
                        Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                    );
                }

                $listingProduct->save();
            }
        }

        if (empty($parentsForProcessing)) {
            return;
        }

        $massProcessor = Mage::getModel(
            'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
        );
        $massProcessor->setListingsProducts($parentsForProcessing);
        $massProcessor->execute();
    }

    private function processOtherListingsUpdates()
    {
        $logger = Mage::getModel('M2ePro/Listing_Other_Log');
        $logger->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $logsActionId = Mage::getModel('M2ePro/Listing_Other_Log')->getNextActionId();

        foreach ($this->items as $orderItem) {
            /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingOtherCollection */
            $listingOtherCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');
            $listingOtherCollection->addFieldToFilter('sku', $orderItem['sku']);
            $listingOtherCollection->addFieldToFilter('account_id', $this->account->getId());

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

                    // M2ePro_TRANSLATIONS
                    // Item QTY was successfully changed from %from% to %to% .
                    $tempLogMessage = Mage::helper('M2ePro')->__(
                        'Item QTY was successfully changed from %from% to %to% .',
                        $currentOnlineQty,
                        ($currentOnlineQty - $orderItem['qty_purchased'])
                    );

                    $logger->addProductMessage(
                        $otherListing->getId(),
                        Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                        $logsActionId,
                        Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANNEL_CHANGE,
                        $tempLogMessage,
                        Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                    );

                    $otherListing->save();

                    continue;
                }

                $otherListing->setData('online_qty', 0);

                $tempLogMessages = array(Mage::helper('M2ePro')->__(
                    'Item qty was successfully changed from %from% to %to% .',
                    $currentOnlineQty, 0
                ));

                if (!$otherListing->isStopped()) {
                    $statusChangedFrom = Mage::helper('M2ePro/Component_Amazon')
                        ->getHumanTitleByListingProductStatus($otherListing->getStatus());
                    $statusChangedTo = Mage::helper('M2ePro/Component_Amazon')
                        ->getHumanTitleByListingProductStatus(Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED);

                    if (!empty($statusChangedFrom) && !empty($statusChangedTo)) {
                        // M2ePro_TRANSLATIONS
                        // Item Status was successfully changed from "%from%" to "%to%" .
                        $tempLogMessages[] = Mage::helper('M2ePro')->__(
                            'Item Status was successfully changed from "%from%" to "%to%" .',
                            $statusChangedFrom, $statusChangedTo
                        );
                    }

                    $otherListing->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED);
                }

                foreach ($tempLogMessages as $tempLogMessage) {
                    $logger->addProductMessage(
                        $otherListing->getId(),
                        Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                        $logsActionId,
                        Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANNEL_CHANGE,
                        $tempLogMessage,
                        Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                    );
                }

                $otherListing->save();
            }
        }
    }

    //########################################
}