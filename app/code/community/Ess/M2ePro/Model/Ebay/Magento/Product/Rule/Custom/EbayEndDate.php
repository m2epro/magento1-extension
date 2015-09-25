<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule_Custom_EbayEndDate
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'ebay_end_date';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('End Date');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $endDate = $product->getData('end_date');
        if (empty($endDate)) {
            return null;
        }

        $endDate = new DateTime($endDate);

        return strtotime($endDate->format('Y-m-d'));
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