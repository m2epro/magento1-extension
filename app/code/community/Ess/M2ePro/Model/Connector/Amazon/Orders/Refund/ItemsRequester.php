<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Amazon_Orders_Refund_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('orders','refund','entities');
    }

    //########################################

    protected function getResponserParams()
    {
        $params = array();

        foreach ($this->params['items'] as $item) {
            if (!is_array($item)) {
                continue;
            }

            $params[$item['change_id']] = $item;
        }

        return $params;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function setProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::setProcessingLocks($processingRequest);

        if (!isset($this->params['items']) || !is_array($this->params['items'])) {
            return;
        }

        $ordersIds = array();

        foreach ($this->params['items'] as $item) {
            if (!isset($item['order_id'])) {
                throw new Ess_M2ePro_Model_Exception_Logic('Order ID is not defined.');
            }

            $ordersIds[] = (int)$item['order_id'];
        }

        /** @var Ess_M2ePro_Model_Order[] $orders */
        $orders = Mage::getModel('M2ePro/Order')
            ->getCollection()
            ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->addFieldToFilter('id', array('in' => $ordersIds))
            ->getItems();

        foreach ($orders as $order) {
            $order->addObjectLock('refund_order', $processingRequest->getHash());
        }
    }

    //########################################

    protected function getRequestData()
    {
        if (!isset($this->params['items']) || !is_array($this->params['items'])) {
            return array('items' => array());
        }

        $orders = array();

        foreach ($this->params['items'] as $orderRefund) {
            if (!is_array($orderRefund)) {
                continue;
            }

            $orders[$orderRefund['change_id']] = array(
                'order_id' => $orderRefund['amazon_order_id'],
                'currency' => $orderRefund['currency'],
                'type'     => 'Refund',
                'items'    => $orderRefund['items'],
            );
        }

        return array('orders' => $orders);
    }

    //########################################

    public function process()
    {
        parent::process();

        $this->deleteProcessedChanges();
    }

    //########################################

    private function deleteProcessedChanges()
    {
        // collect ids of processed order changes
        // ---------------------------------------
        $changeIds = array();

        foreach ($this->params['items'] as $orderRefund) {
            if (!is_array($orderRefund)) {
                continue;
            }

            $changeIds[] = $orderRefund['change_id'];
        }
        // ---------------------------------------

        Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds($changeIds);
    }

    //########################################
}