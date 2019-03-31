<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Add_Tabs_Selling
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingAddTabsSelling');

        $this->component = Ess_M2ePro_Helper_Component_Amazon::NICK;
        $this->sessionKey = 'amazon_listing_create';
        $this->setId('amazonListingAddTabsSelling');
        $this->setTemplate('M2ePro/amazon/listing/add/tabs/selling.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _beforeToHtml()
    {
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                    'label' => Mage::helper('M2ePro')->__('Insert'),
                    'onclick' => "AmazonListingChannelSettingsHandlerObj.appendToText"
                        ."('condition_note_custom_attribute', 'condition_note_value');",
                    'class' => 'condition_note_value_insert_button'
                ));
        $this->setChild('condition_note_value_insert_button', $buttonBlock);

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
        $maxRecordsQuantity = Mage::helper('M2ePro/View_Amazon')->getAutocompleteMaxItems();
        // ---------------------------------------

        // ---------------------------------------
        $this->sellingFormatTemplates = $this->getTemplates('SellingFormat');
        $this->sellingFormatTemplatesDropDown = (count($this->sellingFormatTemplates) < $maxRecordsQuantity);
        // ---------------------------------------

        // ---------------------------------------
        $this->synchronizationsTemplates = $this->getTemplates('Synchronization');
        $this->synchronizationsTemplatesDropDown = (count($this->synchronizationsTemplates) < $maxRecordsQuantity);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    protected function getTemplates($policy)
    {
        $collection = Mage::getModel("M2ePro/Template_$policy")->getCollection();
        $collection->addFieldToFilter('component_mode', $this->component);
        $collection->setOrder('title', Varien_Data_Collection::SORT_ORDER_ASC);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('id', 'title'));

        $collection->setOrder('main_table.title', Varien_Data_Collection::SORT_ORDER_ASC);

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
        return self::getDefaultFieldsValues();
    }

    static function getDefaultFieldsValues()
    {
        return array(
            'sku_mode' => Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_DEFAULT,
            'sku_custom_attribute' => '',
            'sku_modification_mode' => Ess_M2ePro_Model_Amazon_Listing::SKU_MODIFICATION_MODE_NONE,
            'sku_modification_custom_value' => '',
            'generate_sku_mode' => Ess_M2ePro_Model_Amazon_Listing::GENERATE_SKU_MODE_NO,

            'template_selling_format_id' => '',
            'template_synchronization_id' => '',

            'condition_mode' => Ess_M2ePro_Model_Amazon_Listing::CONDITION_MODE_DEFAULT,
            'condition_value' => Ess_M2ePro_Model_Amazon_Listing::CONDITION_NEW,
            'condition_custom_attribute' => '',
            'condition_note_mode' => Ess_M2ePro_Model_Amazon_Listing::CONDITION_NOTE_MODE_NONE,
            'condition_note_value' => '',

            'image_main_mode' => Ess_M2ePro_Model_Amazon_Listing::IMAGE_MAIN_MODE_NONE,
            'image_main_attribute' => '',
            'gallery_images_mode' => Ess_M2ePro_Model_Amazon_Listing::GALLERY_IMAGES_MODE_NONE,
            'gallery_images_limit' => '',
            'gallery_images_attribute' => '',

            'gift_wrap_mode' => Ess_M2ePro_Model_Amazon_Listing::GIFT_WRAP_MODE_NO,
            'gift_wrap_attribute' => '',

            'gift_message_mode' => Ess_M2ePro_Model_Amazon_Listing::GIFT_MESSAGE_MODE_NO,
            'gift_message_attribute' => '',

            'handling_time_mode' => Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_NONE,
            'handling_time_value' => '',
            'handling_time_custom_attribute' => '',

            'restock_date_mode' => Ess_M2ePro_Model_Amazon_Listing::RESTOCK_DATE_MODE_NONE,
            'restock_date_value' => Mage::helper('M2ePro')->getCurrentTimezoneDate(),
            'restock_date_custom_attribute' => ''
        );
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

    public function getSellingFormatTemplateTitleById($id)
    {
        foreach ($this->sellingFormatTemplates as $template) {
            if ($template['id'] == $id) {
                return $template['title'];
            }
        }

        return '';
    }

    public function getSynchronizationTemplateTitleById($id)
    {
        foreach ($this->synchronizationsTemplates as $template) {
            if ($template['id'] == $id) {
                return $template['title'];
            }
        }

        return '';
    }

    //########################################

    public function isEditMode()
    {
        return !!$this->getRequest()->getParam('id');
    }

    //########################################
}