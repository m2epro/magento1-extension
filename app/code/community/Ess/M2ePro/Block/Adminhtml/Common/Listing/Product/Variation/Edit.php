<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Variation_Edit
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Variation
{
    public $currentVariation = array();

    //##############################################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingProductVariationEdit');
        //------------------------------

        $this->setTemplate('M2ePro/common/listing/product/variation/edit.phtml');
    }

    //##############################################################

    protected function _beforeToHtml()
    {
        $this->_prepareButtons();

        $variationManager = $this->getListingProduct()->getChildObject()->getVariationManager();

        if ($this->getListingProduct()->getComponentMode() == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */

            $isVariationMatched = $variationManager->getTypeModel()->isVariationProductMatched();
        } else {
            $isVariationMatched = $variationManager->isVariationProductMatched();
        }

        if (!$isVariationMatched) {
            return $this;
        }

        $variations = $this->getListingProduct()->getVariations(true);
        /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
        $variation = reset($variations);

        /* @var $optionInstance Ess_M2ePro_Model_Listing_Product_Variation_Option */
        foreach ($variation->getOptions(true) as $optionInstance) {
            $option = $optionInstance->getOption();
            $attribute = $optionInstance->getAttribute();
            $this->currentVariation[$attribute] = $option;
        }

        return parent::_beforeToHtml();
    }

    //##############################################################

    protected function _prepareButtons()
    {
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Confirm'),
                'onclick' => '',
                'class' => 'confirm',
                'id' => 'variation_edit_confirm'
            ));
        $this->setChild('variation_edit_confirm', $buttonBlock);
    }

    //##############################################################
}