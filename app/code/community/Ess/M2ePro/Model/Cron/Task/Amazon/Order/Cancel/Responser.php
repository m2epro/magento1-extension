<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_Cancel_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Orders_Cancel_ItemsResponser
{
    /** @var Ess_M2ePro_Model_Order $_order */
    protected $_order = array();

    //########################################

    public function __construct(array $params, Ess_M2ePro_Model_Connector_Connection_Response $response)
    {
        $this->_order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', $params['order']['order_id']);
        parent::__construct($params, $response);
    }

    //########################################

    /**
     * @param $messageText
     * @return void|null
     */
    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        /** @var Ess_M2ePro_Model_Order_Change $orderChange */
        $orderChange = Mage::getModel('M2ePro/Order_Change')->load($this->_params['order']['change_id']);
        $this->_order->getLog()->setInitiator($orderChange->getCreatorType());
        $this->_order->addErrorLog('Amazon Order was not cancelled. Reason: %msg%', array('msg' => $messageText));
    }

    /**
     * @return bool
     */
    protected function isNeedProcessResponse()
    {
        if (!parent::isNeedProcessResponse()) {
            return false;
        }

        if ($this->getResponse()->getMessages()->hasErrorEntities()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $messages
     * @return void
     */
    protected function processResponseMessages(array $messages = array())
    {
        parent::processResponseMessages();

        $orderChange = Mage::getModel('M2ePro/Order_Change')->load($this->_params['order']['change_id']);
        $this->_order->getLog()->setInitiator($orderChange->getCreatorType());

        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if ($message->isError()) {
                $this->_order->addErrorLog(
                    'Amazon Order was not cancelled. Reason: %msg%',
                    array('msg' => $message->getText())
                );
            } else {
                $this->_order->addWarningLog($message->getText());
            }
        }
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processResponseData()
    {
        /** @var Ess_M2ePro_Model_Order_Change $orderChange */
        $orderChange = Mage::getModel('M2ePro/Order_Change')->load($this->_params['order']['change_id']);
        $this->_order->getLog()->setInitiator($orderChange->getCreatorType());
        $orderChange->deleteInstance();

        $responseData = $this->getPreparedResponseData();

        // Check separate messages
        //----------------------
        $isFailed = false;

        /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messagesSet */
        $messagesSet = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
        $messagesSet->init($responseData['messages']);

        foreach ($messagesSet->getEntities() as $message) {
            if ($message->isError()) {
                $isFailed = true;

                $this->_order->addErrorLog(
                    'Amazon Order was not cancelled. Reason: %msg%',
                    array('msg' => $message->getText())
                );
            } else {
                $this->_order->addWarningLog($message->getText());
            }
        }

        if ($isFailed) {
            return;
        }

        $this->_order->addSuccessLog('Amazon Order was cancelled.');
    }

    //########################################
}
