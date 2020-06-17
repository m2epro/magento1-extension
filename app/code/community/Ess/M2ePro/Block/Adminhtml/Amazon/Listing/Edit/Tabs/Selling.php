<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Edit_Tabs_Selling
    extends Mage_Adminhtml_Block_Widget
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    /** @var Ess_M2ePro_Model_Template_SellingFormat[] */
    protected $_sellingFormatTemplates;

    /** @var Ess_M2ePro_Model_Template_Synchronization[] */
    protected $_synchronizationsTemplates;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingEditTabsSelling');
        $this->setTemplate('M2ePro/amazon/listing/edit/tabs/selling.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'label' => Mage::helper('M2ePro')->__('Insert'),
                    'onclick' => "AmazonListingChannelSettingsObj.appendToText"
                        ."('condition_note_custom_attribute', 'condition_note_value');",
                    'class' => 'condition_note_value_insert_button'
                )
            );
        $this->setChild('condition_note_value_insert_button', $buttonBlock);

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

    public function getSellingFormatTemplates()
    {
        if ($this->_sellingFormatTemplates !== null) {
            return $this->_sellingFormatTemplates;
        }

        /** @var $collection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Template_SellingFormat')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);
        $collection->resetByType(Zend_Db_Select::COLUMNS, array('id', 'title'));

        $data = $collection->toArray();
        foreach ($data['items'] as $key => $value) {
            $data['items'][$key]['title'] = Mage::helper('M2ePro')->escapeHtml($data['items'][$key]['title']);
        }

        return $this->_sellingFormatTemplates = $data['items'];
    }

    public function getSynchronizationsTemplates()
    {
        if ($this->_synchronizationsTemplates !== null) {
            return $this->_synchronizationsTemplates;
        }

        /** @var $collection Ess_M2ePro_Model_Resource_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Template_Synchronization')->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);
        $collection->resetByType(Zend_Db_Select::COLUMNS, array('id', 'title'));

        $data = $collection->toArray();
        foreach ($data['items'] as $key => $value) {
            $data['items'][$key]['title'] = Mage::helper('M2ePro')->escapeHtml($data['items'][$key]['title']);
        }

        return $this->_synchronizationsTemplates = $data['items'];
    }

    //########################################
}
