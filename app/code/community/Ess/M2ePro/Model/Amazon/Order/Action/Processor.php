<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Action_Processor
{
    const PENDING_REQUEST_MAX_LIFE_TIME = 86400;
    const MAX_ITEMS_PER_REQUEST = 10000;

    protected $_actionType = null;

    //########################################

    public function __construct($args)
    {
        if (empty($args['action_type'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Action Type is not defined.');
        }

        $this->_actionType = $args['action_type'];
    }

    //########################################

    public function process()
    {
        $this->removeMissedProcessingActions();

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountCollection */
        $accountCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');

        $merchantIds = array_unique($accountCollection->getColumnValues('merchant_id'));
        if (empty($merchantIds)) {
            return;
        }

        foreach ($merchantIds as $merchantId) {
            $this->processAction($merchantId);
        }
    }

    //########################################

    protected function processAction($merchantId)
    {
        if (!$this->isTimeToProcess($merchantId)) {
            return;
        }

        $processingActions = $this->getNotProcessedActions($merchantId);
        if (empty($processingActions)) {
            return;
        }

        $throttlingManager = Mage::getSingleton('M2ePro/Amazon_ThrottlingManager');

        if ($throttlingManager->getAvailableRequestsCount(
            $merchantId, Ess_M2ePro_Model_Amazon_ThrottlingManager::REQUEST_TYPE_FEED
        ) < 1) {
            return;
        }

        $this->setLastProcessDate($merchantId);

        $requestDataKey = $this->getRequestDataKey();

        $requestData = array(
            $requestDataKey => array(),
            'accounts'      => array(),
        );

        foreach ($processingActions as $processingAction) {
            $requestData[$requestDataKey][$processingAction->getOrderId()] = $processingAction->getRequestData();
        }

        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountsCollection */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('merchant_id', $merchantId);

        /** @var Ess_M2ePro_Model_Account[] $accounts */
        $accounts = $accountsCollection->getItems();

        foreach ($accounts as $account) {
            /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
            $amazonAccount = $account->getChildObject();
            $requestData['accounts'][] = $amazonAccount->getServerHash();
        }

        /** @var Ess_M2ePro_Model_Amazon_Connector_Dispatcher $dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');

        $command = $this->getServerCommand();

        $connector = $dispatcher->getVirtualConnector(
            $command[0], $command[1], $command[2],
            $requestData, null, null
        );

        try {
            $dispatcher->process($connector);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromException($exception);

            foreach ($processingActions as $processingAction) {
                $this->completeProcessingAction($processingAction, array('messages' => array($message->asArray())));
            }

            return;
        }

        $throttlingManager->registerRequests(
            $merchantId, Ess_M2ePro_Model_Amazon_ThrottlingManager::REQUEST_TYPE_FEED, 1
        );

        $responseData = $connector->getResponseData();
        $responseMessages = $connector->getResponseMessages();

        if (empty($responseData['processing_id'])) {
            foreach ($processingActions as $processingAction) {
                $messages = $this->getResponseMessages(
                    $responseData, $responseMessages, $processingAction->getOrderId()
                );
                $this->completeProcessingAction($processingAction, array('messages' => $messages));
            }

            return;
        }

        $requestPendingSingle = Mage::getModel('M2ePro/Request_Pending_Single');
        $requestPendingSingle->setData(
            array(
                'component'       => Ess_M2ePro_Helper_Component_Amazon::NICK,
                'server_hash'     => $responseData['processing_id'],
                'expiration_date' => gmdate(
                    'Y-m-d H:i:s',
                    Mage::helper('M2ePro')->getCurrentGmtDate(true)
                        + self::PENDING_REQUEST_MAX_LIFE_TIME
                )
            )
        );
        $requestPendingSingle->save();

        $actionsIds = array();
        foreach ($processingActions as $processingAction) {
            $actionsIds[] = $processingAction->getId();
        }

        Mage::getResourceModel('M2ePro/Amazon_Order_Action_Processing')->markAsInProgress(
            $actionsIds, $requestPendingSingle
        );
    }

    //########################################

    /**
     * @param $merchantId
     * @return Ess_M2ePro_Model_Amazon_Order_Action_Processing[]
     */
    protected function getNotProcessedActions($merchantId)
    {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Order_Action_Processing_Collection');
        $collection->getSelect()->joinLeft(
            array('o' => Mage::getResourceModel('M2ePro/Order')->getMainTable()),
            'o.id = main_table.order_id', array()
        );
        $collection->getSelect()->joinLeft(
            array('aa' => Mage::getResourceModel('M2ePro/Amazon_Account')->getMainTable()),
            'aa.account_id = o.account_id', array()
        );
        $collection->setNotProcessedFilter();
        $collection->addFieldToFilter('aa.merchant_id', $merchantId);
        $collection->addFieldToFilter('main_table.type', $this->_actionType);
        $collection->getSelect()->limit(self::MAX_ITEMS_PER_REQUEST);

        return $collection->getItems();
    }

    protected function completeProcessingAction(Ess_M2ePro_Model_Amazon_Order_Action_Processing $action, array $data)
    {
        $processing = $action->getProcessing();

        $processing->setSettings('result_data', $data);
        $processing->setData('is_completed', 1);

        $processing->save();

        $action->deleteInstance();
    }

    protected function getResponseMessages(array $responseData, array $responseMessages, $orderId)
    {
        $messages = $responseMessages;

        if (!empty($responseData['messages'][0])) {
            $messages = array_merge($messages, $responseData['messages']['0']);
        }

        if (!empty($responseData['messages']['0-id'])) {
            $messages = array_merge($messages, $responseData['messages']['0-id']);
        }

        if (!empty($responseData['messages'][$orderId.'-id'])) {
            $messages = array_merge($messages, $responseData['messages'][$orderId.'-id']);
        }

        return $messages;
    }

    private function isTimeToProcess($merchantId)
    {
        /** @var Ess_M2ePro_Model_Amazon_Order_Action_TimeManager $timeManager */
        $timeManager = Mage::getModel('M2ePro/Amazon_Order_Action_TimeManager');

        switch ($this->_actionType) {
            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_UPDATE:
                return $timeManager->isTimeToProcessUpdate($merchantId);

            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_CANCEL:
                return $timeManager->isTimeToProcessCancel($merchantId);

            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_REFUND:
                return $timeManager->isTimeToProcessRefund($merchantId);

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown action type');
        }
    }

    private function setLastProcessDate($merchantId)
    {
        /** @var Ess_M2ePro_Model_Amazon_Order_Action_TimeManager $timeManager */
        $timeManager = Mage::getModel('M2ePro/Amazon_Order_Action_TimeManager');

        switch ($this->_actionType) {
            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_UPDATE:
                $timeManager->setLastUpdate($merchantId, Mage::helper('M2ePro')->createCurrentGmtDateTime());
                break;

            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_CANCEL:
                $timeManager->setLastCancel($merchantId, Mage::helper('M2ePro')->createCurrentGmtDateTime());
                break;

            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_REFUND:
                $timeManager->setLastRefund($merchantId, Mage::helper('M2ePro')->createCurrentGmtDateTime());
                break;

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown action type');
        }
    }

    protected function getServerCommand()
    {
        switch ($this->_actionType) {
            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_UPDATE:
                return array('orders', 'update', 'entities');

            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_REFUND:
                return array('orders', 'refund', 'entities');

            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_CANCEL:
                return array('orders', 'cancel', 'entities');

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown action type');
        }
    }

    protected function getRequestDataKey()
    {
        switch ($this->_actionType) {
            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_UPDATE:
                return 'items';

            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_REFUND:
            case Ess_M2ePro_Model_Amazon_Order_Action_Processing::ACTION_TYPE_CANCEL:
                return 'orders';

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown action type');
        }
    }

    //########################################

    protected function removeMissedProcessingActions()
    {
        $actionCollection = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Action_Processing_Collection');
        $actionCollection->getSelect()->joinLeft(
            array('p' => Mage::getResourceModel('M2ePro/Processing')->getMainTable()),
            'p.id = main_table.processing_id',
            array()
        );
        $actionCollection->addFieldToFilter('p.id', array('null' => true));

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Processing[] $actions */
        $actions = $actionCollection->getItems();

        foreach ($actions as $action) {
            $action->deleteInstance();
        }
    }

    //########################################
}
