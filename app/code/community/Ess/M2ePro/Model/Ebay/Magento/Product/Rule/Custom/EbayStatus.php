<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_SOLD,
                'label' => Mage::helper('M2ePro')->__('Sold'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
                'label' => Mage::helper('M2ePro')->__('Stopped'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED,
                'label' => Mage::helper('M2ePro')->__('Finished'),
            ),
            array(
                'value' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
                'label' => Mage::helper('M2ePro')->__('Pending'),
            ),
        );
    }

    //########################################
}