<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive_Details_Responser
    extends Ess_M2ePro_Model_Amazon_Connector_Orders_Get_Details_ItemsResponser
{
    protected $_synchronizationLog = null;

    //########################################

    protected function processResponseMessages(array $messages = array())
    {
        parent::processResponseMessages();

        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError() && !$message->isWarning()) {
                continue;
            }

            $logType = $message->isError() ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message->getText()),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

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

    //########################################

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($messageText),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    //########################################

    protected function processResponseData()
    {
        $responseData = $this->getPreparedResponseData();

        $amazonOrdersIds = array();
        foreach ($responseData['data'] as $details) {
            $amazonOrdersIds[] = $details['amazon_order_id'];
        }

        $amazonOrdersIds = array_unique($amazonOrdersIds);
        if (empty($amazonOrdersIds)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Order_Collection $ordersCollection */
        $ordersCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');
        $ordersCollection->addFieldToFilter('amazon_order_id', array('in' => $amazonOrdersIds));

        foreach ($responseData['data'] as $details) {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = $ordersCollection->getItemByColumnValue('amazon_order_id', $details['amazon_order_id']);
            if ($order === null) {
                continue;
            }

            unset($details['amazon_order_id']);

            $additionalData = $order->getAdditionalData();
            $additionalData['fulfillment_details'] = $details;
            $order->setSettings('additional_data', $additionalData)->save();
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account', 'account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        if ($this->_synchronizationLog !== null) {
            return $this->_synchronizationLog;
        }

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        return $this->_synchronizationLog;
    }

    //########################################
}
