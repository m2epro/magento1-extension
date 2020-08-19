<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_ExtensionCron
    extends Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    //########################################

    public function getTitle()
    {
        return 'Extension Cron';
    }

    public function getDescription()
    {
        return <<<HTML
- Cron [runner] does not work<br>
- Cron [runner] is not working more than 30 min<br>
- Cron [runner] is disabled by developer
HTML;
    }

    public function getGroup()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::GROUP_GENERAL;
    }

    public function getExecutionSpeed()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::EXECUTION_SPEED_FAST;
    }

    //########################################

    public function process()
    {
        $issues = array();
        $helper = Mage::helper('M2ePro/Module_Cron');
        $moduleConfig = Mage::helper('M2ePro/Module')->getConfig();

        if ($helper->getLastRun() === null) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                "Cron [{$helper->getRunner()}] does not work"
            );
        } elseif (Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(1800)) {
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $cron = new \DateTime($helper->getLastRun(), new \DateTimeZone('UTC'));
            $diff = round(($now->getTimestamp() - $cron->getTimestamp()) / 60, 0);

            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                "Cron [{$helper->getRunner()}] is not working for {$diff} min",
                <<<HTML
Last run: {$helper->getLastRun()}
Now:      {$now->format('Y-m-d H:i:s')}
HTML
            );
        }

        foreach (array('magento', 'service') as $runner) {
            if ($moduleConfig->getGroupValue("/cron/{$runner}/", 'disabled')) {
                $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createNotice(
                    $this,
                    "Cron [{$runner}] is disabled by developer"
                );
            }
        }

        return $issues;
    }

    //########################################
}