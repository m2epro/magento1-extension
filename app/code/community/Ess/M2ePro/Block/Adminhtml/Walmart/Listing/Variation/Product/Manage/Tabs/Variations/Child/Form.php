<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Variation_Product_Manage_Tabs_Variations_Child_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_childListingProducts     = null;
    protected $_currentProductVariations = null;
    protected $_productVariationsTree    = array();
    protected $_channelVariationsTree    = array();

    protected $_listingProductId;

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    //########################################

    /**
     * @param mixed $listingProductId
     * @return $this
     */
    public function setListingProductId($listingProductId)
    {
        $this->_listingProductId = $listingProductId;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->_listingProductId;
    }

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/walmart/listing/variation/product/manage/tabs/variations/child/form.phtml');
    }

    // ---------------------------------------
    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if (empty($this->_listingProduct)) {
            $this->_listingProduct = Mage::helper('M2ePro/Component_Walmart')
                                         ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    //########################################

    public function hasChannelAttributes()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->hasChannelAttributes();
    }

    // ---------------------------------------

    public function getMatchedAttributes()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getMatchedAttributes();
    }

    public function getVirtualProductAttributes()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getVirtualProductAttributes();
    }

    public function getVirtualChannelAttributes()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getVirtualChannelAttributes();
    }

    // ---------------------------------------

    public function getUnusedProductVariations()
    {
        return $this->getListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel()
            ->getUnusedProductOptions();
    }

    // ---------------------------------------

    public function getChildListingProducts()
    {
        if ($this->_childListingProducts !== null) {
            return $this->_childListingProducts;
        }

        return $this->_childListingProducts = $this->getListingProduct()->getChildObject()
                                                   ->getVariationManager()->getTypeModel()->getChildListingsProducts();
    }

    public function getCurrentProductVariations()
    {
        if ($this->_currentProductVariations !== null) {
            return $this->_currentProductVariations;
        }

        $magentoProductVariations = $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();

        $productVariations = array();

        foreach ($magentoProductVariations['variations'] as $option) {
            $productOption = array();

            foreach ($option as $attribute) {
                $productOption[$attribute['attribute']] = $attribute['option'];
            }

            $productVariations[] = $productOption;
        }

        return $this->_currentProductVariations = $productVariations;
    }

    public function getCurrentChannelVariations()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getChannelVariations();
    }

    // ---------------------------------------

    public function getAttributesOptionsFromVariations($variations)
    {
        $attributesOptions = array();

        foreach ($variations as $variation) {
            foreach ($variation as $attr => $option) {
                if (!isset($attributesOptions[$attr])) {
                    $attributesOptions[$attr] = array();
                }

                if (!in_array($option, $attributesOptions[$attr])) {
                    $attributesOptions[$attr][] = $option;
                }
            }
        }

        ksort($attributesOptions);

        return $attributesOptions;
    }

    // ---------------------------------------

    public function getUsedChannelVariations()
    {
        return $this->getListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel()
            ->getUsedChannelOptions();
    }

    public function getUsedProductVariations()
    {
        return $this->getListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel()
            ->getUsedProductOptions();
    }

    // ---------------------------------------

    public function getProductVariationsTree()
    {
        if (empty($this->_productVariationsTree)) {
            $matchedAttributes = $this->getMatchedAttributes();
            $unusedVariations = $this->sortVariationsAttributes(
                $this->getUnusedProductVariations(),
                array_keys($matchedAttributes)
            );
            $variationsSets = $this->sortVariationAttributes(
                $this->getAttributesOptionsFromVariations($unusedVariations),
                array_keys($matchedAttributes)
            );

            $firstAttribute = key($matchedAttributes);

            $this->_productVariationsTree = $this->prepareVariations(
                $firstAttribute, $unusedVariations, $variationsSets
            );
        }

        return $this->_productVariationsTree;
    }

    protected function sortVariationsAttributes($variations, $sortTemplate)
    {
        foreach ($variations as $key => $variation) {
            $variations[$key] = $this->sortVariationAttributes($variation, $sortTemplate);
        }

        return $variations;
    }

    protected function sortVariationAttributes($variation, $sortTemplate)
    {
        $sortedData = array();

        foreach ($sortTemplate as $attr) {
            $sortedData[$attr] = $variation[$attr];
        }

        return $sortedData;
    }

    protected function prepareVariations($currentAttribute,$magentoVariations,$variationsSets,$filters = array())
    {
        $return = false;

        $temp = array_flip(array_keys($variationsSets));

        $lastAttributePosition = count($variationsSets) - 1;
        $currentAttributePosition = $temp[$currentAttribute];

        if ($currentAttributePosition != $lastAttributePosition) {
            $temp = array_keys($variationsSets);
            $nextAttribute = $temp[$currentAttributePosition + 1];

            foreach ($variationsSets[$currentAttribute] as $option) {
                $filters[$currentAttribute] = $option;

                $result = $this->prepareVariations(
                    $nextAttribute, $magentoVariations, $variationsSets, $filters
                );

                if (!$result) {
                    continue;
                }

                $return[$currentAttribute][$option] = $result;
            }

            if ($return !== false) {
                ksort($return[$currentAttribute]);
            }

            return $return;
        }

        $return = false;
        foreach ($magentoVariations as $key => $magentoVariation) {
            foreach ($magentoVariation as $attribute => $option) {
                if ($attribute == $currentAttribute) {
                    if (count($variationsSets) != 1) {
                        continue;
                    }

                    $values = array_flip($variationsSets[$currentAttribute]);
                    $return = array($currentAttribute => $values);

                    foreach ($return[$currentAttribute] as &$option) {
                        $option = true;
                    }

                    return $return;
                }

                if ($option != $filters[$attribute]) {
                    unset($magentoVariations[$key]);
                    continue;
                }

                foreach ($magentoVariation as $tempAttribute => $tempOption) {
                    if ($tempAttribute == $currentAttribute) {
                        $option = $tempOption;
                        $return[$currentAttribute][$option] = true;
                    }
                }
            }
        }

        if (count($magentoVariations) < 1) {
            return false;
        }

        if ($return !== false) {
            ksort($return[$currentAttribute]);
        }

        return $return;
    }

    //########################################
}
