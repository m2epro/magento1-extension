<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser_Wrapper extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategoryChooserWrapper');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/category/chooser/wrapper.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        //------------------------------
        $data = array(
            'id'      => 'done_button',
            'class'   => 'save done',
            'label'   => Mage::helper('M2ePro')->__('Done'),
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('done', $buttonBlock);
        //------------------------------
    }

    // ####################################
}