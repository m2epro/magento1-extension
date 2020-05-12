<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_SendInvoice_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Orders_SendInvoice_ItemsResponser
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

        $this->_order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
        $this->_order->addErrorLog('Amazon Order invoice was not send. Reason: %msg%', array('msg' => $messageText));
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

                if ($this->_params['order']['document_type'] == Ess_M2ePro_Model_Amazon_Order::DOCUMENT_TYPE_INVOICE) {
                    $this->_order->addErrorLog(
                        'Invoice #%document_number% was not sent. Reason: %msg%',
                        array(
                            'document_number' => $this->_params['order']['document_number'],
                            'msg' => $message->getText()
                        )
                    );
                } elseif ($this->_params['order']['document_type'] ==
                    Ess_M2ePro_Model_Amazon_Order::DOCUMENT_TYPE_CREDIT_NOTE) {
                    $this->_order->addErrorLog(
                        'Credit Memo #%document_number% was not sent. Reason: %msg%',
                        array(
                            'document_number' => $this->_params['order']['document_number'],
                            'msg' => $message->getText()
                        )
                    );
                }
            } else {
                $this->_order->addWarningLog($message->getText());
            }
        }

        if ($isFailed) {
            return;
        }

        if ($this->_params['order']['document_type'] == Ess_M2ePro_Model_Amazon_Order::DOCUMENT_TYPE_INVOICE) {
            $this->_order->getChildObject()->setData('is_invoice_sent', 1)->save();
            $this->_order->addSuccessLog(
                'Invoice #%document_number% was sent.',
                array(
                    'document_number' => $this->_params['order']['document_number'],
                )
            );
        } elseif ($this->_params['order']['document_type'] ==
            Ess_M2ePro_Model_Amazon_Order::DOCUMENT_TYPE_CREDIT_NOTE) {
            $this->_order->getChildObject()->setData('is_credit_memo_sent', 1)->save();
            $this->_order->addSuccessLog(
                'Credit Memo #%document_number% was sent.',
                array(
                    'document_number' => $this->_params['order']['document_number'],
                )
            );
        }
    }

    //########################################
}
