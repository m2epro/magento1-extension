<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Connector_Order_Update_Abstract
    extends Ess_M2ePro_Model_Ebay_Connector_Command_RealTime
{
    // M2ePro_TRANSLATIONS
    // eBay Order Status was not updated. Reason: %msg%
    // Status of India Site Orders cannot be updated if the Buyer uses PaisaPay payment method.

    /**
     * @var $order Ess_M2ePro_Model_Order
     */
    protected $order = NULL;
    protected $action = NULL;

    private $status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;

    // ########################################

    public function setOrder(Ess_M2ePro_Model_Order $order)
    {
        $this->order = $order;
        $this->account = $order->getAccount();

        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    //----------------------------------------

    public function getStatus()
    {
        return $this->status;
    }

    //----------------------------------------

    /**
     * @return int|null
     */
    public function getOrderChangeId()
    {
        if (isset($this->params['change_id'])) {
            return (int)$this->params['change_id'];
        }

        return NULL;
    }

    // ########################################

    protected function getCommand()
    {
        return array('orders', 'update', 'status');
    }

    // ########################################

    protected function validateResponse()
    {
        return true;
    }

    public function process()
    {
        if (!$this->isNeedSendRequest()) {
            $this->status = Ess_M2ePro_Helper_Data::STATUS_ERROR;
            return;
        }

        parent::process();

        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError()) {
                continue;
            }

            $this->status = Ess_M2ePro_Helper_Data::STATUS_ERROR;

            $this->order->addErrorLog(
                'eBay Order status was not updated. Reason: %msg%', array('msg' => $message->getText())
            );
        }
    }

    //----------------------------------------

    protected function isNeedSendRequest()
    {
        if ($this->order->getMarketplace()->getCode() == 'India'
            && stripos($this->order->getChildObject()->getPaymentMethod(), 'paisa') !== false
        ) {
            $this->order->addErrorLog('eBay Order Status was not updated. Reason: %msg%', array(
                'msg' => 'Status of India Site Orders cannot be updated if the Buyer uses PaisaPay payment method.'
            ));

            return false;
        }

        if (!in_array($this->action,array(
            Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_PAY,
            Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP,
            Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK
        ))) {
            throw new Ess_M2ePro_Model_Exception_Logic('Invalid Action.');
        }

        return true;
    }

    public function getRequestData()
    {
        $requestData = array('action' => $this->action);

        $ebayOrderId = $this->order->getData('ebay_order_id');

        if (strpos($ebayOrderId, '-') === false) {
            $requestData['order_id'] = $ebayOrderId;
        } else {
            $orderIdParts = explode('-', $ebayOrderId);

            $requestData['item_id'] = $orderIdParts[0];
            $requestData['transaction_id'] = $orderIdParts[1];
        }

        return $requestData;
    }

    // ########################################
}