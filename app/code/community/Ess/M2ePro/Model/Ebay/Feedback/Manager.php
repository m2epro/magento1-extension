<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Feedback_Manager
{
    const ERROR_CODE_FEEDBACK_WAS_NOT_LEFT = 55;

    /** @var Ess_M2ePro_Helper_Module_Exception */
    protected $_exceptionHelper;
    /** @var Ess_M2ePro_Helper_Data */
    protected $_dataHelper;
    /** @var Ess_M2ePro_Model_Synchronization_Log */
    protected $_synchronizationLog;

    public function __construct()
    {
        $this->_exceptionHelper = Mage::helper('M2ePro/Module_Exception');
        $this->_dataHelper = Mage::helper('M2ePro');

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER);
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Feedback $feedback
     * @param string $text
     * @param string $type
     * @return bool
     */
    public function sendResponse($feedback, $text, $type = Ess_m2ePro_Model_Ebay_Feedback::TYPE_POSITIVE)
    {
        $feedback->setData('last_response_attempt_date', $this->_dataHelper->getCurrentGmtDate());
        $connectorObj = $this->sendFeedbackToServer($feedback, $text, $type);

        if ($connectorObj === null) {
            $feedback->save();

            return false;
        }

        $response = $connectorObj->getResponseData();
        $messages = $connectorObj->getResponse()->getMessages();
        $this->handleResponseMessages($messages, $feedback);

        if ($messages->hasErrorEntities() || !isset($response['feedback_id'])) {
            $feedback->save();

            return false;
        }

        $feedback->setData('seller_feedback_id', $response['feedback_id']);
        $feedback->setData('seller_feedback_type', $type);
        $feedback->setData('seller_feedback_text', $text);
        $feedback->setData('seller_feedback_date', $response['feedback_date']);
        $feedback->save();

        return true;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Feedback $feedback
     * @param string $text
     * @param string $type
     * @return Ess_M2ePro_Model_Connector_Command_RealTime_Virtual|null
     */
    protected function sendFeedbackToServer(
        $feedback,
        $text,
        $type
    ) {
        $paramsConnector = array(
            'item_id'        => $feedback->getData('ebay_item_id'),
            'transaction_id' => $feedback->getData('ebay_transaction_id'),
            'text'           => $text,
            'type'           => $type,
            'target_user'    => $feedback->getData('buyer_name')
        );

        try {
            /** @var Ess_M2ePro_Model_Ebay_Connector_Dispatcher $dispatcherObj */
            $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
            /** @var Ess_M2ePro_Model_Connector_Command_RealTime_Virtual $connectorObj */
            $connectorObj = $dispatcherObj->getVirtualConnector(
                'feedback',
                'add',
                'entity',
                $paramsConnector,
                null,
                null,
                $feedback->getAccount()
            );

            $dispatcherObj->process($connectorObj);

            return $connectorObj;
        } catch (Exception $e) {
            $this->_exceptionHelper->process($e);

            return null;
        }
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messages
     * @param Ess_M2ePro_Model_Ebay_Feedback $feedback
     *
     * @return void
     */
    protected function handleResponseMessages(
        $messages,
        $feedback
    ) {
        /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message $message */
        foreach ($messages as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            if ($message->isError()
                && $message->getCode() == self::ERROR_CODE_FEEDBACK_WAS_NOT_LEFT
            ) {
                $feedback->setData('is_critical_error_received', 1);
                continue;
            }

            $logType = $message->isError()
                ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->_synchronizationLog->addMessage(
                $this->_dataHelper->__($message->getText()),
                $logType
            );
        }
    }
}
