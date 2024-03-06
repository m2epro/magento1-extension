<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule_Custom_EbayStatus
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'ebay_status';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Status');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('ebay_status');
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
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
                'label' => Mage::helper('M2ePro')->__('Not Listed'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
                'label' => Mage::helper('M2ePro')->__('Listed'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN,
                'label' => Mage::helper('M2ePro')->__('Listed (Hidden)'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE,
                'label' => Mage::helper('M2ePro')->__('Inactive'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
                'label' => Mage::helper('M2ePro')->__('Pending'),
            ),
        );
    }

    //########################################
}
