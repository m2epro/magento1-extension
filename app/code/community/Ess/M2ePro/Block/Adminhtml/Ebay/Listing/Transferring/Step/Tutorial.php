<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_Step_Tutorial extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingTransferringStepTutorial');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/transferring/step/tutorial.phtml');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        //------------------------------
        $data = array(
            'id'      => 'confirm_button_tutorial',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'EbayListingTransferringHandlerObj.go();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ####################################

    public function isAllowedStep()
    {
        return (bool)$this->getData('is_allowed');
    }

    // ####################################
}