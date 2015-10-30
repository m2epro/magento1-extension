<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_Step_Categories extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingTransferringStepCategories');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/transferring/step/categories.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $data = array(
            'id'      => 'back_button_category',
            'class'   => 'back back_button',
            'label'   => Mage::helper('M2ePro')->__('Back'),
            'onclick' => 'EbayListingTransferringHandlerObj.back();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('back_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'yes_button_category',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Yes, I want'),
            'onclick' => 'EbayListingTransferringHandlerObj.confirm(true);',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('yes_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'no_button_category',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('No, Complete Wizard'),
            'onclick' => 'EbayListingTransferringHandlerObj.confirm();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('no_button', $buttonBlock);
        // ---------------------------------------
    }

    //########################################

    public function isAllowedStep()
    {
        return (bool)$this->getData('is_allowed');
    }

    //########################################
}