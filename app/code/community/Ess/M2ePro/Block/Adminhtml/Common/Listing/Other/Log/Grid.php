<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Other_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Other_Log_Grid
{
    //########################################

    protected function getColumnTitles()
    {
        return array(
            'create_date' => Mage::helper('M2ePro')->__('Creation Date'),
            'identifier' => Mage::helper('M2ePro')->__('Identifier'),
            'title' => Mage::helper('M2ePro')->__('Title'),
            'action' => Mage::helper('M2ePro')->__('Action'),
            'description' => Mage::helper('M2ePro')->__('Description'),
            'initiator' => Mage::helper('M2ePro')->__('Run Mode'),
            'type' => Mage::helper('M2ePro')->__('Type')
        );
    }

    //########################################

    protected function getActionTitles()
    {
        $allActions = Mage::getModel('M2ePro/Listing_Other_Log')->getActionsTitles();
        $excludeActions = array(
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_REVISE_PRODUCT => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_RELIST_PRODUCT => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_STOP_PRODUCT => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_CUSTOM_ATTRIBUTE => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_PRICE => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_QTY => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_STATUS => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY => '',
        );

        return array_diff_key($allActions, $excludeActions);
    }

    //########################################
}