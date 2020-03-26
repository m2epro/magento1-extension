<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_CronController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_CommandController
{
    //########################################

    public function runAction()
    {
        $taskCode = $this->getRequest()->getParam('task_code');

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Developer');
        $taskCode && $cronRunner->setAllowedTasks(array($taskCode));

        $cronRunner->process();

        return $this->_addRawContent('<pre>' . $cronRunner->getOperationHistory()->getFullDataInfo() . '</pre>');
    }

    //########################################
}
