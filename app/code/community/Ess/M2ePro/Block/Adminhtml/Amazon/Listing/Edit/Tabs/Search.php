<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Edit_Tabs_Search
    extends Mage_Adminhtml_Block_Widget
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingEditTabsSearch');
        $this->setTemplate('M2ePro/amazon/listing/edit/tabs/search.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->setData(
            'all_attributes',
            Mage::helper('M2ePro/Magento_Attribute')->getAll()
        );

        foreach ($this->getListing()->getData() as $key => $value) {
            $this->setData($key, $value);
        }

        return parent::_beforeToHtml();
    }

    //########################################

    protected function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing', $listingId);
        }

        return $this->_listing;
    }

    //########################################
}
