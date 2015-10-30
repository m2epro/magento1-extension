<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Order_Builder extends Mage_Core_Model_Abstract
{
    const STATUS_NOT_MODIFIED = 0;
    const STATUS_NEW          = 1;
    const STATUS_UPDATED      = 2;

    // M2ePro_TRANSLATIONS
    // Duplicated Buy orders with ID #%id%.

    //########################################

    /** @var $order Ess_M2ePro_Model_Account */
    private $account = NULL;

    /** @var $order Ess_M2ePro_Model_Order */
    private $order = NULL;

    private $status = self::STATUS_NOT_MODIFIED;

    private $items = array();

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
        $this->setData('marketplace_id', Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_ID);

        $this->setData('seller_id', $data['seller_id']);
        $this->setData('buy_order_id', $data['order_id']);
        $this->setData('purchase_create_date', $data['purchase_create_date']);
        // ---------------------------------------

        // Init sale data
        // ---------------------------------------
        $this->setData('paid_amount', (float)$data['paid_amount']);
        $this->setData('currency', 'USD');
        // ---------------------------------------

        // Init customer/shipping data
        // ---------------------------------------
        $this->setData('buyer_name', $data['buyer_name']);
        $this->setData('buyer_email', $data['buyer_email']);
        $this->setData('billing_address', $data['billing_address']);
        $this->setData('shipping_method', $data['shipping_method']);
        $this->setData('shipping_address', $data['shipping_address']);
        $this->setData('shipping_price', (float)$data['shipping_price']);
        // ---------------------------------------

        $this->items = $data['items'];
    }

    //########################################

    private function initializeOrder()
    {
        $this->status = self::STATUS_NOT_MODIFIED;

        $existOrders = Mage::helper('M2ePro/Component_Buy')
            ->getCollection('Order')
            ->addFieldToFilter('account_id', $this->account->getId())
            ->addFieldToFilter('buy_order_id', $this->getData('buy_order_id'))
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
            $this->order = Mage::helper('M2ePro/Component_Buy')->getModel('Order');
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

    public function process()
    {
        $this->createOrUpdateOrder();
        $this->createOrUpdateItems();

        if ($this->isNew()) {
            $this->processListingsProductsUpdates();
            $this->processOtherListingsUpdates();
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

            /** @var $itemBuilder Ess_M2ePro_Model_Buy_Order_Item_Builder */
            $itemBuilder = Mage::getModel('M2ePro/Buy_Order_Item_Builder');
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
        $this->setData('billing_address', json_encode($this->getData('billing_address')));
        $this->setData('shipping_address', json_encode($this->getData('shipping_address')));
        $this->order->addData($this->getData());

        $this->order->save();
        $this->order->setAccount($this->account);
    }

    //########################################

    private function processListingsProductsUpdates()
    {
        $logger = Mage::getModel('M2ePro/Listing_Log');
        $logger->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);

        $logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();

        foreach ($this->items as $orderItem) {
            /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
            $listingProductCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Product');
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

                Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                    $listingProduct->getProductId(), Ess_M2ePro_Model_ProductChange::INITIATOR_SYNCHRONIZATION
                );

                $currentOnlineQty = $listingProduct->getData('online_qty');

                if ($currentOnlineQty > $orderItem['qty']) {
                    $listingProduct->setData('online_qty', $currentOnlineQty - $orderItem['qty']);

                    // M2ePro_TRANSLATIONS
                    // Item QTY was successfully changed from %from% to %to% .
                    $tempLogMessage = Mage::helper('M2ePro')->__(
                        'Item QTY was successfully changed from %from% to %to% .',
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
                    $statusChangedFrom = Mage::helper('M2ePro/Component_Buy')
                        ->getHumanTitleByListingProductStatus($listingProduct->getStatus());
                    $statusChangedTo = Mage::helper('M2ePro/Component_Buy')
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
    }

    private function processOtherListingsUpdates()
    {
        $logger = Mage::getModel('M2ePro/Listing_Other_Log');
        $logger->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);

        $logsActionId = Mage::getModel('M2ePro/Listing_Other_Log')->getNextActionId();

        foreach ($this->items as $orderItem) {
            /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingOtherCollection */
            $listingOtherCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');
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

                $currentOnlineQty = $otherListing->getData('online_qty');

                if ($currentOnlineQty > $orderItem['qty']) {
                    $otherListing->setData('online_qty', $currentOnlineQty - $orderItem['qty']);

                    // M2ePro_TRANSLATIONS
                    // Item QTY was successfully changed from %from% to %to% .
                    $tempLogMessage = Mage::helper('M2ePro')->__(
                        'Item QTY was successfully changed from %from% to %to% .',
                        $currentOnlineQty,
                        ($currentOnlineQty - $orderItem['qty'])
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
                    'Item QTY was successfully changed from %from% to %tp% .',
                    $currentOnlineQty, 0
                ));

                if (!$otherListing->isStopped()) {
                    $statusChangedFrom = Mage::helper('M2ePro/Component_Buy')
                        ->getHumanTitleByListingProductStatus($otherListing->getStatus());
                    $statusChangedTo = Mage::helper('M2ePro/Component_Buy')
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