<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Order_Invoice as AmazonOrderInvoice;

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_SendInvoice_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Orders_SendInvoice_ItemsResponser
{
    /** @var Ess_M2ePro_Model_Order */
    protected $_order;

    /** @var Ess_M2ePro_Model_Order_Change */
    protected $_orderChange;

    //########################################

    public function __construct(array $params, Ess_M2ePro_Model_Connector_Connection_Response $response)
    {
        $this->_order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', $params['order']['order_id']);
        $this->_orderChange = Mage::getModel('M2ePro/Order_Change')->load($params['order']['change_id']);

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

        $this->_order->getLog()->setInitiator($this->_orderChange->getCreatorType());
        $this->_order->addErrorLog('Amazon Order invoice was not send. Reason: %msg%', array('msg' => $messageText));
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

        $this->_order->getLog()->setInitiator($this->_orderChange->getCreatorType());

        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if ($message->isError()) {
                $this->logErrorMessage($message);
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
        $this->_order->getLog()->setInitiator($this->_orderChange->getCreatorType());
        $responseData = $this->getPreparedResponseData();

        /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messagesSet */
        $messagesSet = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
        $messagesSet->init($responseData['messages']);

        foreach ($messagesSet->getEntities() as $message) {
            if ($message->isError()) {
                $this->logErrorMessage($message);
            } else {
                $this->_order->addWarningLog($message->getText());
            }
        }

        if ($messagesSet->hasErrorEntities()) {
            return;
        }

        $this->_orderChange->deleteInstance();

        if ($this->_params['order']['document_type'] == AmazonOrderInvoice::DOCUMENT_TYPE_INVOICE) {
            /** @var Ess_M2ePro_Model_Amazon_Order $amazonOrder */
            $amazonOrder = $this->_order->getChildObject();
            $amazonOrder
                ->markThatInvoiceSentToChannel()
                ->save();
            $this->_order->addSuccessLog(
                'Invoice #%document_number% was sent.',
                array(
                    'document_number' => $this->_params['order']['document_number'],
                )
            );
        } elseif ($this->_params['order']['document_type'] == AmazonOrderInvoice::DOCUMENT_TYPE_CREDIT_NOTE) {
            $this->_order->getChildObject()->setData('is_credit_memo_sent', 1)->save();
            $this->_order->addSuccessLog(
                'Credit Memo #%document_number% was sent.',
                array(
                    'document_number' => $this->_params['order']['document_number'],
                )
            );
        }
    }

    /**
     * @param Ess_M2ePro_Model_Response_Message $message
     */
    protected function logErrorMessage(Ess_M2ePro_Model_Response_Message $message)
    {
        if ($this->_params['order']['document_type'] == AmazonOrderInvoice::DOCUMENT_TYPE_INVOICE) {
            $this->_order->addErrorLog(
                'Invoice #%document_number% was not sent. Reason: %msg%',
                array(
                    'document_number' => $this->_params['order']['document_number'],
                    'msg' => $message->getText()
                )
            );
        } elseif ($this->_params['order']['document_type'] == AmazonOrderInvoice::DOCUMENT_TYPE_CREDIT_NOTE) {
            $this->_order->addErrorLog(
                'Credit Memo #%document_number% was not sent. Reason: %msg%',
                array(
                    'document_number' => $this->_params['order']['document_number'],
                    'msg' => $message->getText()
                )
            );
        }
    }

    //########################################
}
