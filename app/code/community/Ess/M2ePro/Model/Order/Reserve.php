<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
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

    //########################################

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

    /**
     * @return bool
     */
    public function isNotProcessed()
    {
        return $this->order->getReservationState() == self::STATE_UNKNOWN;
    }

    /**
     * @return bool
     */
    public function isPlaced()
    {
        return $this->order->getReservationState() == self::STATE_PLACED;
    }

    /**
     * @return bool
     */
    public function isReleased()
    {
        return $this->order->getReservationState() == self::STATE_RELEASED;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->order->getReservationState() == self::STATE_CANCELED;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function place()
    {
        if ($this->isPlaced()) {
            throw new Ess_M2ePro_Model_Exception_Logic('QTY is already reserved.');
        }

        try {

            $this->order->associateWithStore();
            $this->order->associateItemsWithProducts();

            $this->performAction(self::ACTION_SUB, self::STATE_PLACED);

            if (!$this->isPlaced()) {
                return false;
            }
        } catch (Exception $e) {
            $this->order->addErrorLog(
                'QTY was not reserved. Reason: %msg%', array(
                    'msg' => $e->getMessage()
                )
            );
            return false;
        }

        $this->order->addSuccessLog('QTY has been reserved.');
        return true;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
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

            if (!$this->isReleased()) {
                return false;
            }
        } catch (Exception $e) {
            $this->order->addErrorLog(
                'QTY was not released. Reason: %msg%', array(
                    'msg' => $e->getMessage()
                )
            );
            return false;
        }

        $this->order->addSuccessLog('QTY has been released.');
        return true;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
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

            if (!$this->isCanceled()) {
                return false;
            }
        } catch (Exception $e) {
            $this->order->addErrorLog(
                'QTY reserve was not canceled. Reason: %msg%', array(
                    'msg' => $e->getMessage()
                )
            );
            return false;
        }

        $this->order->addSuccessLog('QTY reserve has been canceled.');
        return true;
    }

    /**
     * @param $action
     * @param $newState
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
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
                $item->setData('qty_reserved', 0);
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

                if ($item->getMagentoProduct()->isSimpleType() || $item->getMagentoProduct()->isDownloadableType()) {
                    $item->getProduct()->setStockItem($magentoStockItem->getStockItem());
                }
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

        if ($productsAffectedCount <= 0) {
            return;
        }

        $this->order->setData('reservation_state', $newState);

        if ($newState == self::STATE_PLACED && !$this->getFlag('order_reservation')) {
            $this->order->setData('reservation_start_date', Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        $transaction->addObject($this->order);
        $transaction->save();
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @param Ess_M2ePro_Model_Magento_Product_StockItem $magentoStockItem
     * @param $action
     * @param $qty
     * @return bool
     */
    private function changeProductQty(
        Ess_M2ePro_Model_Magento_Product $magentoProduct,
        Ess_M2ePro_Model_Magento_Product_StockItem $magentoStockItem,
        $action,
        $qty
    ) {
        $result = true;

        switch ($action) {

            case self::ACTION_ADD:
                if ($magentoStockItem->canChangeQty()) {
                    $result = $magentoStockItem->addQty($qty, false);
                }
                break;

            case self::ACTION_SUB:
                try {
                    $result = $magentoStockItem->subtractQty($qty, false);
                } catch (Exception $e) {

                    $this->order->addErrorLog(
                        'QTY for Product "%name%" cannot be reserved. Reason: %msg%',
                        array(
                            '!name' => $magentoProduct->getName(),
                            'msg' => $e->getMessage()
                        )
                    );
                    return false;
                }
                break;
        }

        if ($result === false && $this->order->getLog()->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_USER) {
            $msg = 'The QTY Reservation action (reserve/release/cancel) has not been performed for "%name%" '
                . 'as the "Decrease Stock When Order is Placed" or/and "Manage Stock" options are disabled in your '
                . 'Magento Inventory configurations.';
            $this->order->addWarningLog(
                $msg,
                array('!name' => $magentoProduct->getName())
            );
        }

        return $result;
    }

    /**
     * @param Ess_M2ePro_Model_Order_Item $item
     * @param $action
     * @return array|mixed|null
     */
    private function getItemProductsByAction(Ess_M2ePro_Model_Order_Item $item, $action)
    {
        $products = array();

        switch ($action) {
            case self::ACTION_ADD:
                $products = $item->getReservedProducts();
                break;
            case self::ACTION_SUB:
                if ($item->getProductId() &&
                    ($item->getMagentoProduct()->isSimpleType() ||
                     $item->getMagentoProduct()->isDownloadableType())
                ) {
                    $products[] = $item->getProductId();
                } else {
                    $products = $item->getAssociatedProducts();
                }
                break;
        }

        return $products;
    }

    //########################################
}