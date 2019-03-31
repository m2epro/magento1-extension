<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid
{
    //########################################

    protected function getActionTitles()
    {
        $allActions = Mage::helper('M2ePro/Module_Log')->getActionsTitlesByClass('Listing_Log');
        $excludeActions = array(
            Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_TIER_PRICE => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_RESET_BLOCKED_PRODUCT => '',
        );

        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $excludeActions[Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT] = '';
        }

        return array_diff_key($allActions, $excludeActions);
    }

    //########################################
}