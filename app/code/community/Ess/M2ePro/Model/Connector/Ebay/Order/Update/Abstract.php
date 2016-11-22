<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_Order_Update_Abstract
    extends Ess_M2ePro_Model_Connector_Ebay_Abstract
{
    // M2ePro_TRANSLATIONS
    // eBay Order Status was not updated. Reason: %msg%
    // Status of India Site Orders cannot be updated if the Buyer uses PaisaPay payment method.

    /**
     * @var $order Ess_M2ePro_Model_Order
     */
    protected $order = NULL;
    protected $action = NULL;

    //########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Order $order, $action = NULL)
    {
        $this->order = $order;
        $this->action = $action;

        parent::__construct($params, NULL, $order->getAccount());
    }

    //########################################

    protected function getCommand()
    {
        return array('orders', 'update', 'status');
    }

    //########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    public function process()
    {
        if (!$this->isNeedSendRequest()) {
            return false;
        }

        $result = parent::process();

        foreach ($this->messages as $message) {
            if ($message[parent::MESSAGE_TYPE_KEY] != parent::MESSAGE_TYPE_ERROR) {
                continue;
            }

            $this->order->addErrorLog(
                'eBay Order status was not updated. Reason: %msg%', array('msg' => $message[parent::MESSAGE_TEXT_KEY])
            );
        }

        return $result;
    }

    // ---------------------------------------

    protected function isNeedSendRequest()
    {
        if ($this->order->getMarketplace()->getCode() == 'India'
            && stripos($this->order->getChildObject()->getPaymentMethod(), 'paisa')
        ) {
            $this->order->addErrorLog('eBay Order Status was not updated. Reason: %msg%', array(
                'msg' => 'Status of India Site Orders cannot be updated if the Buyer uses PaisaPay payment method.'
            ));

            return false;
        }

        if (!in_array($this->action,array(
            Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher::ACTION_PAY,
            Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher::ACTION_SHIP,
            Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK
        ))) {
            throw new Ess_M2ePro_Model_Exception_Logic('Invalid Action.');
        }

        return true;
    }

    protected function getRequestData()
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

    //########################################
}