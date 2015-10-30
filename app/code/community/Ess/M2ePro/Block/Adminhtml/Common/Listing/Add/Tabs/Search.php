<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_Search extends Mage_Adminhtml_Block_Widget
{
    protected $sessionKey = 'listing_create';

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingAddTabsGeneral');
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
        if (!is_null($this->getRequest()->getParam('id'))) {
            $data = $this->getListing()->getData();
        } else {
            $data = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);
            $data = array_merge($this->getDefaults(), $data);
        }

        return $data;
    }

    protected function getDefaults()
    {
        return array();
    }

    protected function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $listingId);
        }

        return $this->listing;
    }

    //########################################
}