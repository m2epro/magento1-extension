<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Synchronization_Log_Grid
    extends Ess_M2ePro_Block_Adminhtml_Synchronization_Log_Grid
{
    //########################################

    protected function getActionTitles()
    {
        return Mage::helper('M2ePro/Module_Log')->getActionsTitlesByClass('Synchronization_Log');
    }

    //########################################
}
