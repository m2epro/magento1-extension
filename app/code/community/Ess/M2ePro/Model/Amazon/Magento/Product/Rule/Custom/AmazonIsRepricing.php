<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product as AmazonListingProduct;

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonIsRepricing
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'amazon_is_repricing';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('On Repricing');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $isRepricing = (int)$product->getData('is_repricing');
        $repricingState = (int)$product->getData('variation_parent_repricing_state');

        if (($this->filterOperator == '==' && $this->filterCondition == AmazonListingProduct::IS_REPRICING_YES) ||
            ($this->filterOperator == '!=' && $this->filterCondition == AmazonListingProduct::IS_REPRICING_NO)) {
            return $isRepricing;
        }

        return (!$isRepricing || $repricingState == AmazonListingProduct::VARIATION_PARENT_IS_REPRICING_STATE_PARTIAL)
            ? AmazonListingProduct::IS_REPRICING_NO
            : AmazonListingProduct::IS_REPRICING_YES;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return array(
            array(
                'value' => AmazonListingProduct::IS_REPRICING_NO,
                'label' => Mage::helper('M2ePro')->__('No'),
            ),
            array(
                'value' => AmazonListingProduct::IS_REPRICING_YES,
                'label' => Mage::helper('M2ePro')->__('Yes'),
            ),
        );
    }

    //########################################
}