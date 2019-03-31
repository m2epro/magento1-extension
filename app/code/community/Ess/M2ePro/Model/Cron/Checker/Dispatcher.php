<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Checker_Dispatcher
{
    //########################################

    public function process()
    {
        foreach ($this->getAllowedTasks() as $taskNick) {
            $this->getTaskObject($taskNick)->process();
        }
    }

    //########################################

    protected function getAllowedTasks()
    {
        return array(
            Ess_M2ePro_Model_Cron_Checker_Task_RepairCrashedTables::NICK
        );
    }

    //########################################

    /**
     * @param $taskNick
     * @return Ess_M2ePro_Model_Cron_Checker_Task_Abstract
     */
    protected function getTaskObject($taskNick)
    {
        $taskNick = str_replace('_', ' ', $taskNick);
        $taskNick = str_replace(' ', '', ucwords($taskNick));

        return Mage::getModel('M2ePro/Cron_Checker_Task_'.trim($taskNick));
    }

    //########################################
}