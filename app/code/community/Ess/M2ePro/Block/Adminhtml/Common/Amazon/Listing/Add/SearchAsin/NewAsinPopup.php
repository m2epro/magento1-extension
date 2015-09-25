<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Add_SearchAsin_NewAsinPopup extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('searchAsinNewAsinPopup');
        //------------------------------

        $this->setTemplate('M2ePro/common/amazon/listing/add/search_asin/new_asin_popup.phtml');
    }

    protected function _beforeToHtml()
    {
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Yes'),
            'onclick' => 'ListingGridHandlerObj.newAsinPopupYesClick()'
        );
        $yesButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('yesBtn', $yesButton);

        $data = array(
            'label'   => Mage::helper('M2ePro')->__('No'),
            'onclick' => 'ListingGridHandlerObj.noAsinPopupNoClick()'
        );
        $noButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('noBtn', $noButton);
    }

    // ####################################
}