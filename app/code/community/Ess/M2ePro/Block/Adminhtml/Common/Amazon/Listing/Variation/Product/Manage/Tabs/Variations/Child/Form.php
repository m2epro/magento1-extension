<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Variation_Product_Manage_Tabs_Variations_Child_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected $childListingProducts = null;
    protected $currentProductVariations = null;
    protected $productVariationsTree = array();
    protected $channelVariationsTree = array();

    protected $listingProductId;

    //########################################

    /**
     * @param mixed $listingProductId
     * @return $this
     */
    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $listingProduct;

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/common/amazon/listing/variation/product/manage/tabs/variations/child/form.phtml');
    }

    // ---------------------------------------
    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if (empty($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->listingProduct;
    }

    //########################################

    public function isGeneralIdOwner()
    {
        return $this->getListingProduct()->getChildObject()->isGeneralIdOwner();
    }

    // ---------------------------------------

    public function hasChannelTheme()
    {
        return $this->getListingProduct()->getChildObject()->getVariationManager()->getTypeModel()->hasChannelTheme();
    }

    public function hasUnusedChannelVariations()
    {
        return count($this->getUsedChannelVariations()) < count($this->getCurrentChannelVariations());
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
        return $this->getUnusedVariations($this->getCurrentProductVariations(), $this->getUsedProductVariations());
    }

    public function getUnusedChannelVariations()
    {
        return $this->getUnusedVariations($this->getCurrentChannelVariations(), $this->getUsedChannelVariations());
    }

    private function getUnusedVariations($currentVariations, $usedVariations)
    {
        if (empty($currentVariations)) {
            return array();
        }

        if (empty($usedVariations)) {
            return $currentVariations;
        }

        $unusedOptions = array();

        foreach ($currentVariations as $id => $currentOption) {
            if ($this->isVariationExistsInArray($currentOption, $usedVariations)) {
                continue;
            }

            $unusedOptions[$id] = $currentOption;
        }

        return $unusedOptions;
    }

    private function isVariationExistsInArray(array $needle, array $haystack)
    {
        foreach ($haystack as $option) {
            if ($option != $needle) {
                continue;
            }

            return true;
        }

        return false;
    }

    // ---------------------------------------

    public function getChildListingProducts()
    {
        if (!is_null($this->childListingProducts)) {
            return $this->childListingProducts;
        }

        return $this->childListingProducts = $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getChildListingsProducts();
    }

    public function getCurrentProductVariations()
    {
        if (!is_null($this->currentProductVariations)) {
            return $this->currentProductVariations;
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

        return $this->currentProductVariations = $productVariations;
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
        $usedOptions = array();

        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationChannelMatched()) {
                continue;
            }

            $usedOptions[] = $childTypeModel->getChannelOptions();
        }

        return $usedOptions;
    }

    public function getUsedProductVariations()
    {
        $usedOptions = array();

        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                continue;
            }

            $usedOptions[] = $childTypeModel->getProductOptions();
        }

        return $usedOptions;
    }

    // ---------------------------------------

    public function getProductVariationsTree()
    {
        if (empty($this->productVariationsTree)) {

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

            $this->productVariationsTree = $this->prepareVariations(
                $firstAttribute,$unusedVariations,$variationsSets
            );
        }

        return $this->productVariationsTree;
    }

    public function getChannelVariationsTree()
    {
        if (empty($this->channelVariationsTree)) {

            $matchedAttributes = $this->getMatchedAttributes();
            $unusedVariations = $this->sortVariationsAttributes(
                $this->getUnusedChannelVariations(),
                array_values($matchedAttributes)
            );

            if (empty($unusedVariations)) {
                $this->channelVariationsTree = new stdClass();

                return $this->channelVariationsTree;
            }

            $variationsSets = $this->sortVariationAttributes(
                $this->getAttributesOptionsFromVariations($unusedVariations),
                array_values($matchedAttributes)
            );

            $firstAttribute = $matchedAttributes[key($matchedAttributes)];

            $this->channelVariationsTree = $this->prepareVariations(
                $firstAttribute,$unusedVariations,$variationsSets
            );
        }

        return $this->channelVariationsTree;
    }

    private function sortVariationsAttributes($variations, $sortTemplate)
    {
        foreach ($variations as $key => $variation) {
            $variations[$key] = $this->sortVariationAttributes($variation, $sortTemplate);
        }

        return $variations;
    }

    private function sortVariationAttributes($variation, $sortTemplate)
    {
        $sortedData = array();

        foreach ($sortTemplate as $attr) {
            $sortedData[$attr] = $variation[$attr];
        }

        return $sortedData;
    }

    private function prepareVariations($currentAttribute,$magentoVariations,$variationsSets,$filters = array())
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
                    $nextAttribute,$magentoVariations,$variationsSets,$filters
                );

                if (!$result) {
                    continue;
                }

                $return[$currentAttribute][$option] = $result;
            }

            ksort($return[$currentAttribute]);

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

        ksort($return[$currentAttribute]);

        return $return;
    }

    //########################################
}