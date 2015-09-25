<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Synchronization_Log_Grid
    extends Ess_M2ePro_Block_Adminhtml_Synchronization_Log_Grid
{
    // ########################################

    protected function getActionTitles()
    {
        $allActions = Mage::getModel('M2ePro/Synchronization_Log')->getActionsTitles();
        $excludeActions = array(
            Ess_M2ePro_Model_Synchronization_Log::TASK_FEEDBACKS => '',
        );

        return array_diff_key($allActions, $excludeActions);
    }

    // ########################################
}