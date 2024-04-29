<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Repository
{
    const COMPONENT_GENERAL = 'general';

    const GROUP_SYSTEM  = 'system';
    const GROUP_EBAY    = 'ebay';
    const GROUP_AMAZON  = 'amazon';
    const GROUP_WALMART = 'walmart';

    /** @var array */
    public static $registeredTasks = array(
        Ess_M2ePro_Model_Cron_Task_System_Servicing_Statistic_InstructionType::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_System_ArchiveOldOrders::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_System_ClearOldLogs::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_System_FixItemTables::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_System_ConnectorCommandPending_ProcessPartial::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_System_ConnectorCommandPending_ProcessSingle::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_System_RequestPending_ProcessPartial::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_System_RequestPending_ProcessSingle::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        //todo maybe not!
        Ess_M2ePro_Model_Cron_Task_System_Processing_ProcessResult::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
            'can-work-parallel' => true
        ),
        Ess_M2ePro_Model_Cron_Task_System_Servicing_Synchronize::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectDirectlyAdded::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectDirectlyDeleted::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_Magento_Product_BulkWebsiteUpdated::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectSpecialPriceStartEndDate::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_Magento_GlobalNotifications::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
        ),
        Ess_M2ePro_Model_Cron_Task_Listing_Product_StopQueue::NICK => array(
            'component' => self::COMPONENT_GENERAL,
            'group'     => self::GROUP_SYSTEM,
            'can-work-parallel' => true
        ),

        //----------------------------------------

        Ess_M2ePro_Model_Cron_Task_Ebay_UpdateAccountsPreferences::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Channel_SynchronizeChanges::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Feedbacks_DownloadNew::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Feedbacks_SendResponse::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Other_ResolveNonReceivedData::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Other_Channel_SynchronizeData::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessInstructions::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessScheduledActions::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessActions::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
            'can-work-parallel' => true
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_RemovePotentialDuplicates::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Order_CreateFailed::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Order_UploadByUser::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Order_Update::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Order_ReserveCancel::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Order_Cancel::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),
        Ess_M2ePro_Model_Cron_Task_Ebay_Order_Refund::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'group'     => self::GROUP_EBAY,
        ),

        //----------------------------------------

        Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_ResolveTitle::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Listing_SynchronizeInventory::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Defected::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_AfnQty::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_RunVariationParentProcessors::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
            'can-work-parallel' => true
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_ProcessInstructions::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_ProcessActions::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_ProcessActionsResults::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive_Details::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive_InvoiceDataReport::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_CreateFailed::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_UploadByUser::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Update::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Update_SellerOrderId::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Refund::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Cancel::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_ReserveCancel::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_SendInvoice::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
            'can-work-parallel' => true
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessUpdate::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessRefund::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessCancel::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessResults::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_InspectProducts::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_UpdateSettings::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
        ),
        Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_Synchronize::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'group'     => self::GROUP_AMAZON,
            'can-work-parallel' => true
        ),

        //----------------------------------------

        Ess_M2ePro_Model_Cron_Task_Walmart_Listing_SynchronizeInventory::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessInstructions::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessActions::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessActionsResults::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessListActions::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Order_Receive::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Order_ReceiveWithCancellationRequested::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Order_CreateFailed::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Order_UploadByUser::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Order_Acknowledge::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Order_Shipping::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Order_Cancel::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Order_Refund::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
        Ess_M2ePro_Model_Cron_Task_Walmart_Order_ReserveCancel::NICK => array(
            'component' => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'group'     => self::GROUP_WALMART,
        ),
    );

    /** @var array */
    protected $_groupedTasks = array();

    //########################################

    public function getTaskMetadata($nick)
    {
        if (!isset(self::$registeredTasks[$nick])) {
            throw new Ess_M2ePro_Model_Exception_Logic("Unknown task nick [{$nick}]");
        }

        return self::$registeredTasks[$nick];
    }

    public function getTaskComponent($nick)
    {
        $meta = $this->getTaskMetadata($nick);
        return $meta['component'];
    }

    public function getTaskGroup($nick)
    {
        $meta = $this->getTaskMetadata($nick);
        return $meta['group'];
    }

    public function getTaskCanWorkInParallel($nick)
    {
        $meta = $this->getTaskMetadata($nick);
        return isset($meta['can-work-parallel']) && $meta['can-work-parallel'];
    }

    //########################################

    public function __construct()
    {
        foreach (self::$registeredTasks as $key => $task) {
            $this->_groupedTasks['components'][$task['component']][$key] = $task;
            $this->_groupedTasks['groups'][$task['group']][$key] = $task;

            if (!empty($task['can-work-parallel'])) {
                $this->_groupedTasks['parallel'][$key] = $task;
            }
        }
    }

    //########################################

    public function getRegisteredTasks()
    {
        return array_keys(self::$registeredTasks);
    }

    public function getComponentTasks($component)
    {
        return isset($this->_groupedTasks['components'][$component])
            ? array_keys($this->_groupedTasks['components'][$component])
            : array();
    }

    public function getGroupTasks($group)
    {
        return isset($this->_groupedTasks['groups'][$group])
            ? array_keys($this->_groupedTasks['groups'][$group])
            : array();
    }

    public function getParallelTasks()
    {
        return isset($this->_groupedTasks['parallel']) ? array_keys($this->_groupedTasks['parallel']) : array();
    }

    //########################################

    public function getRegisteredComponents()
    {
        return array(
            self::COMPONENT_GENERAL,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            Ess_M2ePro_Helper_Component_Amazon::NICK,
            Ess_M2ePro_Helper_Component_Walmart::NICK,
        );
    }

    public function getRegisteredGroups()
    {
        return array(
            self::GROUP_SYSTEM,
            self::GROUP_EBAY,
            self::GROUP_AMAZON,
            self::GROUP_WALMART,
        );
    }

    //########################################
}
