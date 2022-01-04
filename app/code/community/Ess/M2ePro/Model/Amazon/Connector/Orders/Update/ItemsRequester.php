<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Orders_Update_ItemsRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Requester
{
    //########################################

    public function getCommand()
    {
        return array('orders','update','entities');
    }

    //########################################

    public function process()
    {
        $this->eventBeforeExecuting();
        $this->getProcessingRunner()->start();
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Amazon_Connector_Orders_ProcessingRunner';
    }

    protected function getProcessingParams()
    {
        return array_merge(
            parent::getProcessingParams(),
            array(
                'request_data' => $this->getRequestData(),
                'order_id'     => $this->_params['order']['order_id'],
                'change_id'    => $this->_params['order']['change_id'],
                'action_type'  => Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_UPDATE,
                'lock_name'    => 'update_shipping_status',
                'start_date'   => Mage::helper('M2ePro')->getCurrentGmtDate(),
            )
        );
    }

    //########################################

    public function getRequestData()
    {
        $order = $this->_params['order'];
        $fulfillmentDate = new DateTime($order['fulfillment_date'], new DateTimeZone('UTC'));

        $request = array(
            'id'               => $order['order_id'],
            'order_id'         => $order['amazon_order_id'],
            'tracking_number'  => $order['tracking_number'],
            'carrier_name'     => $order['carrier_name'],
            'carrier_code'     => $order['carrier_code'],
            'fulfillment_date' => $fulfillmentDate->format('c'),
            'shipping_method'  => isset($order['shipping_method']) ? $order['shipping_method'] : null,
            'items'            => array()
        );

        if (isset($order['items']) && is_array($order['items'])) {
            foreach ($order['items'] as $item) {
                $request['items'][] = array(
                    'item_code' => $item['amazon_order_item_id'],
                    'qty'       => (int)$item['qty']
                );
            }
        }

        return $request;
    }

    //########################################
}
