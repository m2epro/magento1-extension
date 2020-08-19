<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Log_Synchronization_Grid
    extends Ess_M2ePro_Block_Adminhtml_Log_Synchronization_AbstractGrid
{
    //########################################

    public function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Walmart::NICK;
    }

    //########################################

    protected function getExcludedActionTitles()
    {
        return array(
            Ess_M2ePro_Model_Synchronization_Log::TASK_REPRICING => '',
        );
    }

    //########################################
}
