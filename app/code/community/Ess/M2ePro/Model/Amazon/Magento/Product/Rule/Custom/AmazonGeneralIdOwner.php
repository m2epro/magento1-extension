<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product as AmazonListingProduct;

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonGeneralIdOwner
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'is_general_id_owner';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('ASIN/ISBN Creator');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return (int)$product->getData('is_general_id_owner');
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
                'value' => AmazonListingProduct::IS_GENERAL_ID_OWNER_YES,
                'label' => Mage::helper('M2ePro')->__('Yes')
            ),
            array(
                'value' => AmazonListingProduct::IS_GENERAL_ID_OWNER_NO,
                'label' => Mage::helper('M2ePro')->__('No')
            ),
        );
    }

    //########################################
}
