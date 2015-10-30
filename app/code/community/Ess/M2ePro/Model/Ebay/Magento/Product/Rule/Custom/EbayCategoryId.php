<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule_Custom_EbayCategoryId
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    //########################################

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return 'ebay_online_category';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Category ID');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        $onlineCategory = $product->getData('online_category');
        if (empty($onlineCategory)) {
            return null;
        }

        preg_match('/^.+\((\d+)\)$/x', $onlineCategory, $matches);

        if (empty($matches[1])) {
            return null;
        }

        return $matches[1];
    }

    //########################################
}