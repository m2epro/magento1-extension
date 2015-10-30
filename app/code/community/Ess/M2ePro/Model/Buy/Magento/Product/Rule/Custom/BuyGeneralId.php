<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Magento_Product_Rule_Custom_BuyGeneralId
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'buy_general_id';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('SKU');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('general_id');
    }

    //########################################
}