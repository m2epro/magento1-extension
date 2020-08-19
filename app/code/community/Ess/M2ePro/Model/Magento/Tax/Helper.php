<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Tax_Helper
{
    //########################################

    public function hasRatesForCountry($countryId)
    {
        return Mage::getModel('tax/calculation_rate')
            ->getCollection()
            ->addFieldToFilter('tax_country_id', $countryId)
            ->addFieldToFilter('code', array('neq' => Ess_M2ePro_Model_Magento_Tax_Rule_Builder::TAX_RATE_CODE_PRODUCT))
            ->addFieldToFilter(
                'code', array('neq' => Ess_M2ePro_Model_Magento_Tax_Rule_Builder::TAX_RATE_CODE_SHIPPING)
            )
            ->getSize();
    }

    /**
     * Return store tax rate for shipping
     *
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function getStoreShippingTaxRate($store)
    {
        $request = new Varien_Object();
        $request->setProductClassId(Mage::getSingleton('tax/config')->getShippingTaxClass($store));

        return Mage::getSingleton('tax/calculation')->getStoreRate($request, $store);
    }

    public function isCalculationBasedOnOrigin($store)
    {
        return Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $store) == 'origin';
    }

    //########################################
}