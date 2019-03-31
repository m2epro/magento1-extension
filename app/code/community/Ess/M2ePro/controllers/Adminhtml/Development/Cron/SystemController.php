<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Development_Cron_SystemController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //########################################

    /**
     * @title "Run All Tasks"
     * @new_line
     */
    public function runCronAction()
    {
        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess('Cron was successfully performed.');
        } else {
            $this->_getSession()->addError('Cron was performed with errors.');
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    //########################################

    /**
     * @title "Archive Old Orders"
     */
    public function archiveOldOrdersAction()
    {
        $taskTitle = 'Archive Old Orders';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_System_ArchiveOldOrders::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    /**
     * @title "Clean Old Logs"
     * @new_line
     */
    public function clearOldLogsAction()
    {
        $taskTitle = 'Clean Old Logs';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_System_ClearOldLogs::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    //########################################

    /**
     * @title "Connector -> CommandPending -> Process Partial"
     */
    public function connectorCommandPendingProcessPartialAction()
    {
        $taskTitle = 'Connector -> CommandPending -> Process Partial';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_System_ConnectorCommandPending_ProcessPartial::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    /**
     * @title "Connector -> CommandPending -> Process Single"
     * @new_line
     */
    public function connectorCommandPendingProcessSingleAction()
    {
        $taskTitle = 'Connector -> CommandPending -> Process Single';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_System_ConnectorCommandPending_ProcessSingle::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    //########################################

    /**
     * @title "Servicing -> Synchronize"
     */
    public function servicingSynchronizeAction()
    {
        $taskTitle = 'Servicing -> Synchronize';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_System_Servicing_Synchronize::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    /**
     * @title "IssuesResolver -> Remove Missed Processing Locks"
     */
    public function issuesResolverRemoveMissedProcessingLocksAction()
    {
        $taskTitle = 'IssuesResolver -> Remove Missed Processing Locks';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_System_IssuesResolver_RemoveMissedProcessingLocks::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    /**
     * @title "Processing -> Process Result"
     * @new_line
     */
    public function processingProcessResultAction()
    {
        $taskTitle = 'Processing -> Process Result';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_System_Processing_ProcessResult::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    //########################################

    /**
     * @title "Request Pending -> Process Partial"
     */
    public function requestPendingProcessPartialAction()
    {
        $taskTitle = 'Request Pending -> Process Partial';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_System_RequestPending_ProcessPartial::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    /**
     * @title "Request Pending -> Process Single"
     * @new_line
     */
    public function requestPendingProcessSingleAction()
    {
        $taskTitle = 'Request Pending -> Process Single';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_System_RequestPending_ProcessSingle::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    //########################################

    /**
     * @title "Magento Product -> Detect Directly Added"
     */
    public function magentoProductDetectDirectlyAddedAction()
    {
        $taskTitle = 'Magento Product -> Detect Directly Added';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectDirectlyAdded::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    /**
     * @title "Magento Product -> Detect Directly Deleted"
     * @new_line
     */
    public function magentoProductDetectDirectlyDeletedAction()
    {
        $taskTitle = 'Magento Product -> Detect Directly Added';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectDirectlyDeleted::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    //########################################

    /**
     * @title "Magento -> Global Notifications"
     * @new_line
     */
    public function magentoGlobalNotificationsAction()
    {
        $taskTitle = 'Magento -> Global Notifications';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
             Ess_M2ePro_Model_Cron_Task_Magento_GlobalNotifications::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    //########################################

    /**
     * @title "Listing -> Product -> Inspect Direct Changes"
     */
    public function listingProductInspectDirectChangesAction()
    {
        $taskTitle = 'Listing -> Product -> Inspect Direct Changes';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    /**
     * @title "Listing -> Product -> Process Revise Total"
     */
    public function listingProductProcessReviseTotalAction()
    {
        $taskTitle = 'Listing -> Product -> Process Revise Total';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Listing_Product_ProcessReviseTotal::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    /**
     * @title "Listing -> Product -> AutoActions -> Magento Product Websites Updates"
     */
    public function listingProductAutoActionsMagentoProductWebsitesUpdatesAction()
    {
        $taskTitle = 'Listing -> Product -> AutoActions -> Magento Product Websites Updates';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Listing_Product_AutoActions_ProcessMagentoProductWebsitesUpdates::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    /**
     * @title "Listing -> Product -> StopQueue -> Process"
     */
    public function listingProductStopQueueProcessAction()
    {
        $taskTitle = 'Listing -> Product -> StopQueue -> Process';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Listing_Product_StopQueue_Process::NICK
        ));

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess("{$taskTitle} was successfully performed.");
        } else {
            $this->_getSession()->addError("{$taskTitle} was performed with errors.");
        }

        return $this->getResponse()->setBody(
            '<pre>'.$cronRunner->getOperationHistory()->getFullDataInfo().'</pre>'
        );
    }

    //########################################
}