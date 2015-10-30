<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_LogicalUnit
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Abstract
{
    //########################################

    /**
     * @return bool
     */
    public function isActualProductAttributes()
    {
        $productAttributes = array_map('strtolower', (array)$this->getProductAttributes());
        $magentoAttributes = array_map('strtolower', (array)$this->getMagentoAttributes());

        sort($productAttributes);
        sort($magentoAttributes);

        return $productAttributes == $magentoAttributes;
    }

    //########################################

    public function getProductAttributes()
    {
        return $this->getListingProduct()->getSetting('additional_data', 'variation_product_attributes', array());
    }

    public function resetProductAttributes($save = true)
    {
        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_product_attributes', $this->getMagentoAttributes()
        );

        $save && $this->getListingProduct()->save();
    }

    //########################################

    public function clearTypeData()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        unset($additionalData['variation_product_attributes']);
        $this->getListingProduct()->setSettings('additional_data', $additionalData);

        $this->getListingProduct()->save();
    }

    //########################################

    protected function getMagentoAttributes()
    {
        $magentoVariations = $this->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();
        return array_keys($magentoVariations['set']);
    }

    //########################################
}