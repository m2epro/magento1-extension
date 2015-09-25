<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Product_Rule_Custom_Stock
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'is_in_stock';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Stock Availability');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($product)
            ->getIsInStock();
    }

    // #################################################

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
                'value' => 1,
                'label' => Mage::helper('catalogrule')->__('In Stock')
            ),
            array(
                'value' => 0,
                'label' => Mage::helper('catalogrule')->__('Out Of Stock')
            ),
        );
    }

    // #################################################
}