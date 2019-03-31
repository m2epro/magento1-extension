<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Variation_Product_VocabularyOptionsPopup
    extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingVocabularyAttributesPopup');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/listing/variation/product/vocabulary_options_popup.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $data = array(
            'class'   => 'switch-to-individual-btn',
            'label'   => Mage::helper('M2ePro')->__('Yes'),
            'onclick' => 'AmazonListingVariationProductManageHandlerObj.addOptionsToVocabulary(true)',
        );
        $this->setChild(
            'yes_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );

        $data = array(
            'class'   => 'switch-to-individual-popup-close',
            'label'   => Mage::helper('M2ePro')->__('No'),
            'onclick' => 'AmazonListingVariationProductManageHandlerObj.addOptionsToVocabulary(false)',
        );
        $this->setChild(
            'no_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );

        return $this;
    }
}