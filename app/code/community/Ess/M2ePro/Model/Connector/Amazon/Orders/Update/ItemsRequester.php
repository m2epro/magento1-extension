<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Amazon_Orders_Update_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('orders','update','entities');
    }

    //########################################

    protected function getResponserParams()
    {
        $params = array();

        foreach ($this->params['items'] as $orderUpdate) {
            if (!is_array($orderUpdate)) {
                continue;
            }

            $params[$orderUpdate['change_id']] = $orderUpdate;
        }

        return $params;
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

        foreach ($this->params['items'] as $update) {
            if (!isset($update['order_id'])) {
                throw new Ess_M2ePro_Model_Exception_Logic('Order ID is not defined.');
            }

            $ordersIds[] = (int)$update['order_id'];
        }

        /** @var Ess_M2ePro_Model_Order $orders */
        $orders = Mage::getModel('M2ePro/Order')
            ->getCollection()
                ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK)
                ->addFieldToFilter('id', array('in' => $ordersIds))
                ->getItems();

        foreach ($orders as $order) {
            $order->addObjectLock('update_shipping_status', $processingRequest->getHash());
        }
    }

    //########################################

    protected function getRequestData()
    {
        if (!isset($this->params['items']) || !is_array($this->params['items'])) {
            return array(
                'accounts' => $this->getAccountsAccessTokens(),
                'items' => array()
            );
        }

        $orders = array();

        foreach ($this->params['items'] as $orderUpdate) {
            if (!is_array($orderUpdate)) {
                continue;
            }

            $fulfillmentDate = new DateTime($orderUpdate['fulfillment_date'], new DateTimeZone('UTC'));

            $order = array(
                'id'               => $orderUpdate['change_id'],
                'order_id'         => $orderUpdate['amazon_order_id'],
                'tracking_number'  => $orderUpdate['tracking_number'],
                'carrier_name'     => $orderUpdate['carrier_name'],
                'fulfillment_date' => $fulfillmentDate->format('c'),
                'shipping_method'  => isset($orderUpdate['shipping_method']) ? $orderUpdate['shipping_method'] : null,
                'items'            => array()
            );

            if (isset($orderUpdate['items']) && is_array($orderUpdate['items'])) {
                foreach ($orderUpdate['items'] as $item) {
                    $order['items'][] = array(
                        'item_code' => $item['amazon_order_item_id'],
                        'qty'       => (int)$item['qty']
                    );
                }
            }

            $orders[] = $order;
        }

        return array(
            'accounts' => $this->getAccountsAccessTokens(),
            'items' => $orders
        );
    }

    // ---------------------------------------

    private function getAccountsAccessTokens()
    {
        $accountsAccessTokens = array();
        foreach ($this->params['accounts'] as $account) {
            $accountsAccessTokens[] = $account->getChildObject()->getServerHash();
        }

        return $accountsAccessTokens;
    }

    //########################################

    private function deleteProcessedChanges()
    {
        // collect ids of processed order changes
        // ---------------------------------------
        $changeIds = array();

        foreach ($this->params['items'] as $orderUpdate) {
            if (!is_array($orderUpdate)) {
                continue;
            }

            $changeIds[] = $orderUpdate['change_id'];
        }
        // ---------------------------------------

        Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds($changeIds);
    }

    //########################################
}