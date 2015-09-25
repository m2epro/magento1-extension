<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_ProductSearch_Menu
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/common/buy/listing/product_search/menu.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $data = array(
            'id'    => 'productSearchMenu_cancel_button',
            'label' => Mage::helper('M2ePro')->__('Close'),
            'class' => 'productSearchMenu_cancel_button'
        );
        $buttonCancelBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('productSearchMenu_cancel_button', $buttonCancelBlock);
        //------------------------------

        parent::_beforeToHtml();
    }

    // ####################################
}