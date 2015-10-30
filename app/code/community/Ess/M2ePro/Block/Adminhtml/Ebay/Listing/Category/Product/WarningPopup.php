<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Product_WarningPopup extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingCategoryWarningPopup');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/category/product/warning_popup.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $data = array(
            'class'   => 'next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => "setLocation(M2ePro.url.get('adminhtml_ebay_listing_categorySettings'));"
        );
        $this->setChild('continue_button',$this->getLayout()->createBlock('adminhtml/widget_button')->setData($data));

        return $this;
    }

    //########################################
}