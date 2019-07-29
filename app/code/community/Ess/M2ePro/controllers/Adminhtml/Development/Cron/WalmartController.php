<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Development_Cron_WalmartController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //########################################

    /**
     * @title "Listing -> Other -> Channel -> SynchronizeData"
     * @new_line
     */
    public function listingOtherChannelSynchronizeDataAction()
    {
        $taskTitle = 'Listing -> Other -> Channel -> SynchronizeData';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Other_Channel_SynchronizeData::NICK
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
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_Channel_SynchronizeData::NICK
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
     * @new_line
     */
    public function listingProductChannelSynchronizeDataBlockedAction()
    {
        $taskTitle = 'Listing -> Product -> Channel -> SynchronizeData -> Blocked';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_Channel_SynchronizeData_Blocked::NICK
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
     * @title "Listing -> Product -> ProcessActions"
     */
    public function listingProductProcessActionsAction()
    {
        $taskTitle = 'Listing -> Product -> ProcessActions';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessActions::NICK
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
     */
    public function listingProductProcessActionsResultsAction()
    {
        $taskTitle = 'Listing -> Product -> ProcessActionsResults';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessActionsResults::NICK
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
    public function listingProductProcessInstructionsAction()
    {
        $taskTitle = 'Listing -> Product -> ProcessInstructions';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessInstructions::NICK
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
     * @title "Listing -> Product -> ProcessListActions"
     */
    public function listingProductProcessListActionsAction()
    {
        $taskTitle = 'Listing -> Product -> ProcessListActions';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessListActions::NICK
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
     * @title "Listing -> Product -> RunVariationParentProcessors"
     * @new_line
     */
    public function listingProductRunVariationParentProcessorsAction()
    {
        $taskTitle = 'Listing -> Product -> RunVariationParentProcessors';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_RunVariationParentProcessors::NICK
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
     * @title "Order -> Acknowledge"
     */
    public function orderAcknowledgeAction()
    {
        $taskTitle = 'Order -> Acknowledge';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Walmart_Order_Acknowledge::NICK
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
            Ess_M2ePro_Model_Cron_Task_Walmart_Order_Cancel::NICK
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
     * @title "Order -> Receive"
     */
    public function orderReceiveAction()
    {
        $taskTitle = 'Order -> Receive';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Walmart_Order_Receive::NICK
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
            Ess_M2ePro_Model_Cron_Task_Walmart_Order_Refund::NICK
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
     * @title "Order -> Shipping"
     */
    public function orderShippingAction()
    {
        $taskTitle = 'Order -> Shipping';

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $cronRunner->setAllowedTasks(array(
            Ess_M2ePro_Model_Cron_Task_Walmart_Order_Shipping::NICK
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