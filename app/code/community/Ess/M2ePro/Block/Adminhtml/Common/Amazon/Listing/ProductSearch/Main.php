<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_ProductSearch_Main
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/common/amazon/listing/product_search/main.phtml');
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'id'    => 'productSearch_submit_button',
            'label' => Mage::helper('M2ePro')->__('Search'),
            'class' => 'productSearch_submit_button submit'
        );
        $buttonSubmitBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('productSearch_submit_button', $buttonSubmitBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'    => 'productSearch_back_button',
            'label' => Mage::helper('M2ePro')->__('Back'),
            'class' => 'productSearch_back_button'
        );
        $buttonBackBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('productSearch_back_button', $buttonBackBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'    => 'productSearch_cancel_button',
            'label' => Mage::helper('M2ePro')->__('Close'),
            'class' => 'productSearch_cancel_button'
        );
        $buttonCancelBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('productSearch_cancel_button', $buttonCancelBlock);
        // ---------------------------------------

        $buttonResetBlock = $this->getChild('productSearch_cleanSuggest_button');

        if (empty($buttonResetBlock)) {
            // ---------------------------------------
            $data = array(
                'id'      => 'productSearch_cleanSuggest_button',
                'label'   => Mage::helper('M2ePro')->__('Clear Search Results'),
                'class'   => 'productSearch_cleanSuggest_button',
                'onclick' =>  'ListingGridHandlerObj.productSearchHandler.clearSearchResultsAndOpenSearchMenu()'
            );
            $buttonResetBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('productSearch_cleanSuggest_button', $buttonResetBlock);
            // ---------------------------------------
        }

        parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $vocabularyAttributesBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_amazon_listing_variation_product_vocabularyAttributesPopup'
        );

        $vocabularyOptionsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_amazon_listing_variation_product_vocabularyOptionsPopup'
        );

        return $vocabularyAttributesBlock->toHtml() . $vocabularyOptionsBlock->toHtml() . parent::_toHtml();
    }

    //########################################
}