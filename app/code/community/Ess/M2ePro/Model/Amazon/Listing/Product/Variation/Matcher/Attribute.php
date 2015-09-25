<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute
{
    /** @var Ess_M2ePro_Model_Magento_Product $magentoProduct */
    private $magentoProduct = null;

    private $sourceAttributes = array();

    private $destinationAttributes = array();

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute_Resolver $resolver */
    private $resolver = null;

    private $matchedAttributes = array();

    private $canUseDictionary = true;

    // ##########################################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $product)
    {
        $this->magentoProduct = $product;
        $this->sourceAttributes = array();

        $this->matchedAttributes = array();

        return $this;
    }

    // ----------------------------------------------------------

    public function setSourceAttributes(array $attributes)
    {
        $this->sourceAttributes = $attributes;
        $this->magentoProduct   = null;

        $this->matchedAttributes = array();

        return $this;
    }

    public function setDestinationAttributes(array $attributes)
    {
        $this->destinationAttributes = $attributes;
        $this->matchedAttributes     = array();

        return $this;
    }

    // ----------------------------------------------------------

    public function canUseDictionary($flag = true)
    {
        $this->canUseDictionary = $flag;
        return $this;
    }

    // ##########################################################

    public function isAmountEqual()
    {
        return count($this->getSourceAttributes()) == count($this->getDestinationAttributes());
    }

    public function isSourceAmountGreater()
    {
        return count($this->getSourceAttributes()) > count($this->getDestinationAttributes());
    }

    public function isDestinationAmountGreater()
    {
        return count($this->getSourceAttributes()) < count($this->getDestinationAttributes());
    }

    // ----------------------------------------------------------

    public function getMatchedAttributes()
    {
        if (empty($this->matchedAttributes)) {
            $this->match();
        }

        return $this->matchedAttributes;
    }

    // ----------------------------------------------------------

    public function isFullyMatched()
    {
        return count($this->getMagentoUnmatchedAttributes()) <= 0 && count($this->getChannelUnmatchedAttributes()) <= 0;
    }

    public function isNotMatched()
    {
        return count($this->getMatchedAttributes()) <= 0;
    }

    public function isPartiallyMatched()
    {
        return !$this->isFullyMatched() && !$this->isNotMatched();
    }

    // ----------------------------------------------------------

    public function getMagentoUnmatchedAttributes()
    {
        return array_keys($this->getMatchedAttributes(), null);
    }

    public function getChannelUnmatchedAttributes()
    {
        $matchedChannelAttributes = array_values($this->getMatchedAttributes());
        return array_diff($this->destinationAttributes, $matchedChannelAttributes);
    }

    // ##########################################################

    private function match()
    {
        if (!is_null($this->magentoProduct) && $this->magentoProduct->isGroupedType() &&
            !$this->magentoProduct->getVariationVirtualAttributes()
        ) {
            $channelAttribute = reset($this->destinationAttributes);

            $this->matchedAttributes = array(
                Ess_M2ePro_Model_Magento_Product_Variation::GROUPED_PRODUCT_ATTRIBUTE_LABEL => $channelAttribute
            );

            return;
        }

        if ($this->matchByNames()) {
            return;
        }

        if (!$this->canUseDictionary) {
            return;
        }

        if ($this->matchByLocalVocabulary()) {
            return;
        }

        $this->matchByServerVocabulary();
    }

    private function matchByNames()
    {
        $this->getResolver()->clearSourceAttributes();

        foreach ($this->getSourceAttributes() as $attribute) {
            $this->getResolver()->addSourceAttribute(
                $attribute, $this->prepareAttributeNames($attribute)
            );
        }

        $this->getResolver()->clearDestinationAttributes();

        foreach ($this->getDestinationAttributes() as $attribute) {
            $this->getResolver()->addDestinationAttribute(
                $attribute, $this->prepareAttributeNames($attribute)
            );
        }

        $this->matchedAttributes = $this->getResolver()->resolve()->getResolvedAttributes();

        return $this->isFullyMatched();
    }

    private function matchByLocalVocabulary()
    {
        $this->getResolver()->clearSourceAttributes();

        foreach ($this->getSourceAttributesData() as $attribute => $names) {
            $this->getResolver()->addSourceAttribute(
                $attribute, $this->prepareAttributeNames($attribute, $names)
            );
        }

        $this->getResolver()->clearDestinationAttributes();

        foreach ($this->getDestinationAttributesLocalVocabularyData() as $attribute => $names) {
            $this->getResolver()->addDestinationAttribute(
                $attribute, $this->prepareAttributeNames($attribute, $names)
            );
        }

        $this->matchedAttributes = $this->getResolver()->resolve()->getResolvedAttributes();

        return $this->isFullyMatched();
    }

    private function matchByServerVocabulary()
    {
        $this->getResolver()->clearSourceAttributes();

        foreach ($this->getSourceAttributesData() as $attribute => $names) {
            $this->getResolver()->addSourceAttribute(
                $attribute, $this->prepareAttributeNames($attribute, $names)
            );
        }

        $this->getResolver()->clearDestinationAttributes();

        foreach ($this->getDestinationAttributesServerVocabularyData() as $attribute => $names) {
            $this->getResolver()->addDestinationAttribute(
                $attribute, $this->prepareAttributeNames($attribute, $names)
            );
        }

        $this->matchedAttributes = $this->getResolver()->resolve()->getResolvedAttributes();

        return $this->isFullyMatched();
    }

    // ##########################################################

    private function getSourceAttributes()
    {
        if (!empty($this->sourceAttributes)) {
            return $this->sourceAttributes;
        }

        if (!is_null($this->magentoProduct)) {
            $magentoVariations = $this->magentoProduct
                ->getVariationInstance()
                ->getVariationsTypeStandard();

            $this->sourceAttributes = array_keys($magentoVariations['set']);
        }

        return $this->sourceAttributes;
    }

    private function getSourceAttributesData()
    {
        if (!is_null($this->magentoProduct)) {
            $magentoAttributesNames = $this->magentoProduct
                ->getVariationInstance()
                ->getTitlesVariationSet();

            $magentoStandardVariations = $this->magentoProduct
                ->getVariationInstance()
                ->getVariationsTypeStandard();

            $resultData = array();
            foreach (array_keys($magentoStandardVariations['set']) as $attribute) {
                $titles = array();
                if (isset($magentoAttributesNames[$attribute])) {
                    $titles = $magentoAttributesNames[$attribute]['titles'];
                }

                $resultData[$attribute] = $titles;
            }

            return $resultData;
        }

        return array_fill_keys($this->getSourceAttributes(), array());
    }

    private function getDestinationAttributes()
    {
        return $this->destinationAttributes;
    }

    private function getDestinationAttributesLocalVocabularyData()
    {
        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        $resultData = array();
        foreach ($this->getDestinationAttributes() as $attribute) {
            $resultData[$attribute] = $vocabularyHelper->getLocalAttributeNames($attribute);
        }

        return $resultData;
    }

    private function getDestinationAttributesServerVocabularyData()
    {
        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        $resultData = array();
        foreach ($this->getDestinationAttributes() as $attribute) {
            $resultData[$attribute] = $vocabularyHelper->getServerAttributeNames($attribute);
        }

        return $resultData;
    }

    // ----------------------------------------------------------

    private function getResolver()
    {
        if (!is_null($this->resolver)) {
            return $this->resolver;
        }

        $this->resolver = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute_Resolver');
        return $this->resolver;
    }

    private function prepareAttributeNames($attribute, array $names = array())
    {
        $names[] = $attribute;
        $names = array_unique($names);

        $names = array_map('trim', $names);
        $names = array_map('strtolower', $names);

        return $names;
    }

    // ##########################################################
}