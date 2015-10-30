<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonStatus
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'amazon_status';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Status');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $status = $product->getData('amazon_status');
        $variationChildStatuses = $product->getData('variation_child_statuses');

        if ($product->getData('is_variation_parent') && !empty($variationChildStatuses)) {
            $status = json_decode($variationChildStatuses, true);
        }

        return $status;
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

    //########################################
}