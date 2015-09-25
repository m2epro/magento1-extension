<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule_Custom_EbayStartDate
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'ebay_start_date';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Start Date');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $startDate = $product->getData('start_date');
        if (empty($startDate)) {
            return null;
        }

        $startDate = new DateTime($startDate);

        return strtotime($startDate->format('Y-m-d'));
    }

    public function getInputType()
    {
        return 'date';
    }

    public function getValueElementType()
    {
        return 'date';
    }

    // #################################################
}