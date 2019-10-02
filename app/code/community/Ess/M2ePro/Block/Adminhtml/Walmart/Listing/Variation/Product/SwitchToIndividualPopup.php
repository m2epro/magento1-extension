<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Variation_Product_SwitchToIndividualPopup
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingAddCategoryTemplateManualPopup');
        // ---------------------------------------

        $this->setTemplate('M2ePro/walmart/listing/variation/product/switch_to_individual_popup.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $data = array(
            'class'   => 'switch-to-individual-btn',
            'label'   => Mage::helper('M2ePro')->__('Yes')
        );
        $this->setChild(
            'yes_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );

        $data = array(
            'class'   => 'switch-to-individual-popup-close',
            'label'   => Mage::helper('M2ePro')->__('No')
        );
        $this->setChild(
            'no_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );

        return $this;
    }

    //########################################
}
