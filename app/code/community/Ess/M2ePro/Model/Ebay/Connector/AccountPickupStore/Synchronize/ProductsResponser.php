<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_AccountPickupStore_Synchronize_ProductsResponser
    extends Ess_M2ePro_Model_Ebay_Connector_Command_Pending_Responser
{
    /** @var Ess_M2ePro_Model_Ebay_Account_PickupStore_State[] $pickupStoreStateItems */
    private $pickupStoreStateItems = array();

    /** @var Ess_M2ePro_Model_Ebay_Account_PickupStore_Log $log */
    private $log = NULL;

    //########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Connector_Connection_Response $response)
    {
        parent::__construct($params, $response);

        $collection = Mage::getResourceModel('M2ePro/Ebay_Account_PickupStore_State_Collection');
        $collection->addFieldToFilter('id', array_keys($this->params['pickup_store_state_items']));

        $this->pickupStoreStateItems = $collection->getItems();
    }

    //########################################

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            $messageText, Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
        );

        foreach ($this->pickupStoreStateItems as $stateItem) {
            if ($stateItem->isDeleted()) {
                $stateItem->deleteInstance();
                continue;
            }

            $this->logMessage($stateItem, $message);
        }
    }

    //########################################

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        return isset($responseData['messages']);
    }

    protected function processResponseData()
    {
        $responseData     = $this->getPreparedResponseData();
        $responseMessages = $responseData['messages'];

        foreach ($this->pickupStoreStateItems as $stateItem) {
            $isSuccess = true;

            if (!empty($responseMessages[$stateItem->getSku()])) {
                $messages = Mage::getModel('M2ePro/Connector_Connection_Response_Message_Set');
                $messages->init($responseMessages[$stateItem->getSku()]);

                $isSuccess = $this->processMessages($stateItem, $messages);
            }

            if (!$isSuccess) {
                if ($stateItem->isDeleted()) {
                    $stateItem->deleteInstance();
                }

                continue;
            }

            $this->processSuccess($stateItem);
        }
    }

    //########################################

    protected function processResponseMessages()
    {
        parent::processResponseMessages();

        foreach ($this->pickupStoreStateItems as $stateItem) {
            $this->processMessages($stateItem, $this->getResponse()->getMessages());
        }
    }

    //########################################

    private function processMessages(Ess_M2ePro_Model_Ebay_Account_PickupStore_State $stateItem,
                                     Ess_M2ePro_Model_Connector_Connection_Response_Message_Set $messages)
    {
        foreach ($messages->getEntities() as $message) {
            $this->logMessage($stateItem, $message);
        }

        return !$messages->hasErrorEntities();
    }

    private function processSuccess(Ess_M2ePro_Model_Ebay_Account_PickupStore_State $stateItem)
    {
        $stateItemData = $this->params['pickup_store_state_items'][$stateItem->getId()];

        $this->logMessage($stateItem, $this->getSuccessMessage($stateItemData));

        if (!$stateItem->isDeleted()) {
            $stateItem->addData(array(
                'online_qty' => $stateItemData['target_qty'],
                'is_added'   => 0,
                'is_deleted' => 0,
            ));
            $stateItem->save();
        } else {
            $stateItem->deleteInstance();
        }
    }

    /**
     * @param array $stateItemData
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function getSuccessMessage(array $stateItemData)
    {
        $encodedDescription = NULL;

        switch ($this->getLogsAction($stateItemData)) {
            case Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_ADD_PRODUCT:
                $encodedDescription = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'The Product with %qty% quantity was successfully added to the Store.',
                    array('!qty' => $stateItemData['target_qty'])
                );
                break;

            case Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_DELETE_PRODUCT:
                $encodedDescription = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'The Product was successfully deleted from the Store.'
                );
                break;

            case Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_UPDATE_QTY:
                $stockFrom = '';
                $stockTo   = '';
                if ((int)$stateItemData['target_qty'] == 0) {
                    $stockFrom = 'IN STOCK ';
                    $stockTo   = 'OUT OF STOCK ';
                } elseif ((int)$stateItemData['online_qty'] == 0) {
                    $stockFrom = 'OUT OF STOCK ';
                    $stockTo   = 'IN STOCK ';
                }

                $encodedDescription = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'The Product quantity was successfully changed from %stock_from%[%qty_from%]
                    to %stock_to%[%qty_to%] for the Store.',
                    array(
                        '!qty_from'   => $stateItemData['online_qty'],
                        '!qty_to'     => $stateItemData['target_qty'],
                        '!stock_from' => $stockFrom,
                        '!stock_to'   => $stockTo,
                    )
                );
                break;

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown logs action type');
        }

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            $encodedDescription, Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS
        );

        return $message;
    }

    //########################################

    private function logMessage(Ess_M2ePro_Model_Ebay_Account_PickupStore_State $stateItem,
                                Ess_M2ePro_Model_Connector_Connection_Response_Message $message)
    {
        $this->getLog()->addMessage(
            $stateItem->getId(),
            $this->params['logs_action_id'],
            $this->getLogsAction($stateItem),
            $message->getText(),
            $this->getLogsMessageType($message),
            $this->getLogsPriority($message)
        );
    }

    // ---------------------------------------

    private function getLogsAction($stateItemData)
    {
        if ($stateItemData['is_added']) {
            return Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_ADD_PRODUCT;
        }

        if ($stateItemData['is_deleted']) {
            return Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_DELETE_PRODUCT;
        }

        return Ess_M2ePro_Model_Ebay_Account_PickupStore_Log::ACTION_UPDATE_QTY;
    }

    private function getLogsMessageType(Ess_M2ePro_Model_Connector_Connection_Response_Message $message)
    {
        if ($message->isError()) {
            return Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
        }

        if ($message->isWarning()) {
            return Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
        }

        if ($message->isSuccess()) {
            return Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;
        }

        if ($message->isNotice()) {
            return Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE;
        }

        return Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
    }

    private function getLogsPriority(Ess_M2ePro_Model_Connector_Connection_Response_Message $message)
    {
        if ($message->isError()) {
            return Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
        }

        if ($message->isNotice()) {
            return Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW;
        }

        return Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;
    }

    //########################################

    private function getLog()
    {
        if (!is_null($this->log)) {
            return $this->log;
        }

        return $this->log = Mage::getModel('M2ePro/Ebay_Account_PickupStore_Log');
    }

    //########################################
}