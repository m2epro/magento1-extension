<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid
{
    //########################################

    protected function getActionTitles()
    {
        $allActions = Mage::getModel('M2ePro/Listing_Log')->getActionsTitles();

        $excludeActions = array(
            Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT => ''
        );

        if ($this->getRequest()->getParam('channel') == Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_AMAZON ||
            !Mage::helper('M2ePro/Component_Buy')->isActive()) {

            $excludeActions[Ess_M2ePro_Model_Listing_Log::ACTION_NEW_SKU_PRODUCT_ON_COMPONENT] = '';
        }

        if ($this->getRequest()->getParam('channel') == Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_BUY ||
            !Mage::helper('M2ePro/Component_Amazon')->isActive()) {

            $excludeActions[Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT] = '';
            $excludeActions[Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT] = '';

            $excludeActions[Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT] = '';
            $excludeActions[Ess_M2ePro_Model_Listing_Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT] = '';
        }

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
                '*/adminhtml_common_'.$row->getData('component_mode').'_listing/view',
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