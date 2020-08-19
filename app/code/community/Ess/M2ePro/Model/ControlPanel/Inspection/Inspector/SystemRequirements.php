<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_SystemRequirements
    extends Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    //########################################

    public function getTitle()
    {
        return 'System Requirements';
    }

    public function getDescription()
    {
        $html = '';
        foreach (Mage::getSingleton('M2ePro/Requirements_Manager')->getChecks() as $check) {
            $html .= "- {$check->getRenderer()->getTitle()}: {$check->getRenderer()->getMin()}<br>";
        }

        return $html;
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

        foreach (Mage::getSingleton('M2ePro/Requirements_Manager')->getChecks() as $check) {
            /**@var Ess_M2ePro_Model_Requirements_Checks_Abstract $check */
            if ($check->isMeet()) {
                continue;
            }

            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                $check->getRenderer()->getTitle(),
                <<<HTML
Minimum: {$check->getMin()}
Configuration: {$check->getReal()}
HTML
            );
        }

        return $issues;
    }

    //########################################
}