<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Buy_Orders_Update_ShippingRequester
    extends Ess_M2ePro_Model_Connector_Buy_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('orders','update','confirmation');
    }

    //########################################

    public function eventBeforeExecuting()
    {
        parent::eventBeforeExecuting();
        $this->deleteProcessedChanges();
    }

    // ---------------------------------------

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

        foreach ($this->params['items'] as $updateData) {
            if (!isset($updateData['order_id'])) {
                throw new Ess_M2ePro_Model_Exception_Logic('Order ID is not defined.');
            }

            $ordersIds[] = (int)$updateData['order_id'];
        }

        /** @var Ess_M2ePro_Model_Order[] $orders */
        $orders = Mage::getModel('M2ePro/Order')
            ->getCollection()
            ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Buy::NICK)
            ->addFieldToFilter('id', array('in' => $ordersIds))
            ->getItems();

        foreach ($orders as $order) {
            $order->addObjectLock('update_shipping_status', $processingRequest->getHash());
        }
    }

    //########################################

    protected function getResponserParams()
    {
        $params = array();

        foreach ($this->params['items'] as $updateData) {
            $params[$updateData['order_id']] = array(
                'order_id'        => $updateData['order_id'],
                'tracking_type'   => $updateData['tracking_type'],
                'tracking_number' => $updateData['tracking_number']
            );
        }

        return $params;
    }

    //########################################

    protected function getRequestData()
    {
        $items = array();

        foreach ($this->params['items'] as $updateData) {
            $items[] = array(
                'order_id'        => $updateData['buy_order_id'],
                'order_item_id'   => $updateData['buy_order_item_id'],
                'qty'             => $updateData['qty'],
                'tracking_type'   => $updateData['tracking_type'],
                'tracking_number' => $updateData['tracking_number'],
                'ship_date'       => $updateData['ship_date'],
            );
        }

        return array('items' => $items);
    }

    //########################################

    private function deleteProcessedChanges()
    {
        // collect ids of processed order changes
        // ---------------------------------------
        $changeIds = array();

        foreach ($this->params['items'] as $updateData) {
            if (!is_array($updateData)) {
                continue;
            }

            $changeIds[] = $updateData['change_id'];
        }
        // ---------------------------------------

        Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds($changeIds);
    }

    //########################################
}