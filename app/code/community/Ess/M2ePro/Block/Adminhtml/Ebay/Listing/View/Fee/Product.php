<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Fee_Product extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingViewFeePreview');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/view/fee/product.phtml');
    }

    public function getFees()
    {
        if (empty($this->_data['fees']) || !is_array($this->_data['fees'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Fees are not set.');
        }

        return $this->_data['fees'];
    }

    public function getTotalFee()
    {
        $fees = $this->getFees();

        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $fees['listing_fee']['currency'],
            $fees['listing_fee']['fee']
        );
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $details = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_details');
        $details->setData('fees', $this->getFees());
        $details->setData('product_name', $this->getData('product_name'));

        $this->setChild('details', $details);
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ####################################
}