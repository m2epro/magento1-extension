<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Add_Tabs_Search
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_Search
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->sessionKey = 'buy_listing_create';
        $this->setId('buyListingAddTabsGeneral');
        $this->setTemplate('M2ePro/common/buy/listing/add/tabs/search.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function getDefaults()
    {
        return self::getDefaultFieldsValues();
    }

    static function getDefaultFieldsValues()
    {
        return array(
            'general_id_mode' => Ess_M2ePro_Model_Buy_Listing::GENERAL_ID_MODE_NOT_SET,
            'general_id_custom_attribute' => '',

            'search_by_magento_title_mode' => Ess_M2ePro_Model_Buy_Listing::SEARCH_BY_MAGENTO_TITLE_MODE_NONE
        );
    }

    //########################################

    public function isEditMode()
    {
        return !!$this->getRequest()->getParam('id');
    }

    //########################################
}