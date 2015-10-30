<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Add_Tabs_Selling
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_Selling
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->component = Ess_M2ePro_Helper_Component_Buy::NICK;
        $this->sessionKey = 'buy_listing_create';
        $this->setId('buyListingAddTabsSelling');
        $this->setTemplate('M2ePro/common/buy/listing/add/tabs/selling.phtml');
        // ---------------------------------------
    }

    //########################################

    protected function _beforeToHtml()
    {
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                    'label' => Mage::helper('M2ePro')->__('Insert'),
                    'onclick' => "BuyListingChannelSettingsHandlerObj.appendToText"
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
            'sku_mode' => Ess_M2ePro_Model_Buy_Listing::SKU_MODE_DEFAULT,
            'sku_custom_attribute' => '',
            'sku_modification_mode' => Ess_M2ePro_Model_Buy_Listing::SKU_MODIFICATION_MODE_NONE,
            'sku_modification_custom_value' => '',
            'generate_sku_mode' => Ess_M2ePro_Model_Buy_Listing::GENERATE_SKU_MODE_NO,

            'template_selling_format_id' => '',
            'template_synchronization_id' => '',

            'shipping_standard_mode' => Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT,
            'shipping_standard_value' => 0,
            'shipping_standard_custom_attribute' => '',

            'shipping_expedited_mode' => Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT,
            'shipping_expedited_value' => 0,
            'shipping_expedited_custom_attribute' => '',

            'shipping_two_day_mode' => Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT,
            'shipping_two_day_value' => 0,
            'shipping_two_day_custom_attribute' => '',

            'shipping_one_day_mode' => Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT,
            'shipping_one_day_value' => 0,
            'shipping_one_day_custom_attribute' => '',

            'condition_mode' => Ess_M2ePro_Model_Buy_Listing::CONDITION_MODE_DEFAULT,
            'condition_value' => Ess_M2ePro_Model_Buy_Listing::CONDITION_NEW,
            'condition_custom_attribute' => '',
            'condition_note_mode' => Ess_M2ePro_Model_Buy_Listing::CONDITION_NOTE_MODE_NONE,
            'condition_note_value' => ''
        );
    }

    //########################################

    public function isEditMode()
    {
        return !!$this->getRequest()->getParam('id');
    }

    //########################################
}