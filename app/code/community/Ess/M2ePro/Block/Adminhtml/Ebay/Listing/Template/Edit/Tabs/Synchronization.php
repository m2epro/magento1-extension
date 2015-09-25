<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Edit_Tabs_Synchronization extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingTemplateEditTabsSynchronization');
        //------------------------------
    }

    // ####################################

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        //------------------------------
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_edit_synchronization_help');
        $this->setChild('help', $helpBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'template_nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION,
        );
        $switcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher');
        $switcherBlock->setData($data);

        $this->setChild('synchronization', $switcherBlock);
        //------------------------------

        return $this;
    }

    // ####################################

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('help')
            . $this->getChildHtml('synchronization')
        ;
    }

    // ####################################
}