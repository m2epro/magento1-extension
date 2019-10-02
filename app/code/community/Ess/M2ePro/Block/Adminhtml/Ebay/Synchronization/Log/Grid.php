<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Synchronization_Log_Grid
    extends Ess_M2ePro_Block_Adminhtml_Synchronization_Log_Grid
{
    //########################################

    protected function getActionTitles()
    {
        $allTitles = Mage::helper('M2ePro/Module_Log')->getActionsTitlesByClass('Synchronization_Log');

        $allTitles[Ess_M2ePro_Model_Synchronization_Log::TASK_MARKETPLACES] =
            Mage::helper('M2ePro')->__('eBay Site Synchronization');

        $excludeTitles = array(
            Ess_M2ePro_Model_Synchronization_Log::TASK_REPRICING => '',
        );

        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $excludeTitles[Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER] = '';
            $excludeTitles[Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS] = '';
        }

        return array_diff_key($allTitles, $excludeTitles);
    }

    //########################################
}
