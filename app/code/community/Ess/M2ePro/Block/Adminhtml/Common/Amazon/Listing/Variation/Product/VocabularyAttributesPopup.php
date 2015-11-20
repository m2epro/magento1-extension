<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Variation_Product_VocabularyAttributesPopup
    extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingVocabularyAttributesPopup');
        // ---------------------------------------

        $this->setTemplate('M2ePro/common/amazon/listing/variation/product/vocabulary_attributes_popup.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $data = array(
            'class'   => 'switch-to-individual-btn',
            'label'   => Mage::helper('M2ePro')->__('Yes'),
            'onclick' => 'AmazonListingVariationProductManageHandlerObj.addAttributesToVocabulary(true)',
        );
        $this->setChild(
            'yes_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );

        $data = array(
            'class'   => 'switch-to-individual-popup-close',
            'label'   => Mage::helper('M2ePro')->__('No'),
            'onclick' => 'AmazonListingVariationProductManageHandlerObj.addAttributesToVocabulary(false)',
        );
        $this->setChild(
            'no_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );

        return $this;
    }
}