<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Development_Cron_EbayController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //########################################

    /**
     * @title "Update Accounts Preferences"
     */
    public function updateAccountsPreferencesAction()
    {
        $taskTitle = 'Update Accounts Preferences';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_UpdateAccountsPreferences::NICK
            )
        );

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
     * @title "Templates -> Remove Unused"
     */
    public function templatesRemoveUnusedAction()
    {
        $taskTitle = 'Templates -> Remove Unused';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Template_RemoveUnused::NICK
            )
        );

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
     * @title "Channel -> SynchronizeChanges (Orders and Items)"
     * @new_line
     */
    public function channelSynchronizeChangesAction()
    {
        $taskTitle = 'Channel -> SynchronizeChanges (Orders and Items)';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Channel_SynchronizeChanges::NICK
            )
        );

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
     * @title "Feedbacks -> Download New"
     */
    public function feedbacksDownloadNewAction()
    {
        $taskTitle = 'Feedbacks -> Download New';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Feedbacks_DownloadNew::NICK
            )
        );

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
     * @title "Feedbacks -> Send Response"
     * @new_line
     */
    public function feedbacksSendResponseAction()
    {
        $taskTitle = 'Feedbacks -> SendResponse';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Feedbacks_SendResponse::NICK
            )
        );

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
     * @title "Listing -> Other -> Resolve Sku"
     */
    public function listingOtherResolveSkuAction()
    {
        $taskTitle = 'Listing -> Other -> ResolveSku';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Other_ResolveSku::NICK
            )
        );

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
     * @title "Listing -> Other -> Channel -> SynchronizeData"
     * @new_line
     */
    public function listingOtherChannelSynchronizeDataAction()
    {
        $taskTitle = 'Listing -> Other -> Channel -> Synchronize Data';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Other_Channel_SynchronizeData::NICK
            )
        );

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
     * @title "Listing -> Product -> ProcessInstructions"
     */
    public function listingProductProcessInstructionsAction()
    {
        $taskTitle = 'Listing -> Product -> ProcessInstructions';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessInstructions::NICK
            )
        );

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
     * @title "Listing -> Product -> ProcessScheduledActions"
     */
    public function listingProductProcessScheduledActionsAction()
    {
        $taskTitle = 'Listing -> Product -> ProcessScheduledActions';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessScheduledActions::NICK
            )
        );

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
     * @title "Listing -> Product -> ProcessActions"
     */
    public function listingProductProcessActionsAction()
    {
        $taskTitle = 'Listing -> Product -> ProcessActions';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_ProcessActions::NICK
            )
        );

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
     * @title "Listing -> Product -> RemovePotentialDuplicates"
     * @new_line
     */
    public function listingProductRemovePotentialDuplicatesAction()
    {
        $taskTitle = 'Listing -> Product -> RemovePotentialDuplicates';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Listing_Product_RemovePotentialDuplicates::NICK
            )
        );

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
     * @title "Order -> CreateFailed"
     */
    public function orderCreateFailedAction()
    {
        $taskTitle = 'Order -> CreateFailed';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Order_CreateFailed::NICK
            )
        );

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
     * @title "Order -> Update"
     */
    public function orderUpdateAction()
    {
        $taskTitle = 'Order -> CreateFailed';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Order_Update::NICK
            )
        );

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
     * @title "Order -> Cancel"
     */
    public function orderCancelAction()
    {
        $taskTitle = 'Order -> Cancel';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Order_Cancel::NICK
            )
        );

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
     * @title "Order -> ReserveCancel"
     * @new_line
     */
    public function orderReserveCancelAction()
    {
        $taskTitle = 'Order -> ReserveCancel';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_Order_ReserveCancel::NICK
            )
        );

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
     * @title "PickupStore -> ScheduleForUpdate"
     */
    public function pickupStoreScheduleForUpdateAction()
    {
        $taskTitle = 'PickupStore -> ScheduleForUpdate';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_PickupStore_ScheduleForUpdate::NICK
            )
        );

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
     * @title "PickupStore -> UpdateOnChannel"
     */
    public function pickupStoreUpdateOnChannelAction()
    {
        $taskTitle = 'PickupStore -> UpdateOnChannel';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(
            array(
            Ess_M2ePro_Model_Cron_Task_Ebay_PickupStore_UpdateOnChannel::NICK
            )
        );

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
