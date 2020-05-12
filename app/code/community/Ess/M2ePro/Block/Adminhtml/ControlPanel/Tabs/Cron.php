<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Tabs_Cron extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelCron');
        $this->setTemplate('M2ePro/controlPanel/tabs/cron.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Model_Cron_Task_Repository $taskRepo */
        $taskRepo = Mage::getSingleton('M2ePro/Cron_Task_Repository');

        $tasks = array();
        foreach ($taskRepo->getRegisteredTasks() as $taskNick) {
            $group = $taskRepo->getTaskGroup($taskNick);
            $titleParts = explode('/', $taskNick);
            reset($titleParts) === $group && array_shift($titleParts);

            $taskTitle = preg_replace_callback(
                '/_([a-z])/i',
                function ($matches) {
                    return ucfirst($matches[1]);
                },
                implode(' > ', array_map('ucfirst', $titleParts))
            );

            $tasks[ucfirst($group)][$taskNick] = $taskTitle;
        }

        foreach ($tasks as $group => &$tasksByGroup) {
            asort($tasksByGroup);
        }

        unset($tasksByGroup);
        $this->tasks = $tasks;

        return parent::_beforeToHtml();
    }

    //########################################
}
