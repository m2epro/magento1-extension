<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_Tabs_Selling
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_Selling
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->component = Ess_M2ePro_Helper_Component_Amazon::NICK;
        $this->sessionKey = 'amazon_listing_create';
        $this->setId('amazonListingAddTabsSelling');
        $this->setTemplate('M2ePro/common/amazon/listing/add/tabs/selling.phtml');
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

        return parent::_beforeToHtml();
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

    public function isEditMode()
    {
        return !!$this->getRequest()->getParam('id');
    }

    //########################################
}