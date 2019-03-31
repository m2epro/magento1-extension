<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Development_Cron_AmazonController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //########################################

    /**
     * @title "Listing -> Other -> Resolve Title"
     */
    public function listingOtherResolveTitleAction()
    {
        $taskTitle = 'Listing -> Other -> Resolve Title';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_ResolveTitle::NICK
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
     * @title "Listing -> Other -> Channel -> SynchronizeData"
     */
    public function listingOtherChannelSynchronizeDataAction()
    {
        $taskTitle = 'Listing -> Other -> Channel -> SynchronizeData';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData::NICK
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
     * @title "Listing -> Other -> Channel -> SynchronizeData -> Blocked"
     * @new_line
     */
    public function listingOtherChannelSynchronizeDataBlockedAction()
    {
        $taskTitle = 'Listing -> Other -> Channel -> SynchronizeData -> Blocked';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData_Blocked::NICK
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
     * @title "Listing -> Product -> Channel -> SynchronizeData"
     */
    public function listingProductChannelSynchronizeDataAction()
    {
        $taskTitle = 'Listing -> Product -> Channel -> SynchronizeData';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData::NICK
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
     * @title "Listing -> Product -> Channel -> SynchronizeData -> Blocked"
     */
    public function listingProductChannelSynchronizeDataBlockedAction()
    {
        $taskTitle = 'Listing -> Product -> Channel -> SynchronizeData -> Blocked';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Blocked::NICK
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
     * @title "Listing -> Product -> Channel -> SynchronizeData -> Defected"
     * @new_line
     */
    public function listingProductChannelSynchronizeDataDefectedAction()
    {
        $taskTitle = 'Listing -> Product -> Channel -> SynchronizeData -> Defected';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Defected::NICK
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
     * @title "Listing -> Product -> RunVariationParentProcessors"
     */
    public function listingProductRunVariationParentProcessorsAction()
    {
        $taskTitle = 'Listing -> Product -> RunVariationParentProcessors';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_RunVariationParentProcessors::NICK
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
     * @title "Listing -> Product -> ProcessInstructions"
     */
    public function listingProductRunProcessInstructionsAction()
    {
        $taskTitle = 'Listing -> Product -> ProcessInstructions';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_ProcessInstructions::NICK
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
     * @title "Listing -> Product -> ProcessActions"
     */
    public function listingProductRunProcessActionsAction()
    {
        $taskTitle = 'Listing -> Product -> ProcessActions';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_ProcessActions::NICK
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
     * @title "Listing -> Product -> ProcessActionsResults"
     * @new_line
     */
    public function listingProductProcessActionsResultsAction()
    {
        $taskTitle = 'Listing -> Product -> ProcessActionsResults';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Listing_Product_ProcessActionsResults::NICK
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
     * @title "Order -> Receive"
     */
    public function orderReceiveAction()
    {
        $taskTitle = 'Order -> Receive';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive::NICK
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
     * @title "Order -> Receive Details"
     */
    public function orderReceiveDetailsAction()
    {
        $taskTitle = 'Order -> Receive Details';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Receive_Details::NICK
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
     * @title "Order -> CreateFailed"
     */
    public function orderCreateFailedAction()
    {
        $taskTitle = 'Order -> CreateFailed';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_CreateFailed::NICK
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
     * @title "Order -> Update"
     */
    public function orderUpdateAction()
    {
        $taskTitle = 'Order -> Update';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Update::NICK
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
     * @title "Order -> Refund"
     */
    public function orderRefundAction()
    {
        $taskTitle = 'Order -> Refund';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Refund::NICK
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
     * @title "Order -> Cancel"
     */
    public function orderCancelAction()
    {
        $taskTitle = 'Order -> Cancel';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Cancel::NICK
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
     * @title "Order -> Reserve Cancel"
     * @new_line
     */
    public function orderReserveCancelAction()
    {
        $taskTitle = 'Order -> Reserve Cancel';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_ReserveCancel::NICK
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
     * @title "Order -> Action -> Process Update"
     */
    public function orderActionProcessUpdateAction()
    {
        $taskTitle = 'Order -> Action -> Process Update';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessUpdate::NICK
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
     * @title "Order -> Action -> Process Refund"
     */
    public function orderActionProcessRefundAction()
    {
        $taskTitle = 'Order -> Action -> Process Refund';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessRefund::NICK
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
     * @title "Order -> Action -> Process Cancel"
     */
    public function orderActionProcessCancelAction()
    {
        $taskTitle = 'Order -> Action -> Process Cancel';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessCancel::NICK
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
     * @title "Order -> Action -> Process Results"
     * @new_line
     */
    public function orderActionProcessResultsAction()
    {
        $taskTitle = 'Order -> Action -> Process Results';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Order_Action_ProcessResults::NICK
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
     * @title "Repricing -> InspectProducts"
     */
    public function repricingInspectProductsAction()
    {
        $taskTitle = 'Repricing -> InspectProducts';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_InspectProducts::NICK
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
     * @title "Repricing -> UpdateSettings"
     */
    public function repricingUpdateSettingsAction()
    {
        $taskTitle = 'Repricing -> UpdateSettings';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_UpdateSettings::NICK
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
     * @title "Repricing -> Synchronize"
     */
    public function repricingSynchronizeAction()
    {
        $taskTitle = 'Repricing -> Synchronize';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Amazon_Repricing_Synchronize::NICK
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