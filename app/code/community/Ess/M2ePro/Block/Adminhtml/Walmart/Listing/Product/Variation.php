<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Variation extends Mage_Adminhtml_Block_Widget
{
    protected $_magentoVariationsSets;
    protected $_magentoVariationsCombinations;

    protected $_magentoVariationsTree;

    protected $_listingProduct;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        if ($this->_listingProduct === null) {
            $this->_listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
                Ess_M2ePro_Helper_Component_Walmart::NICK, 'Listing_Product', $this->getListingProductId()
            );
            $this->_listingProduct->getMagentoProduct()->enableCache();
        }

        return $this->_listingProduct;
    }

    public function getMagentoVariationsSets()
    {
        if ($this->_magentoVariationsSets === null) {
            $temp                         = $this->getListingProduct()
                ->getMagentoProduct()
                ->getVariationInstance()
                ->getVariationsTypeStandard();
            $this->_magentoVariationsSets = $temp['set'];
        }

        return $this->_magentoVariationsSets;
    }

    public function getMagentoVariationsCombinations()
    {
        if ($this->_magentoVariationsCombinations === null) {
            $temp                                 = $this->getListingProduct()
                ->getMagentoProduct()
                ->getVariationInstance()
                ->getVariationsTypeStandard();
            $this->_magentoVariationsCombinations = $temp['variations'];
        }

        return $this->_magentoVariationsCombinations;
    }

    //########################################

    public function getMagentoVariationsTree()
    {
        if ($this->_magentoVariationsTree === null) {
            $firstAttribute = $this->getMagentoVariationsSets();
            $firstAttribute = key($firstAttribute);

            $this->_magentoVariationsTree = $this->prepareVariations(
                $firstAttribute, $this->getMagentoVariationsCombinations()
            );
        }

        return $this->_magentoVariationsTree;
    }

    // ---------------------------------------

    protected function prepareVariations($currentAttribute, $magentoVariations, $filters = array())
    {
        $return = false;

        $magentoVariationsSets = $this->getMagentoVariationsSets();

        $temp = array_flip(array_keys($magentoVariationsSets));

        if (!isset($temp[$currentAttribute])) {
            return false;
        }

        $lastAttributePosition = count($magentoVariationsSets) - 1;
        $currentAttributePosition = $temp[$currentAttribute];

        if ($currentAttributePosition != $lastAttributePosition) {
            $temp = array_keys($magentoVariationsSets);
            $nextAttribute = $temp[$currentAttributePosition + 1];

            foreach ($magentoVariationsSets[$currentAttribute] as $value) {
                $filters[$currentAttribute] = $value;

                $result = $this->prepareVariations(
                    $nextAttribute, $magentoVariations, $filters
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