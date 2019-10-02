<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Add_Tabs_Search
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingAddTabsGeneral');

        $this->sessionKey = 'amazon_listing_create';
        $this->setId('amazonListingAddTabsGeneral');
        $this->setTemplate('M2ePro/amazon/listing/add/tabs/search.phtml');
        // ---------------------------------------
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = $this->getListingData();

        $this->setData(
            'general_attributes',
            Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets()
        );

        foreach ($data as $key=>$value) {
            $this->setData($key, $value);
        }

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected  function getListingData()
    {
        if ($this->getRequest()->getParam('id') !== null) {
            $data = $this->getListing()->getData();
        } else {
            $data = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);
            $data = array_merge($this->getDefaults(), $data);
        }

        return $data;
    }

    protected function getDefaults()
    {
        return self::getDefaultFieldsValues();
    }

    public static function getDefaultFieldsValues()
    {
        return array(
            'general_id_mode' => Ess_M2ePro_Model_Amazon_Listing::GENERAL_ID_MODE_NOT_SET,
            'general_id_custom_attribute' => '',

            'worldwide_id_mode' => Ess_M2ePro_Model_Amazon_Listing::WORLDWIDE_ID_MODE_NOT_SET,
            'worldwide_id_custom_attribute' => '',

            'search_by_magento_title_mode' => Ess_M2ePro_Model_Amazon_Listing::SEARCH_BY_MAGENTO_TITLE_MODE_NONE
        );
    }

    //########################################

    protected function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->listing === null) {
            $this->listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $listingId);
        }

        return $this->listing;
    }

    //########################################

    public function isEditMode()
    {
        return !!$this->getRequest()->getParam('id');
    }

    //########################################
}
