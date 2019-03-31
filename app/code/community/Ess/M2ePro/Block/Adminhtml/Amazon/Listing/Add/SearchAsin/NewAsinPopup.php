<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Add_SearchAsin_NewAsinPopup extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('searchAsinNewAsinPopup');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/listing/add/search_asin/new_asin_popup.phtml');
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

    //########################################
}