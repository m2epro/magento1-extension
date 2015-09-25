<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule_Custom_EbayAvailableQty
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'ebay_available_qty';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Available QTY');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('available_qty');
    }

    // #################################################
}