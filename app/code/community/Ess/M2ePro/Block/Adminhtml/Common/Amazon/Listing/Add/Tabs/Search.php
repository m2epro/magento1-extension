<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_Tabs_Search
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_Search
{
    // #############################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->sessionKey = 'amazon_listing_create';
        $this->setId('amazonListingAddTabsGeneral');
        $this->setTemplate('M2ePro/common/amazon/listing/add/tabs/search.phtml');
        //------------------------------
    }

    // #############################################

    protected function getDefaults()
    {
        return self::getDefaultFieldsValues();
    }

    static function getDefaultFieldsValues()
    {
        return array(
            'general_id_mode' => Ess_M2ePro_Model_Amazon_Listing::GENERAL_ID_MODE_NOT_SET,
            'general_id_custom_attribute' => '',

            'worldwide_id_mode' => Ess_M2ePro_Model_Amazon_Listing::WORLDWIDE_ID_MODE_NOT_SET,
            'worldwide_id_custom_attribute' => '',

            'search_by_magento_title_mode' => Ess_M2ePro_Model_Amazon_Listing::SEARCH_BY_MAGENTO_TITLE_MODE_NONE
        );
    }

    // ####################################

    public function isEditMode()
    {
        return !!$this->getRequest()->getParam('id');
    }

    // ####################################
}