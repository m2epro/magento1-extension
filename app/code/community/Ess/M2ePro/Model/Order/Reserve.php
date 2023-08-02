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
    protected $_order = null;

    protected $_flags = array();

    protected $_qtyChangeInfo = array();

    //########################################

    public function __construct(Ess_M2ePro_Model_Order $order)
    {
        $this->_order = $order;
    }

    public function setFlag($action, $flag)
    {
        $this->_flags[$action] = (bool)$flag;

        return $this;
    }

    public function getFlag($action)
    {
        if (isset($this->_flags[$action])) {
            return $this->_flags[$action];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isNotProcessed()
    {
        return $this->_order->getReservationState() == self::STATE_UNKNOWN;
    }

    /**
     * @return bool
     */
    public function isPlaced()
    {
        return $this->_order->getReservationState() == self::STATE_PLACED;
    }

    /**
     * @return bool
     */
    public function isReleased()
    {
        return $this->_order->getReservationState() == self::STATE_RELEASED;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->_order->getReservationState() == self::STATE_CANCELED;
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
            $this->_order->associateWithStore();
            $this->_order->associateItemsWithProducts();

            $this->performAction(self::ACTION_SUB, self::STATE_PLACED);

            if (!$this->isPlaced()) {
                return false;
            }
        } catch (Exception $e) {
            $message = 'QTY was not reserved. Reason: %msg%';
            if ($e instanceof Ess_M2ePro_Model_Order_Exception_ProductCreationDisabled) {
                $this->_order->addInfoLog($message, array('msg' => $e->getMessage()), array(), true);

                return false;
            }

            $this->_order->addErrorLog($message,  array('msg' => $e->getMessage()));

            return false;
        }

        $this->addSuccessLogQtyChange();
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
            $this->_order->addErrorLog(
                'QTY was not released. Reason: %msg%',
                array(
                    'msg' => $e->getMessage()
                )
            );

            return false;
        }

        $this->addSuccessLogQtyChange();
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
            $this->_order->addErrorLog(
                'QTY reserve was not canceled. Reason: %msg%',
                array(
                    'msg' => $e->getMessage()
                )
            );

            return false;
        }

        $this->addSuccessLogQtyChange();
        $this->_order->addSuccessLog('QTY reserve has been canceled.');

        return true;
    }

    /**
     * @param $action
     * @param $newState
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function performAction($action, $newState)
    {
        /** @var $transaction Mage_Core_Model_Resource_Transaction */
        $transaction = Mage::getModel('core/resource_transaction');

        $productsAffectedCount = 0;
        $productsDeletedCount = 0;
        $productsExistCount = 0;

        $stockItems = array();

        foreach ($this->_order->getItemsCollection()->getItems() as $item) {
            if ($action == self::ACTION_SUB) {
                $qty = $item->getChildObject()->getQtyPurchased();
                $item->setData('qty_reserved', $qty);
            } else {
                $qty = $item->getQtyReserved();
                $item->setData('qty_reserved', 0);
            }

            $products = $this->getItemProductsByAction($item, $action);

            if (empty($products)) {
                continue;
            }

            foreach ($products as $key => $productId) {
                /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
                $magentoProduct = Mage::getModel('M2ePro/Magento_Product')
                    ->setStoreId($this->_order->getStoreId())
                    ->setProductId($productId);

                if (!$magentoProduct->exists()) {
                    $productsDeletedCount++;
                    unset($products[$key]);
                    continue;
                }

                $productsExistCount++;

                if ($item->getMagentoProduct()->isBundleType()) {
                    $bundleDefaultQty = $item
                        ->getMagentoProduct()
                        ->getBundleDefaultQty($magentoProduct->getProductId());
                    $qty *= $bundleDefaultQty;
                }

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
                $this->pushQtyChangeInfo($qty, $action, $magentoProduct);
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
            $this->_order->setData('reservation_state', self::STATE_UNKNOWN)->save();
            throw new Ess_M2ePro_Model_Exception_Logic(
                'The Order Item(s) was not Linked to Magento Product(s) or Linked incorrect.'
            );
        }

        if ($productsExistCount == 0) {
            $this->_order->setData('reservation_state', self::STATE_UNKNOWN)->save();
            throw new Ess_M2ePro_Model_Exception_Logic('Product(s) does not exist.');
        }

        if ($productsDeletedCount > 0) {
            $this->_order->addWarningLog(
                'QTY for %number% Product(s) was not changed. Reason: Product(s) does not exist.',
                array(
                    '!number' => $productsDeletedCount
                )
            );
        }

        if ($productsAffectedCount <= 0) {
            return;
        }

        $this->_order->setData('reservation_state', $newState);

        if ($newState == self::STATE_PLACED && !$this->getFlag('order_reservation')) {
            $this->_order->setData('reservation_start_date', Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        $transaction->addObject($this->_order);
        $transaction->save();
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @param Ess_M2ePro_Model_Magento_Product_StockItem $magentoStockItem
     * @param $action
     * @param $qty
     * @return bool
     */
    protected function changeProductQty(
        Ess_M2ePro_Model_Magento_Product $magentoProduct,
        Ess_M2ePro_Model_Magento_Product_StockItem $magentoStockItem,
        $action,
        $qty
    ) {
        $result = false;

        if ($magentoStockItem->canChangeQty()) {

            if ($action == self::ACTION_ADD) {
                $result = $magentoStockItem->addQty($qty, false);
            }

            if ($action == self::ACTION_SUB) {
                $result = $magentoStockItem->subtractQty($qty, false);

                if ($result === false &&
                    !$magentoStockItem->isAllowedQtyBelowZero() &&
                    $magentoStockItem->resultOfSubtractingQtyBelowZero($qty)
                ) {
                    $this->_order->addErrorLog(
                        'QTY wasn’t reserved for "%name%". Magento QTY: "%magento_qty%". Ordered QTY: "%order_qty%".',
                        array(
                            '!name' => $magentoProduct->getName(),
                            '!magento_qty' => $magentoStockItem->getStockItem()->getQty(),
                            '!order_qty' => $qty,
                        )
                    );

                    return false;
                }
            }
        }

        if ($result === false && $this->_order->getLog()->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_USER) {
            $msg = 'The QTY Reservation action (reserve/release/cancel) has not been performed for "%name%" '
                . 'as the "Decrease Stock When Order is Placed" or/and "Manage Stock" options are disabled in your '
                . 'Magento Inventory configurations.';
            $this->_order->addWarningLog(
                $msg,
                array('!name' => $magentoProduct->getName())
            );
        }

        return $result;
    }

    protected function pushQtyChangeInfo($qty, $action, Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->_qtyChangeInfo[] = array(
            'action'       => $action,
            'quantity'     => $qty,
            'product_name' => $magentoProduct->getName()
        );
    }

    protected function addSuccessLogQtyChange()
    {
        $description = array(
            self::ACTION_ADD => 'QTY was released for "%product_name%". Released QTY: %quantity%.',
            self::ACTION_SUB => 'QTY was reserved for "%product_name%". Reserved QTY: %quantity%.'
        );

        foreach ($this->_qtyChangeInfo as $item) {
            $this->_order->addSuccessLog(
                $description[$item['action']],
                array(
                    '!product_name' => $item['product_name'],
                    '!quantity'    => $item['quantity']
                )
            );
        }

        $this->_qtyChangeInfo = array();
    }

    /**
     * @param Ess_M2ePro_Model_Order_Item $item
     * @param $action
     * @return array|mixed|null
     */
    protected function getItemProductsByAction(Ess_M2ePro_Model_Order_Item $item, $action)
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
