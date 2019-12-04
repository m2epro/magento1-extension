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
        $tasks = array();

        foreach (Mage::getModel('M2ePro/Cron_Strategy_Serial')->getAllowedTasks() as $taskCode) {
            $optGroup = 'system';
            $titleParts = explode('/', $taskCode);
            if (in_array(reset($titleParts), Mage::helper('M2ePro/Component')->getComponents())) {
                $optGroup = array_shift($titleParts);
            }

            if ($index = array_search('cron', $titleParts)) {
                unset($titleParts[$index]);
            }

            $taskTitle = preg_replace_callback(
                '/_([a-z])/i',
                function ($matches) {
                    return ucfirst($matches[1]);
                },
                implode(' > ', array_map('ucfirst', $titleParts))
            );

            $tasks[ucfirst($optGroup)][$taskCode] = $taskTitle;
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
