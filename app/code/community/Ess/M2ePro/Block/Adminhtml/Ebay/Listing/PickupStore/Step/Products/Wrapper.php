<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_PickupStore_Step_Products_Wrapper
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingPickupStoreProductsWrapper');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/pickupStore/step/products/wrapper.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $data = array(
            'id'      => 'done_button',
            'class'   => 'done next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('done', $buttonBlock);
        // ---------------------------------------
    }

    protected function _toHtml()
    {
        $breadcrumb = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_breadcrumb');
        $breadcrumb->setStep(1);

        $help = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_step_help');
        $breadcrumb->setChild('help', $help);

        return $breadcrumb->toHtml() . parent::_toHtml();
    }

    //########################################
}
