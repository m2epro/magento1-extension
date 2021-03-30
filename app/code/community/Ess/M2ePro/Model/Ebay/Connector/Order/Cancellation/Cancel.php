<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Order_Cancellation_Cancel
    extends Ess_M2ePro_Model_Ebay_Connector_Order_Cancellation_Abstract
{
    //########################################

    protected function getCommand()
    {
        return array('orders', 'cancel', 'entity');
    }

    public function getRequestData()
    {
        return $this->_orderChange->getParams();
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processResponseData()
    {
        $this->_order->getLog()->setInitiator($this->_orderChange->getCreatorType());

        /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messagesSet */
        $messagesSet = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
        $messagesSet->init($this->getResponseMessages());

        foreach ($messagesSet->getEntities() as $message) {
            if ($message->isError()) {
                $this->_order->addErrorLog(
                    'eBay order was not canceled. Reason: %msg%',
                    array('msg' => $message->getText())
                );
            } else {
                $this->_order->addWarningLog($message->getText());
            }
        }

        if ($messagesSet->hasErrorEntities()) {
            return;
        }

        if ($this->_orderChange->getAction() === Ess_M2ePro_Model_Order_Change::ACTION_CANCEL) {
            $this->_orderChange->deleteInstance();
        }

        if ($this->_responseData['result'] && $this->_responseData['cancelId'] !== null) {
            $this->_order->getChildObject()->setData('cancellation_status', 1);
            $this->_order->getChildObject()->save();
            $this->_order->addSuccessLog('Order is canceled. Status is updated on eBay.');
        }
    }

    //########################################
}
