<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product as AmazonListingProduct;

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonIsAfnChanel
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'amazon_is_afn_chanel';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Fulfillment');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $isAfn = (int)$product->getData('is_afn_channel');
        $afnState = (int)$product->getData('variation_parent_afn_state');

        if (($this->filterOperator == '==' && $this->filterCondition == AmazonListingProduct::IS_AFN_CHANNEL_YES) ||
            ($this->filterOperator == '!=' && $this->filterCondition == AmazonListingProduct::IS_AFN_CHANNEL_NO)) {
            return $isAfn;
        }

        return (!$isAfn || $afnState == AmazonListingProduct::VARIATION_PARENT_IS_AFN_STATE_PARTIAL)
            ? AmazonListingProduct::IS_AFN_CHANNEL_NO
            : AmazonListingProduct::IS_AFN_CHANNEL_YES;
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
                'value' => AmazonListingProduct::IS_AFN_CHANNEL_NO,
                'label' => Mage::helper('M2ePro')->__('Merchant'),
            ),
            array(
                'value' => AmazonListingProduct::IS_AFN_CHANNEL_YES,
                'label' => Mage::helper('M2ePro')->__('Amazon'),
            ),
        );
    }

    //########################################
}