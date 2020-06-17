<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Connector_Order_Update_Abstract
    extends Ess_M2ePro_Model_Ebay_Connector_Command_RealTime
{
    /**
     * @var $_order Ess_M2ePro_Model_Order
     */
    protected $_order  = null;
    protected $_action = null;

    protected $_status = Ess_M2ePro_Helper_Data::STATUS_SUCCESS;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @return $this
     */
    public function setOrder(Ess_M2ePro_Model_Order $order)
    {
        $this->_order   = $order;
        $this->_account = $order->getAccount();

        return $this;
    }

    /**
     * @param $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->_action = $action;
        return $this;
    }

    //----------------------------------------

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    //----------------------------------------

    /**
     * @return int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOrderChangeId()
    {
        if (isset($this->_params['change_id'])) {
            return (int)$this->_params['change_id'];
        }

        throw new Ess_M2ePro_Model_Exception_Logic('Order change id has not been set.');
    }

    //########################################

    /**
     * @return array
     */
    protected function getCommand()
    {
        return array('orders', 'update', 'status');
    }

    //########################################

    /**
     * @return bool
     */
    protected function validateResponse()
    {
        return true;
    }

    /**
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function process()
    {
        if (!$this->isNeedSendRequest()) {
            $this->_status = Ess_M2ePro_Helper_Data::STATUS_ERROR;
            return;
        }

        parent::process();
        /** @var Ess_M2ePro_Model_Order_Change $orderChange */
        $orderChange = Mage::getModel('M2ePro/Order_Change')->load($this->getOrderChangeId());
        $this->_order->getLog()->setInitiator($orderChange->getCreatorType());

        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError()) {
                continue;
            }

            $this->_status = Ess_M2ePro_Helper_Data::STATUS_ERROR;

            $this->_order->addErrorLog(
                'eBay Order status was not updated. Reason: %msg%', array('msg' => $message->getText())
            );
        }
    }

    //----------------------------------------

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function isNeedSendRequest()
    {
        if ($this->_order->getMarketplace()->getCode() == 'India'
            && stripos($this->_order->getChildObject()->getPaymentMethod(), 'paisa') !== false
        ) {
            /** @var Ess_M2ePro_Model_Order_Change $orderChange */
            $orderChange = Mage::getModel('M2ePro/Order_Change')->load($this->getOrderChangeId());
            $this->_order->getLog()->setInitiator($orderChange->getCreatorType());
            $this->_order->addErrorLog(
                'eBay Order Status was not updated. Reason: %msg%', array(
                'msg' => 'Status of India Site Orders cannot be updated if the Buyer uses PaisaPay payment method.'
                )
            );

            $orderChange->deleteInstance();
            return false;
        }

        if (!in_array(
            $this->_action, array(
            Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_PAY,
            Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP,
            Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK
            )
        )) {
            throw new Ess_M2ePro_Model_Exception_Logic('Invalid Action.');
        }

        return true;
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getRequestData()
    {
        return array(
            'action' => $this->_action,
            'order_id' => $this->_order->getChildObject()->getEbayOrderId()
        );
    }

    //########################################
}
