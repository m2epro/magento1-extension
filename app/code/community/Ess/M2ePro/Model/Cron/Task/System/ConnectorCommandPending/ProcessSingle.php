<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_System_ConnectorCommandPending_ProcessSingle
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'system/connector_command_pending/process_single';

    //####################################

    protected function performActions()
    {
        $this->removeMissedProcessingItems();
        $this->completeExpiredItems();
        $this->processCompletedItems();
    }

    //####################################

    private function removeMissedProcessingItems()
    {
        $collection = Mage::getResourceModel('M2ePro/Connector_Command_Pending_Processing_Single_Collection');
        $collection->getSelect()->joinLeft(
            array('p' => Mage::getResourceModel('M2ePro/Processing')->getMainTable()),
            'p.id = main_table.processing_id',
            array()
        );
        $collection->addFieldToFilter('p.id', array('null' => true));

        /** @var Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single[] $failedItems */
        $failedItems = $collection->getItems();
        if (empty($failedItems)) {
            return;
        }

        foreach ($failedItems as $failedItem) {
            $requestPendingSingle = $failedItem->getRequestPendingSingle();
            if ($requestPendingSingle->getId()) {
                $requestPendingSingle->deleteInstance();
            }

            $failedItem->deleteInstance();
        }
    }

    private function completeExpiredItems()
    {
        $collection = Mage::getResourceModel('M2ePro/Connector_Command_Pending_Processing_Single_Collection');
        $collection->getSelect()->joinLeft(
            array('rps' => Mage::getResourceModel('M2ePro/Request_Pending_Single')->getMainTable()),
            'rps.id = main_table.request_pending_single_id',
            array()
        );
        $collection->addFieldToFilter('rps.id', array('null' => true));

        /** @var Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single[] $expiredItems */
        $expiredItems = $collection->getItems();
        if (empty($expiredItems)) {
            return;
        }

        $expiredMessage = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $expiredMessage->initFromPreparedData(
            'Request wait timeout exceeded.',
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
        );

        foreach ($expiredItems as $expiredItem) {
            $this->completeRequesterPendingSingle($expiredItem, array(), array($expiredMessage->asArray()));
        }
    }

    private function processCompletedItems()
    {
        $collection = Mage::getResourceModel('M2ePro/Connector_Command_Pending_Processing_Single_Collection');
        $collection->setCompletedRequestPendingSingleFilter();
        $collection->setNotCompletedProcessingFilter();

        /** @var Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single[] $requesterSingleObjects */
        $requesterSingleObjects = $collection->getItems();

        foreach ($requesterSingleObjects as $requesterSingleObject) {
            $this->completeRequesterPendingSingle(
                $requesterSingleObject,
                $requesterSingleObject->getRequestPendingSingle()->getResultData(),
                $requesterSingleObject->getRequestPendingSingle()->getResultMessages()
            );
        }
    }

    //####################################

    private function completeRequesterPendingSingle(
        Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single $requesterPendingSingle,
        array $data = array(), array $messages = array()
    ) {
        $processing = $requesterPendingSingle->getProcessing();

        $processing->setSettings('result_data', $data);
        $processing->setSettings('result_messages', $messages);
        $processing->setData('is_completed', 1);
        $processing->save();

        $requesterPendingSingle->deleteInstance();
    }

    //####################################
}