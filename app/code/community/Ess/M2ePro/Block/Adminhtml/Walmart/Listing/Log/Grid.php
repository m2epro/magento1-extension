<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid
{
    //########################################

    protected function getActionTitles()
    {
        $allActions = Mage::helper('M2ePro/Module_Log')->getActionsTitlesByClass('Listing_Log');

        $excludeActions = array(
            Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT => '',

            Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT => '',
            Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT => '',

            Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_TIER_PRICE => '',
        );

        $allActions[Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT] = Mage::helper('M2ePro')->__(
            'Retire on Channel / Remove from Listing'
        );

        return array_diff_key($allActions, $excludeActions);
    }

    //########################################

    public function callbackColumnListingTitleID($value, $row, $column, $isExport)
    {
        if (strlen($value) > 50) {
            $value = substr($value, 0, 50) . '...';
        }

        $value = Mage::helper('M2ePro')->escapeHtml($value);

        if ($row->getData('listing_id')) {
            $url = $this->getUrl(
                '*/adminhtml_walmart_listing/view',
                array('id' => $row->getData('listing_id'))
            );

            $value = '<a target="_blank" href="'.$url.'">' .
                        $value .
                     '</a><br/>ID: '.$row->getData('listing_id');
        }

        return $value;
    }

    //########################################
}
