<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Module_ModuleController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    /**
     * @title "Run Cron"
     * @description "Emulate starting cron"
     */
    public function runCronAction()
    {
        $cron = Mage::getModel('M2ePro/Cron_Type_Developer');
        $result = $cron->process();

        if ($result) {
            $this->_getSession()->addSuccess('Cron was successfully performed.');
        } else {
            $this->_getSession()->addError('Cron was performed with errors.');
        }

        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    /**
     * @title "Run Processing Cron"
     * @description "Run Processing Cron"
     * @new_line
     */
    public function cronProcessingTemporaryAction()
    {
        $dispatcher = Mage::getModel('M2ePro/Processing_Dispatcher');
        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER);
        $result = $dispatcher->process();

        if ($result) {
            $this->_getSession()->addSuccess('Processing Cron was successfully performed.');
        } else {
            $this->_getSession()->addError('Processing Cron was performed with errors.');
        }

        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    //#############################################

    /**
     * @title "Process Servicing"
     * @description "Process Servicing Task"
     */
    public function processServicingAction()
    {
        $dispatcher = Mage::getModel('M2ePro/Servicing_Dispatcher');
        $dispatcher->setForceTasksRunning(true);

        $dispatcher->process()
            ? $this->_getSession()->addSuccess('Processing was successfully executed.')
            : $this->_getSession()->addError('Processing was executed with errors.');

        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    //#############################################
}