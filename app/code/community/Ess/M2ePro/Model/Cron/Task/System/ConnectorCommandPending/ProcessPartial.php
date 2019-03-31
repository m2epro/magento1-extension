<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_System_ConnectorCommandPending_ProcessPartial
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'system/connector_command_pending/process_partial';

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
        $collection = Mage::getResourceModel('M2ePro/Connector_Command_Pending_Processing_Partial_Collection');
        $collection->getSelect()->joinLeft(
            array('p' => Mage::getResourceModel('M2ePro/Processing')->getMainTable()),
            'p.id = main_table.processing_id',
            array()
        );
        $collection->addFieldToFilter('p.id', array('null' => true));

        /** @var Ess_M2ePro_Model_Connector_Command_Pending_Processing_Partial[] $failedItems */
        $failedItems = $collection->getItems();
        if (empty($failedItems)) {
            return;
        }

        foreach ($failedItems as $failedItem) {
            $requestPendingPartial = $failedItem->getRequestPendingPartial();
            if ($requestPendingPartial->getId()) {
                $requestPendingPartial->deleteInstance();
            }

            $failedItem->deleteInstance();
        }
    }

    private function completeExpiredItems()
    {
        $collection = Mage::getResourceModel('M2ePro/Connector_Command_Pending_Processing_Partial_Collection');
        $collection->getSelect()->joinLeft(
            array('rpp' => Mage::getResourceModel('M2ePro/Request_Pending_Partial')->getMainTable()),
            'rpp.id = main_table.request_pending_partial_id',
            array()
        );
        $collection->addFieldToFilter('rpp.id', array('null' => true));

        /** @var Ess_M2ePro_Model_Connector_Command_Pending_Processing_Partial[] $expiredItems */
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
            $processing = $expiredItem->getProcessing();

            $processing->setSettings('result_messages', array($expiredMessage->asArray()));
            $processing->setData('is_completed', 1);
            $processing->save();

            $expiredItem->deleteInstance();
        }
    }

    private function processCompletedItems()
    {
        $collection = Mage::getResourceModel('M2ePro/Connector_Command_Pending_Processing_Partial_Collection');
        $collection->setCompletedRequestPendingPartialFilter();
        $collection->setNotCompletedProcessingFilter();

        /** @var Ess_M2ePro_Model_Connector_Command_Pending_Processing_Partial[] $requesterPartialObjects */
        $requesterPartialObjects = $collection->getItems();

        foreach ($requesterPartialObjects as $requesterPartialObject) {

            $processing = $requesterPartialObject->getProcessing();
            $processing->setSettings(
                'result_data',
                array('request_pending_partial_id' => $requesterPartialObject->getRequestPendingPartialId())
            );
            $processing->setData('is_completed', 1);
            $processing->save();

            $requesterPartialObject->deleteInstance();
        }
    }

    //####################################
}