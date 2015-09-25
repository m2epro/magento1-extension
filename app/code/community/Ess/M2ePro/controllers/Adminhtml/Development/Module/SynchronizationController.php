<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Module_SynchronizationController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    /**
     * @title "Cron Tasks"
     * @description "Run all cron synchronization tasks as developer mode"
     * @confirm "Are you sure?"
     * @components
     * @new_line
     */
    public function synchCronTasksAction()
    {
        $this->processSynchTasks(array(
            Ess_M2ePro_Model_Synchronization_Task::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Task::TEMPLATES,
            Ess_M2ePro_Model_Synchronization_Task::ORDERS,
            Ess_M2ePro_Model_Synchronization_Task::FEEDBACKS,
            Ess_M2ePro_Model_Synchronization_Task::OTHER_LISTINGS,
            Ess_M2ePro_Model_Synchronization_Task::POLICIES
        ));
    }

    //----------------------------------------------

    /**
     * @title "Defaults"
     * @description "Run only defaults synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components
     */
    public function synchDefaultsAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Task::DEFAULTS
        ));
    }

    //#############################################

    /**
     * @title "Templates"
     * @description "Run only stock level synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components
     */
    public function synchTemplatesAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Task::TEMPLATES
        ));
    }

    /**
     * @title "Orders"
     * @description "Run only orders synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components
     */
    public function synchOrdersAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Task::ORDERS
        ));
    }

    /**
     * @title "Feedbacks"
     * @description "Run only feedbacks synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components ebay
     */
    public function synchFeedbacksAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Task::FEEDBACKS
        ));
    }

    /**
     * @title "Marketplaces"
     * @description "Run only marketplaces synchronization as developer mode"
     * @prompt "Please enter Marketplace ID."
     * @prompt_var "marketplace_id"
     * @components
     */
    public function synchMarketplacesAction()
    {
        $params = array();

        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id');
        if(!empty($marketplaceId)) {
            $params['marketplace_id'] = $marketplaceId;
        }

        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Task::MARKETPLACES
        ), $params);
    }

    /**
     * @title "3rd Party Listings"
     * @description "Run only 3rd party listings synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components
     */
    public function synchOtherListingsAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Task::OTHER_LISTINGS
        ));
    }

    //#############################################

    private function processSynchTasks($tasks, $params = array())
    {
        session_write_close();

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $components = Mage::helper('M2ePro/Component')->getComponents();
        if ($this->getRequest()->getParam('component')) {
            $components = array($this->getRequest()->getParam('component'));
        }

        $dispatcher->setAllowedComponents($components);
        $dispatcher->setAllowedTasksTypes($tasks);

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER);
        $dispatcher->setParams($params);

        $dispatcher->process();

        echo '<pre>'.$dispatcher->getOperationHistory()->getFullProfilerInfo().'</pre>';
    }

    //#############################################
}