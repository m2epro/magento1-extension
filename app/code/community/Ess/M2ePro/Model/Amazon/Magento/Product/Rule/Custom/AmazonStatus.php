<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonStatus
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'amazon_status';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Status');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $status = $product->getData('amazon_status');
        $variationChildStatuses = $product->getData('variation_child_statuses');

        if ($product->getData('is_variation_parent') && !empty($variationChildStatuses)) {
            $status = json_decode($variationChildStatuses, true);
        }

        return $status;
    }

    public function getInputType()
    {
        return 'select';
    }

    public function getValueElementType()
    {
        return 'select';
    }

    public function getOptions()
    {
        return array(
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN,
                'label' => Mage::helper('M2ePro')->__('Unknown'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
                'label' => Mage::helper('M2ePro')->__('Not Listed'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
                'label' => Mage::helper('M2ePro')->__('Active'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
                'label' => Mage::helper('M2ePro')->__('Inactive'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
                'label' => Mage::helper('M2ePro')->__('Inactive (Blocked)'),
            ),
        );
    }

    // #################################################
}