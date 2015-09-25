<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Settings_Category_Specific_Wrapper
    extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingViewSettingsCategorySpecificWrapper');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/view/settings/category/specific/wrapper.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        //------------------------------
        $data = array(
            'id'      => 'done_button',
            'class'   => 'save done',
            'label'   => Mage::helper('M2ePro')->__('Save'),
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('done', $buttonBlock);
        //------------------------------
    }

    protected function _toHtml()
    {
        $breadcrumb = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_breadcrumb');
        $breadcrumb->setStep(2);

        return $breadcrumb->toHtml() . parent::_toHtml();
    }

    // ####################################
}