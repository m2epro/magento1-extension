<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Order_Builder extends Mage_Core_Model_Abstract
{
    const INSTRUCTION_INITIATOR = 'order_builder';

    const STATUS_NOT_MODIFIED = 0;
    const STATUS_NEW          = 1;
    const STATUS_UPDATED      = 2;

    const UPDATE_STATUS = 'status';

    //########################################

    /** @var Ess_M2ePro_Model_Walmart_Order_Helper */
    protected $_helper = null;

    /** @var Ess_M2ePro_Model_Account */
    protected $_account = null;

    /** @var Ess_M2ePro_Model_Order */
    protected $_order = null;

    protected $_status = self::STATUS_NOT_MODIFIED;

    protected $_items = array();

    protected $_updates = array();

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::getSingleton('M2ePro/Walmart_Order_Helper');
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
        $this->setData('walmart_order_id', $data['walmart_order_id']);
        $this->setData('customer_order_id', $data['customer_order_id']);
        $this->setData('marketplace_id', $this->_account->getChildObject()->getMarketplaceId());

        $itemsStatuses = array();
        foreach ($data['items'] as $item) {
            $itemsStatuses[$item['walmart_order_item_id']] = $item['status'];
        }

        $this->setData('status', $this->_helper->getOrderStatus($itemsStatuses));

        $this->setData('purchase_update_date', $data['update_date']);
        $this->setData('purchase_create_date', $data['purchase_date']);
        // ---------------------------------------

        // Init sale data
        // ---------------------------------------
        $this->setData('paid_amount', (float)$data['amount_paid']);
        $this->setData('tax_details', Mage::helper('M2ePro')->jsonEncode($data['tax_details']));
        $this->setData('currency', $data['currency']);
        // ---------------------------------------

        // Init customer/shipping data
        // ---------------------------------------
        $this->setData('buyer_name', $data['buyer']['name']);
        $this->setData('buyer_email', $data['buyer']['email']);
        $this->setData('shipping_service', $data['shipping']['level']);
        $this->setData('shipping_address', Mage::helper('M2ePro')->jsonEncode($data['shipping']['address']));
        $this->setData('shipping_price', (float)$data['shipping']['price']);
        $this->setData('shipping_date_to', $data['shipping']['estimated_ship_date']);
        // ---------------------------------------

        $this->_items = $data['items'];
    }

    //########################################

    protected function initializeOrder()
    {
        $this->_status = self::STATUS_NOT_MODIFIED;

        $existOrders = Mage::helper('M2ePro/Component_Walmart')
            ->getCollection('Order')
            ->addFieldToFilter('account_id', $this->_account->getId())
            ->addFieldToFilter('walmart_order_id', $this->getData('walmart_order_id'))
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
            $this->_order  = Mage::helper('M2ePro/Component_Walmart')->getModel('Order');
            $this->_order->setStatusUpdateRequired(true);

            return;
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

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order
     */
    public function process()
    {
        $this->checkUpdates();

        $this->createOrUpdateOrder();
        $this->createOrUpdateItems();

        if ($this->isNew() && $this->getData('status') != Ess_M2ePro_Model_Walmart_Order::STATUS_CANCELED) {
            $this->processListingsProductsUpdates();
            $this->processOtherListingsUpdates();
        }

        if ($this->isUpdated()) {
            $this->processMagentoOrderUpdates();
        }

        return $this->_order;
    }

    //########################################

    /**
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function createOrUpdateItems()
    {
        $itemsCollection = $this->_order->getItemsCollection();
        $itemsCollection->load();

        foreach ($this->_items as $itemData) {
            $itemData['order_id'] = $this->_order->getId();

            /** @var $itemBuilder Ess_M2ePro_Model_Walmart_Order_Item_Builder */
            $itemBuilder = Mage::getModel('M2ePro/Walmart_Order_Item_Builder');
            $itemBuilder->initialize($itemData);

            $item = $itemBuilder->process();
            $item->setOrder($this->_order);
            if (!$itemBuilder->getPreviousBuyerCancellationRequested()
                && $item->getChildObject()->isBuyerCancellationRequested()
            ) {
                $description = 'A buyer requested to cancel the item(s) "%item_name%"'
                    . ' from the order #%order_number%.';

                $this->_order->addWarningLog(
                    $description,
                    array(
                        '!order_number' => $this->_order->getChildObject()->getWalmartOrderId(),
                        '!item_name'    => $item->getChildObject()->getTitle()
                    )
                );
            }

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

    protected function createOrUpdateOrder()
    {
        if (!$this->isNew() && $this->getData('status') == Ess_M2ePro_Model_Walmart_Order::STATUS_CANCELED) {
            $this->_order->setData('status', Ess_M2ePro_Model_Walmart_Order::STATUS_CANCELED);
            $this->_order->setData('purchase_update_date', $this->getData('purchase_update_date'));
            $this->_order->save();
        } else {
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

        $this->_order->setAccount($this->_account);
    }

    //########################################

    protected function checkUpdates()
    {
        if ($this->hasUpdatedStatus()) {
            $this->_updates[] = self::UPDATE_STATUS;
        }
    }

    protected function hasUpdatedStatus()
    {
        if (!$this->isUpdated()) {
            return false;
        }

        return $this->getData('status') != $this->_order->getData('status');
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
                $shippingData['carrier_title'].' - '.$shippingData['shipping_method']
            );
        }

        $magentoOrderUpdater->finishUpdate();
    }

    protected function cancelMagentoOrder()
    {
        $magentoOrderComments = array();
        $magentoOrderComments[] = '<b>Attention!</b> Order was canceled on Walmart.';
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
        $logger->setComponentMode(Ess_M2ePro_Helper_Component_Walmart::NICK);

        $logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();

        $parentsForProcessing = array();

        foreach ($this->_items as $orderItem) {
            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
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
                if (!$listingProduct->isListed() && !$listingProduct->isInactive()) {
                    continue;
                }

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
                $walmartListingProduct = $listingProduct->getChildObject();

                $currentOnlineQty = $listingProduct->getData('online_qty');

                // if product was linked by sku during list action
                if ($listingProduct->isInactive() && $currentOnlineQty === null) {
                    continue;
                }

                $variationManager = $walmartListingProduct->getVariationManager();

                if ($variationManager->isRelationChildType()) {
                    $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
                    $parentsForProcessing[$parentListingProduct->getId()] = $parentListingProduct;
                }

                $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
                $instruction->setData(
                    array(
                    'listing_product_id' => $listingProduct->getId(),
                    'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
                    'type'               =>
                        Ess_M2ePro_Model_Walmart_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
                    'initiator'          => self::INSTRUCTION_INITIATOR,
                    'priority'           => 80,
                    )
                );
                $instruction->save();

                if ($currentOnlineQty > $orderItem['qty']) {
                    $listingProduct->setData('online_qty', $currentOnlineQty - $orderItem['qty']);

                    $tempLogMessage = Mage::helper('M2ePro')->__(
                        'Item QTY was changed from %from% to %to% .',
                        $currentOnlineQty,
                        ($currentOnlineQty - $orderItem['qty'])
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

                $tempLogMessages = array(Mage::helper('M2ePro')->__(
                    'Item QTY was changed from %from% to %to% .',
                    $currentOnlineQty, 0
                ));

                if (!$listingProduct->isInactive()) {
                    $statusChangedFrom = Mage::helper('M2ePro/Component_Walmart')
                        ->getHumanTitleByListingProductStatus($listingProduct->getStatus());
                    $statusChangedTo = Mage::helper('M2ePro/Component_Walmart')
                        ->getHumanTitleByListingProductStatus(Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE);

                    if (!empty($statusChangedFrom) && !empty($statusChangedTo)) {
                        $tempLogMessages[] = Mage::helper('M2ePro')->__(
                            'Item Status was changed from "%from%" to "%to%" .',
                            $statusChangedFrom,
                            $statusChangedTo
                        );
                    }

                    $listingProduct->setData(
                        'status_changer', Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT
                    );
                    $listingProduct->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE);
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
                'M2ePro/Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Mass'
            );
            $massProcessor->setListingsProducts($parentsForProcessing);
            $massProcessor->execute();
        }
    }

    protected function processOtherListingsUpdates()
    {
        foreach ($this->_items as $orderItem) {
            /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingOtherCollection */
            $listingOtherCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');
            $listingOtherCollection->addFieldToFilter('sku', $orderItem['sku']);
            $listingOtherCollection->addFieldToFilter('account_id', $this->_account->getId());

            /** @var Ess_M2ePro_Model_Listing_Other[] $otherListings */
            $otherListings = $listingOtherCollection->getItems();
            if (empty($otherListings)) {
                continue;
            }

            foreach ($otherListings as $otherListing) {
                if (!$otherListing->isListed() && !$otherListing->isInactive()) {
                    continue;
                }

                $currentOnlineQty = $otherListing->getData('online_qty');

                if ($currentOnlineQty > $orderItem['qty']) {
                    $otherListing->setData('online_qty', $currentOnlineQty - $orderItem['qty']);
                    $otherListing->save();

                    continue;
                }

                $otherListing->setData('online_qty', 0);

                if (!$otherListing->isInactive()) {
                    $otherListing->setData(
                        'status_changer', Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT
                    );
                    $otherListing->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE);
                }

                $otherListing->save();
            }
        }
    }

    //########################################
}
