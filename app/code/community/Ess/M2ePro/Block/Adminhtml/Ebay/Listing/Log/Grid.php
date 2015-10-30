<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid
{
    //########################################

    protected function getActionTitles()
    {
        $allActions = Mage::getModel('M2ePro/Listing_Log')->getActionsTitles();
        $excludeActions = array(
            Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_NEW_SKU_PRODUCT_ON_COMPONENT => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT => ''
        );

        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $excludeActions[Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT] = '';
        }

        return array_diff_key($allActions, $excludeActions);
    }

    //########################################
}