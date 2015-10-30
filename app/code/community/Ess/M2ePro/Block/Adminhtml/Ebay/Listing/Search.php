<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Search extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingSearch');
        // ---------------------------------------
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $this->setChild('help', $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search_help'));
        $this->setChild('grid', $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search_grid'));
    }

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('help')
            . $this->getChildHtml('grid')
        ;
    }

    //########################################
}