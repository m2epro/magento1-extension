<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Variation_Edit
    extends Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Variation
{
    public $currentVariation = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingProductVariationEdit');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/listing/product/variation/edit.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->_prepareButtons();

        $variationManager = $this->getListingProduct()->getChildObject()->getVariationManager();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $isVariationMatched = $variationManager->getTypeModel()->isVariationProductMatched();

        if (!$isVariationMatched) {
            return $this;
        }

        $variations = $this->getListingProduct()->getVariations(true);
        if (count($variations) <= 0) {
            throw new Ess_M2ePro_Model_Exception('There are no variations for a variation product.',
                                                 array(
                                                     'listing_product_id' => $this->getListingProduct()->getId()
                                                 ));
        }

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

    //########################################

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

    //########################################
}