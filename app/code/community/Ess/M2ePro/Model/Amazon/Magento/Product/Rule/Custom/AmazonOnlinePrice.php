<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonOnlinePrice
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'amazon_online_price';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Price');
    }

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

    // #################################################
}