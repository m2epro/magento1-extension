<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_SystemRequirements
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    //########################################

    public function process()
    {
        $issues = array();

        foreach (Mage::getSingleton('M2ePro/Requirements_Manager')->getChecks() as $check) {
            /**@var Ess_M2ePro_Model_Requirements_Checks_Abstract $check */
            if ($check->isMeet()) {
                continue;
            }

            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
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