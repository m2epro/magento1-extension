<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Development_Module_ModuleController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //########################################

    /**
     * @title "Run Cron"
     * @description "Emulate starting cron"
     */
    public function runCronAction()
    {
        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');

        if ($cronRunner->process()) {
            $this->_getSession()->addSuccess('Cron was successfully performed.');
        } else {
            $this->_getSession()->addError('Cron was performed with errors.');
        }

        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    //########################################

    /**
     * @title "Process Servicing"
     * @description "Process Servicing Task"
     */
    public function processServicingAction()
    {
        $dispatcher = Mage::getModel('M2ePro/Servicing_Dispatcher');
        $dispatcher->setForceTasksRunning(true);

        $dispatcher->process()
            ? $this->_getSession()->addSuccess('Servicing was successfully executed.')
            : $this->_getSession()->addError('Servicing was executed with errors.');

        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageModuleTabUrl());
    }

    //########################################
}