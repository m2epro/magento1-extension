<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule_Custom_EbayBuyItNowPrice
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'ebay_online_buyitnow_price';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('"Buy It Now" Price');
    }

    public function getInputType()
    {
        return 'price';
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('online_buyitnow_price');
    }

    // #################################################
}