<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Synchronization_Log_Grid
    extends Ess_M2ePro_Block_Adminhtml_Synchronization_Log_Grid
{
    //########################################

    protected function getActionTitles()
    {
        $allTitles = Mage::getModel('M2ePro/Synchronization_Log')->getActionsTitles();

        $allTitles[Ess_M2ePro_Model_Synchronization_Log::TASK_MARKETPLACES] =
            Mage::helper('M2ePro')->__('eBay Site Synchronization');

        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return $allTitles;
        }

        $excludeTitles = array(
            Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS => '',
            Ess_M2ePro_Model_Synchronization_Log::TASK_FEEDBACKS => ''
        );

        return array_diff_key($allTitles, $excludeTitles);
    }

    //########################################
}