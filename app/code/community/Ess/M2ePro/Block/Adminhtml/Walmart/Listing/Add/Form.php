<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Add_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartListingAddForm');
        $this->setTemplate('M2ePro/walmart/listing/add/form.phtml');
    }

    //########################################

    protected function _prepareForm()
    {
        // ---------------------------------------

        $form = new Varien_Data_Form(
            array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/adminhtml_walmart_listing/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => 'Add',
                'onclick' => '',
                'id' => 'add_account_button',
                )
            );

        $this->setChild('add_account_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $this->setChild(
            'store_switcher',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_storeSwitcher', '', array(
                    'id'=>'store_id',
                    'selected' => $this->getData('store_id'),
                    'display_default_store_mode' => 'down',
                    'required_option' => true,
                    'empty_option' => true
                )
            )
        );
        // ---------------------------------------

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label' => Mage::helper('M2ePro')->__('Insert'),
                'onclick' => "WalmartListingChannelSettingsHandlerObj.appendToText"
                    ."('condition_note_custom_attribute', 'condition_note_value');",
                'class' => 'condition_note_value_insert_button'
                )
            );
        $this->setChild('condition_note_value_insert_button', $buttonBlock);

        // ---------------------------------------

        $this->setData(
            'general_attributes',
            Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets()
        );

        $this->setData(
            'all_attributes',
            Mage::helper('M2ePro/Magento_Attribute')->getAll()
        );
        // ---------------------------------------

        $this->sellingFormatTemplates = $this->getTemplates('SellingFormat');
        $this->descriptionsTemplates = $this->getTemplates('Description');
        $this->synchronizationsTemplates = $this->getTemplates('Synchronization');

        return parent::_beforeToHtml();
    }

    //########################################

    protected function getTemplates($policy)
    {
        $collection = Mage::getModel("M2ePro/Template_$policy")->getCollection();
        $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
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

    protected function getDefaults()
    {
        return self::getDefaultFieldsValues();
    }

    public static function getDefaultFieldsValues()
    {
        return array(
            'template_selling_format_id' => '',
            'template_description_id' => '',
            'template_synchronization_id' => '',
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
