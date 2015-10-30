<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule_Custom_EbayPrice
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'ebay_online_current_price';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Price');
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'price';
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $minPrice = $product->getData('min_online_price');
        $maxPrice = $product->getData('max_online_price');

        if (!empty($minPrice) && !empty($maxPrice) && $minPrice != $maxPrice) {
            return array(
                $product->getData('min_online_price'),
                $product->getData('max_online_price'),
            );
        }

        return $product->getData('min_online_price');
    }

    //########################################
}