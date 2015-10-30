<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Rule_Custom_Stock
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'is_in_stock';
    }

    /**
     * @return string
     */
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

    //########################################

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
                'value' => 1,
                'label' => Mage::helper('catalogrule')->__('In Stock')
            ),
            array(
                'value' => 0,
                'label' => Mage::helper('catalogrule')->__('Out Of Stock')
            ),
        );
    }

    //########################################
}