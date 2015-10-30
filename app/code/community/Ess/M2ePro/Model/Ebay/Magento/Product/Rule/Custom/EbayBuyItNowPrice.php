<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule_Custom_EbayBuyItNowPrice
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'ebay_online_buyitnow_price';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('"Buy It Now" Price');
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'price';
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('online_buyitnow_price');
    }

    //########################################
}