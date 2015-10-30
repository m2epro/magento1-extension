<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_SearchAsin_SearchSettings
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected $component;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('searchSettings');
        // ---------------------------------------

        $this->setTemplate('M2ePro/common/amazon/listing/add/search_asin/search_settings.phtml');
    }

    protected function _prepareForm()
    {
        // Prepare action
        // ---------------------------------------
        $action = $this->getUrl('*/adminhtml_common_amazon_listing_productAdd/saveSearchSettings', array(
            'id' => (int)$this->getRequest()->getParam('id')
        ));
        // ---------------------------------------

        $form = new Varien_Data_Form(array(
            'id'      => 'search_settings_form',
            'action'  => $action,
            'method'  => 'post'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $child = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_tabs_search');

        $this->setChild('content', $child);

        return parent::_beforeToHtml();
    }

    //########################################

    protected function _toHtml()
    {
        $buttonSave = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'id'    => 'save_search_settings',
            'label' => Mage::helper('M2ePro')->__('Confirm'),
            'style' => 'float: right; margin: 0 0 7px 5px;',
            'onclick' => 'ListingGridHandlerObj.saveSearchSettings()'
        ));

        $this->setChild('save_search_settings', $buttonSave);

        return parent::_toHtml();
    }

    //########################################
}