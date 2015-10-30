<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_Selling extends Mage_Adminhtml_Block_Widget
{
    protected $sessionKey = 'listing_create';
    protected $component = null;
    protected $listing = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingAddTabsSelling');
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

        $this->setData(
            'all_attributes',
            Mage::helper('M2ePro/Magento_Attribute')->getAll()
        );

        foreach ($data as $key=>$value) {
            $this->setData($key, $value);
        }
        // ---------------------------------------

        // ---------------------------------------
        $maxRecordsQuantity = Mage::helper('M2ePro/View_Common')->getAutocompleteMaxItems();
        // ---------------------------------------

        // ---------------------------------------
        $templates = $this->getSellingFormatTemplates();
        if (count($templates) < $maxRecordsQuantity) {
            $this->sellingFormatTemplatesDropDown = true;
            $this->sellingFormatTemplates = $templates;
        } else {
            $this->sellingFormatTemplatesDropDown = false;
            $this->sellingFormatTemplates = array();
        }
        // ---------------------------------------

        // ---------------------------------------
        $synchronizationTemplatesCollection = Mage::helper('M2ePro/Component')
            ->getComponentCollection($this->component, 'Template_Synchronization')
            ->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);

        if ($synchronizationTemplatesCollection->getSize() < $maxRecordsQuantity) {
            $this->synchronizationsTemplatesDropDown = true;
            $templates = $synchronizationTemplatesCollection->toArray();

            foreach ($templates['items'] as $key => $value) {
                $templates['items'][$key]['title'] = Mage::helper('M2ePro')
                    ->escapeHtml($templates['items'][$key]['title']);
            }

            $this->synchronizationsTemplates = $templates['items'];
        } else {
            $this->synchronizationsTemplatesDropDown = false;
            $this->synchronizationsTemplates = array();
        }
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    protected function getSellingFormatTemplates()
    {
        $collection = Mage::getModel('M2ePro/Template_SellingFormat')->getCollection();
        $collection->addFieldToFilter('component_mode', $this->component);
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('id', 'title'));

        $collection->setOrder('main_table.title', 'ASC');

        $data = $collection->toArray();

        foreach ($data['items'] as $key => $value) {
            $data['items'][$key]['title'] = Mage::helper('M2ePro')->escapeHtml($data['items'][$key]['title']);
        }

        return $data['items'];
    }

    //########################################

    protected function getListingData()
    {
        if (!is_null($this->getRequest()->getParam('id'))) {
            $data = $this->getListing()->getData();
        } else {
            $data = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);
            $data = array_merge($this->getDefaults(), $data);
        }

        return $data;
    }

    //########################################

    protected function getDefaults()
    {
        return array();
    }

    //########################################

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