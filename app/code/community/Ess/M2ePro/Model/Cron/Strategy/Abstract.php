<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Cron_Strategy_Abstract
{
    const INITIALIZATION_TRANSACTIONAL_LOCK_NICK = 'cron_strategy_initialization';

    const PROGRESS_START_EVENT_NAME           = 'm2epro_cron_progress_start';
    const PROGRESS_SET_PERCENTAGE_EVENT_NAME  = 'm2epro_cron_progress_set_percentage';
    const PROGRESS_SET_DETAILS_EVENT_NAME     = 'm2epro_cron_progress_set_details';
    const PROGRESS_STOP_EVENT_NAME            = 'm2epro_cron_progress_stop';

    protected $_initiator = null;

    protected $_allowedTasks = null;

    /**
     * @var Ess_M2ePro_Model_Cron_OperationHistory
     */
    protected $_operationHistory = null;

    /**
     * @var Ess_M2ePro_Model_Cron_OperationHistory
     */
    protected $_parentOperationHistory = null;

    //########################################

    public function setInitiator($initiator)
    {
        $this->_initiator = $initiator;
        return $this;
    }

    public function getInitiator()
    {
        return $this->_initiator;
    }

    // ---------------------------------------

    /**
     * @param array $tasks
     * @return $this
     */
    public function setAllowedTasks(array $tasks)
    {
        $this->_allowedTasks = $tasks;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getAllowedTasks()
    {
        if ($this->_allowedTasks !== null) {
            return $this->_allowedTasks;
        }

        return $this->_allowedTasks = array(
            Ess_M2ePro_Model_Cron_Task_System_ArchiveOldOrders::NICK,
            Ess_M2ePro_Model_Cron_Task_System_ClearOldLogs::NICK,
            Ess_M2ePro_Model_Cron_Task_System_ConnectorCommandPending_ProcessPartial::NICK,
            Ess_M2ePro_Model_Cron_Task_System_ConnectorCommandPending_ProcessSingle::NICK,
            Ess_M2ePro_Model_Cron_Task_System_IssuesResolver_RemoveMissedProcessingLocks::NICK,
            Ess_M2ePro_Model_Cron_Task_System_Processing_ProcessResult::NICK,
            Ess_M2ePro_Model_Cron_Task_System_RequestPending_ProcessPartial::NICK,
            Ess_M2ePro_Model_Cron_Task_System_RequestPending_ProcessSingle::NICK,
            Ess_M2ePro_Model_Cron_Task_System_Servicing_Synchronize::NICK,
            Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectDirectlyAdded::NICK,
            Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectDirectlyDeleted::NICK,
            Ess_M2ePro_Model_Cron_Task_Magento_GlobalNotifications::NICK,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::NICK,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_AutoActions_ProcessMagentoProductWebsitesUpdates::NICK,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_StopQueue_Process::NICK,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_StopQueue_RemoveOld::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_UpdateAccountsPreferences::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Template_RemoveUnused::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Channel_SynchronizeChanges::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Feedbacks_DownloadNew::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Feedbacks_SendResponse::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Other_ResolveSku::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Other_Channel_SynchronizeData::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessInstructions::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessScheduledActions::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessActions::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_RemovePotentialDuplicates::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Order_CreateFailed::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Order_Update::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_Order_ReserveCancel::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_PickupStore_ScheduleForUpdate::NICK,
            Ess_M2ePro_Model_Cron_Task_Ebay_PickupStore_UpdateOnChannel::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_ResolveTitle::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData_Blocked::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Blocked::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Defected::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_RunVariationParentProcessors::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_ProcessInstructions::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_ProcessActions::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_ProcessActionsResults::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive_Details::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_CreateFailed::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Update::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Update_SellerOrderId::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Refund::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Cancel::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_ReserveCancel::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessUpdate::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessRefund::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessCancel::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessResults::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_InspectProducts::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_UpdateSettings::NICK,
            Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_Synchronize::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_Channel_SynchronizeData::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_Channel_SynchronizeData_Blocked::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Other_Channel_SynchronizeData::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessInstructions::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessActions::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessActionsResults::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessListActions::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Order_Receive::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Order_Acknowledge::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Order_Shipping::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Order_Cancel::NICK,
            Ess_M2ePro_Model_Cron_Task_Walmart_Order_Refund::NICK,
        );
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Cron_OperationHistory $operationHistory
     * @return $this
     */
    public function setParentOperationHistory(Ess_M2ePro_Model_Cron_OperationHistory $operationHistory)
    {
        $this->_parentOperationHistory = $operationHistory;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Cron_OperationHistory
     */
    public function getParentOperationHistory()
    {
        return $this->_parentOperationHistory;
    }

    //########################################

    abstract protected function getNick();

    //########################################

    public function process()
    {
        $this->beforeStart();

        try {
            $result = $this->processTasks();
        } catch (Exception $exception) {
            $result = false;

            $this->getOperationHistory()->addContentData(
                'exceptions', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
                )
            );

            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }

        $this->afterEnd();

        return $result;
    }

    // ---------------------------------------

    /**
     * @param $taskNick
     * @return Ess_M2ePro_Model_Cron_Task_Abstract
     */
    protected function getTaskObject($taskNick)
    {
        $taskNick = preg_replace_callback(
            '/_([a-z])/i', function($matches) {
            return ucfirst($matches[1]);
            }, $taskNick
        );

        $taskNick = preg_replace_callback(
            '/\/([a-z])/i', function($matches) {
            return '_' . ucfirst($matches[1]);
            }, $taskNick
        );

        $taskNick = ucfirst($taskNick);

        /** @var $task Ess_M2ePro_Model_Cron_Task_Abstract **/
        $task = Mage::getModel('M2ePro/Cron_Task_'.trim($taskNick));

        $task->setInitiator($this->getInitiator());
        $task->setParentOperationHistory($this->getOperationHistory());

        return $task;
    }

    abstract protected function processTasks();

    //########################################

    protected function beforeStart()
    {
        $parentId = $this->getParentOperationHistory()
            ? $this->getParentOperationHistory()->getObject()->getId() : null;
        $this->getOperationHistory()->start('cron_strategy_'.$this->getNick(), $parentId, $this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();
    }

    protected function afterEnd()
    {
        $this->getOperationHistory()->stop();
    }

    //########################################

    protected function keepAliveStart(Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager)
    {
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_KeepAlive')->enable();
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_KeepAlive')->setLockItemManager($lockItemManager);
    }

    protected function keepAliveStop()
    {
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_KeepAlive')->disable();
    }

    //########################################

    protected function startListenProgressEvents(Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager)
    {
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_Progress')->enable();
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_Progress')->setLockItemManager($lockItemManager);
    }

    protected function stopListenProgressEvents()
    {
        Mage::getSingleton('M2ePro/Cron_Strategy_Observer_Progress')->disable();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Cron_OperationHistory
     */
    protected function getOperationHistory()
    {
        if ($this->_operationHistory !== null) {
            return $this->_operationHistory;
        }

        return $this->_operationHistory = Mage::getModel('M2ePro/Cron_OperationHistory');
    }

    protected function makeLockItemShutdownFunction(Ess_M2ePro_Model_Lock_Item_Manager $lockItemManager)
    {
        $lockItem = Mage::getModel('M2ePro/Lock_Item')->load($lockItemManager->getNick(), 'nick');
        if (!$lockItem->getId()) {
            return;
        }

        $id = $lockItem->getId();

        register_shutdown_function(
            function() use ($id)
            {
            $error = error_get_last();
            if ($error === null || !in_array((int)$error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR))) {
                return;
            }

            $lockItem = Mage::getModel('M2ePro/Lock_Item')->load($id);
            if ($lockItem->getId()) {
                $lockItem->delete();
            }
            }
        );
    }

    //########################################
}