<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Variation extends Mage_Adminhtml_Block_Widget
{
    private $magentoVariationsSets = NULL;
    private $magentoVariationsCombinations = NULL;

    private $magentoVariationsTree = NULL;

    private $listingProduct = NULL;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        if (is_null($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->getComponent(), 'Listing_Product', $this->getListingProductId()
            );
            $this->listingProduct->getMagentoProduct()->enableCache();
        }
        return $this->listingProduct;
    }

    public function getMagentoVariationsSets()
    {
        if (is_null($this->magentoVariationsSets)) {
            $temp = $this->getListingProduct()
                ->getMagentoProduct()
                ->getVariationInstance()
                ->getVariationsTypeStandard();
            $this->magentoVariationsSets = $temp['set'];
        }

        return $this->magentoVariationsSets;
    }

    public function getMagentoVariationsCombinations()
    {
        if (is_null($this->magentoVariationsCombinations)) {
            $temp = $this->getListingProduct()
                ->getMagentoProduct()
                ->getVariationInstance()
                ->getVariationsTypeStandard();
            $this->magentoVariationsCombinations = $temp['variations'];
        }

        return $this->magentoVariationsCombinations;
    }

    //########################################

    public function getMagentoVariationsTree()
    {
        if (is_null($this->magentoVariationsTree)) {

            $firstAttribute = $this->getMagentoVariationsSets();
            $firstAttribute = key($firstAttribute);

            $this->magentoVariationsTree = $this->prepareVariations(
                $firstAttribute,$this->getMagentoVariationsCombinations()
            );
        }

        return $this->magentoVariationsTree;
    }

    // ---------------------------------------

    private function prepareVariations($currentAttribute,$magentoVariations,$filters = array())
    {
        $return = false;

        $magentoVariationsSets = $this->getMagentoVariationsSets();

        $temp = array_flip(array_keys($magentoVariationsSets));

        $lastAttributePosition = count($magentoVariationsSets) - 1;
        $currentAttributePosition = $temp[$currentAttribute];

        if ($currentAttributePosition != $lastAttributePosition) {

            $temp = array_keys($magentoVariationsSets);
            $nextAttribute = $temp[$currentAttributePosition + 1];

            foreach ($magentoVariationsSets[$currentAttribute] as $value) {

                $filters[$currentAttribute] = $value;

                $result = $this->prepareVariations(
                    $nextAttribute,$magentoVariations,$filters
                );

                if (!$result) {
                    continue;
                }

                $return[$currentAttribute][$value] = $result;
            }

            return $return;
        }

        $return = false;
        foreach ($magentoVariations as $key => $magentoVariation) {
            foreach ($magentoVariation as $option) {

                $value = $option['option'];
                $attribute = $option['attribute'];

                if ($attribute == $currentAttribute) {

                    if (count($magentoVariationsSets) != 1) {
                        continue;
                    }

                    $values = array_flip($magentoVariationsSets[$currentAttribute]);
                    $return = array($currentAttribute => $values);

                    foreach ($return[$currentAttribute] as &$value) {
                        $value = true;
                    }

                    return $return;
                }

                if ($value != $filters[$attribute]) {
                    unset($magentoVariations[$key]);
                    continue;
                }

                foreach ($magentoVariation as $tempOption) {
                    if ($tempOption['attribute'] == $currentAttribute) {
                        $value = $tempOption['option'];
                        $return[$currentAttribute][$value] = true;
                    }
                }
            }
        }

        if (count($magentoVariations) < 1) {
            return false;
        }

        return $return;
    }

    //########################################
}