<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Orders_Refund_ItemsRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('orders','refund','entities');
    }

    // ########################################

    public function process()
    {
        $this->eventBeforeExecuting();
        $this->getProcessingRunner()->start();
    }

    // ########################################

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
                'order_id'     => $this->params['order']['order_id'],
                'change_id'    => $this->params['order']['change_id'],
                'action_type'  => Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_REFUND,
                'lock_name'    => 'refund_order',
                'start_date'   => Mage::helper('M2ePro')->getCurrentGmtDate(),
            )
        );
    }

    // ########################################

    public function getRequestData()
    {
        return array(
            'order_id' => $this->params['order']['amazon_order_id'],
            'currency' => $this->params['order']['currency'],
            'type'     => 'Refund',
            'items'    => $this->params['order']['items'],
        );
    }

    // ########################################
}