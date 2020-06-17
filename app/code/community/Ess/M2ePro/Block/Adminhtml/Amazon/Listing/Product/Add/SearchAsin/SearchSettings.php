<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_SearchAsin_SearchSettings
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_component;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('searchSettings');
        $this->setTemplate('M2ePro/amazon/listing/product/add/search_asin/search_settings.phtml');
    }

    protected function _prepareForm()
    {
        $action = $this->getUrl(
            '*/adminhtml_amazon_listing_productAdd/saveSearchSettings', array(
            'id' => (int)$this->getRequest()->getParam('id')
            )
        );

        $form = new Varien_Data_Form(
            array(
                'id'     => 'search_settings_form',
                'action' => $action,
                'method' => 'post'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $child = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_edit_tabs_search');

        $this->setChild('content', $child);

        return parent::_beforeToHtml();
    }

    //########################################

    protected function _toHtml()
    {
        $buttonSave = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
                'id'      => 'save_search_settings',
                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                'style'   => 'float: right; margin: 0 0 7px 5px;',
                'onclick' => 'ListingGridObj.saveSearchSettings()'
            )
        );

        $this->setChild('save_search_settings', $buttonSave);

        return parent::_toHtml();
    }

    //########################################
}
