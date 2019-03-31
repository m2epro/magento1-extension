<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_OrderItem_Update_Status
    extends Ess_M2ePro_Model_Ebay_Connector_Command_RealTime
{
    // M2ePro_TRANSLATIONS
    // Shipping status was not updated (Item: %item_id%, Transaction: %trn_id%). Reason: %msg%
    // Shipping status was not updated (Item: %item_id%, Transaction: %trn_id%). Reason: eBay Failure.
    // Tracking number "%num%" for "%code%" has been sent to eBay (Item: %item_id%, Transaction: %trn_id%).
    // Order Item has been marked as Shipped (Item: %item_id%, Transaction: %trn_id%).

    /** @var $orderItem Ess_M2ePro_Model_Order_Item */
    private $orderItem;

    // ########################################

    public function setOrderItem(Ess_M2ePro_Model_Order_Item $orderItem)
    {
        $this->orderItem = $orderItem;
        $this->account   = $orderItem->getOrder()->getAccount();

        return $this;
    }

    //----------------------------------------

    /**
     * @return int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOrderChangeId()
    {
        if (isset($this->params['change_id'])) {
            return (int)$this->params['change_id'];
        }

        throw new Ess_M2ePro_Model_Exception_Logic('Order change id has not been set.');
    }

    // ########################################

    protected function getCommand()
    {
        return array('orders', 'update', 'status');
    }

    protected function isNeedSendRequest()
    {
        return true;
    }

    public function getRequestData()
    {
        $action = Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP;
        if (!empty($this->params['tracking_number']) && !empty($this->params['carrier_code'])) {
            $action = Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK;
        }

        $trackingNumber = !empty($this->params['tracking_number']) ? $this->params['tracking_number'] : null;
        $carrierCode = !empty($this->params['carrier_code']) ? $this->params['carrier_code'] : null;

        return array(
            'account'         => $this->orderItem->getOrder()->getAccount()->getServerHash(),
            'action'          => $action,
            'item_id'         => $this->orderItem->getChildObject()->getItemId(),
            'transaction_id'  => $this->orderItem->getChildObject()->getTransactionId(),
            'tracking_number' => $trackingNumber,
            'carrier_code'    => $carrierCode
        );
    }

    // ########################################

    public function process()
    {
        if (!$this->isNeedSendRequest()) {
            return false;
        }

        parent::process();

        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError()) {
                continue;
            }

            $messageText = 'Shipping status was not updated (Item: %item_id%, Transaction: %trn_id%). Reason: %msg%';
            $this->orderItem->getOrder()->addErrorLog($messageText, array(
                '!item_id' => $this->orderItem->getChildObject()->getItemId(),
                '!trn_id'  => $this->orderItem->getChildObject()->getTransactionId(),
                'msg'      => $message->getText(),
            ));
        }
    }

    // ########################################

    protected function validateResponseData()
    {
        return true;
    }

    protected function prepareResponseData()
    {
        if ($this->getResponse()->isResultError()) {
            return;
        }

        $responseData = $this->getResponse()->getData();

        if (!isset($responseData['result']) || !$responseData['result']) {
            $message = 'Shipping status was not updated (Item: %item_id%, Transaction: %trn_id%). '.
                       'Reason: eBay Failure.';
            $this->orderItem->getOrder()->addErrorLog($message, array(
                '!item_id' => $this->orderItem->getChildObject()->getItemId(),
                '!trn_id'  => $this->orderItem->getChildObject()->getTransactionId(),
            ));

            return;
        }

        if (!empty($this->params['tracking_number']) && !empty($this->params['carrier_code'])) {
            $message = 'Tracking number "%num%" for "%code%" has been sent to eBay '.
                       '(Item: %item_id%, Transaction: %trn_id%).';
            $this->orderItem->getOrder()->addSuccessLog($message, array(
                '!num' => $this->params['tracking_number'],
                'code' => $this->params['carrier_code'],
                '!item_id' => $this->orderItem->getChildObject()->getItemId(),
                '!trn_id'  => $this->orderItem->getChildObject()->getTransactionId(),
            ));
        } else {
            $message = 'Order Item has been marked as Shipped (Item: %item_id%, Transaction: %trn_id%).';
            $this->orderItem->getOrder()->addSuccessLog($message, array(
                '!item_id' => $this->orderItem->getChildObject()->getItemId(),
                '!trn_id'  => $this->orderItem->getChildObject()->getTransactionId(),
            ));
        }

        Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds(array($this->getOrderChangeId()));
    }

    // ########################################
}