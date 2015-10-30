<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Magento_Product_Rule_Custom_AmazonOnlineQty
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'amazon_online_qty';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('QTY');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return mixed
     */
    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('online_qty');
    }

    //########################################
}