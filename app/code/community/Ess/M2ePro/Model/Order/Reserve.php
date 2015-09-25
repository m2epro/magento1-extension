<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order_Reserve
{
    const STATE_UNKNOWN  = 0;
    const STATE_PLACED   = 1;
    const STATE_RELEASED = 2;
    const STATE_CANCELED = 3;

    const ACTION_ADD = 'add';
    const ACTION_SUB = 'sub';

    /** @var Ess_M2ePro_Model_Order */
    private $order = null;

    private $flags = array();

    // ########################################

    public function __construct(Ess_M2ePro_Model_Order $order)
    {
        $this->order = $order;
    }

    public function setFlag($action, $flag)
    {
        $this->flags[$action] = (bool)$flag;
        return $this;
    }

    public function getFlag($action)
    {
        if (isset($this->flags[$action])) {
            return $this->flags[$action];
        }
        return null;
    }

    public function isNotProcessed()
    {
        return $this->order->getReservationState() == self::STATE_UNKNOWN;
    }

    public function isPlaced()
    {
        return $this->order->getReservationState() == self::STATE_PLACED;
    }

    public function isReleased()
    {
        return $this->order->getReservationState() == self::STATE_RELEASED;
    }

    public function isCanceled()
    {
        return $this->order->getReservationState() == self::STATE_CANCELED;
    }

    public function place()
    {
        if ($this->isPlaced()) {
            throw new Ess_M2ePro_Model_Exception_Logic('QTY is already reserved.');
        }

        $this->order->associateWithStore(false);
        $this->order->associateItemsWithProducts(false);

        try {
            $this->performAction(self::ACTION_SUB, self::STATE_PLACED);
            $this->order->addSuccessLog('QTY has been reserved.');
        } catch (Exception $e) {
            $this->order->addErrorLog(
                'QTY was not reserved. Reason: %msg%', array(
                    'msg' => $e->getMessage()
                )
            );
            return false;
        }

        return true;
    }

    public function release()
    {
        if ($this->isReleased()) {
            throw new Ess_M2ePro_Model_Exception_Logic('QTY is already released.');
        }

        if (!$this->isPlaced()) {
            return false;
        }

        try {
            $this->performAction(self::ACTION_ADD, self::STATE_RELEASED);
            $this->order->addSuccessLog('QTY has been released.');
        } catch (Exception $e) {
            $this->order->addErrorLog(
                'QTY was not released. Reason: %msg%', array(
                    'msg' => $e->getMessage()
                )
            );
            return false;
        }

        return true;
    }

    public function cancel()
    {
        if ($this->isCanceled()) {
            throw new Ess_M2ePro_Model_Exception_Logic('QTY reserve is already canceled.');
        }

        if (!$this->isPlaced()) {
            return false;
        }

        try {
            $this->performAction(self::ACTION_ADD, self::STATE_CANCELED);
            $this->order->addSuccessLog('QTY reserve has been canceled.');
        } catch (Exception $e) {
            $this->order->addErrorLog(
                'QTY reserve was not canceled. Reason: %msg%', array(
                    'msg' => $e->getMessage()
                )
            );
            return false;
        }

        return true;
    }

    private function performAction($action, $newState)
    {
        /** @var $transaction Mage_Core_Model_Resource_Transaction */
        $transaction = Mage::getModel('core/resource_transaction');

        $productsAffectedCount = 0;
        $productsDeletedCount  = 0;
        $productsExistCount    = 0;

        $stockItems = array();

        foreach ($this->order->getItemsCollection()->getItems() as $item) {
            if ($action == self::ACTION_SUB) {
                $qty = $item->getChildObject()->getQtyPurchased();
                $item->setData('qty_reserved', $qty);
            } else {
                $qty = $item->getQtyReserved();
            }

            $products = $this->getItemProductsByAction($item, $action);

            if (count($products) == 0) {
                continue;
            }

            foreach ($products as $key => $productId) {
                /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
                $magentoProduct = Mage::getModel('M2ePro/Magento_Product')
                    ->setStoreId($this->order->getStoreId())
                    ->setProductId($productId);

                if (!$magentoProduct->exists()) {
                    $productsDeletedCount++;
                    unset($products[$key]);
                    continue;
                }

                $productsExistCount++;

                if (!isset($stockItems[$productId])) {
                    $stockItems[$productId] = $magentoProduct->getStockItem();
                }

                $stockItem = $stockItems[$productId];

                /** @var $magentoStockItem Ess_M2ePro_Model_Magento_Product_StockItem */
                $magentoStockItem = Mage::getSingleton('M2ePro/Magento_Product_StockItem');
                $magentoStockItem->setStockItem($stockItem);

                if (!$this->changeProductQty($magentoProduct, $magentoStockItem, $action, $qty)) {
                    if ($action == self::ACTION_SUB) {
                        unset($products[$key]);
                    }

                    continue;
                }

                if ($action == self::ACTION_ADD) {
                    unset($products[$key]);
                }

                $productsAffectedCount++;

                $transaction->addObject($magentoStockItem->getStockItem());
            }

            $item->setReservedProducts($products);
            $transaction->addObject($item);
        }

        unset($stockItems);

        if ($productsExistCount == 0 && $productsDeletedCount == 0) {
            $this->order->setData('reservation_state', self::STATE_UNKNOWN)->save();
            throw new Ess_M2ePro_Model_Exception_Logic('The Order Item(s) was not Mapped to Magento Product(s) or
                Mapped incorrect.');
        }

        if ($productsExistCount == 0) {
            $this->order->setData('reservation_state', self::STATE_UNKNOWN)->save();
            throw new Ess_M2ePro_Model_Exception_Logic('Product(s) does not exist.');
        }

        if ($productsDeletedCount > 0) {
            $this->order->addWarningLog(
                'QTY for %number% Product(s) was not changed. Reason: Product(s) does not exist.',
                array(
                    '!number' => $productsDeletedCount
                )
            );
        }

        $this->order->setData('reservation_state', $newState);

        if ($newState == self::STATE_PLACED && !$this->getFlag('order_reservation')) {
            $this->order->setData('reservation_start_date', Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        $transaction->addObject($this->order);
        $transaction->save();
    }

    private function changeProductQty(
        Ess_M2ePro_Model_Magento_Product $magentoProduct,
        Ess_M2ePro_Model_Magento_Product_StockItem $magentoStockItem,
        $action,
        $qty
    ) {
        $result = true;

        switch ($action) {
            case self::ACTION_ADD:
                $magentoStockItem->addQty($qty, false);
                break;
            case self::ACTION_SUB:
                try {
                    $magentoStockItem->subtractQty($qty, false);
                } catch (Exception $e) {
                    $result = false;

                    $this->order->addErrorLog(
                        'QTY for Product "%name%" cannot be reserved. Reason: %msg%',
                        array(
                            '!name' => $magentoProduct->getName(),
                            'msg' => $e->getMessage()
                        )
                    );
                }
                break;
        }

        return $result;
    }

    private function getItemProductsByAction(Ess_M2ePro_Model_Order_Item $item, $action)
    {
        $products = array();

        switch ($action) {
            case self::ACTION_ADD:
                $products = $item->getReservedProducts();
                break;
            case self::ACTION_SUB:
                if ($item->getProductId() && $item->getMagentoProduct()->isSimpleType()) {
                    $products[] = $item->getProductId();
                } else {
                    $products = $item->getAssociatedProducts();
                }
                break;
        }

        return $products;
    }

    // ########################################
}