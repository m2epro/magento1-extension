<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonOnlineQty
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'amazon_online_qty';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('QTY');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('online_qty');
    }

    // #################################################
}