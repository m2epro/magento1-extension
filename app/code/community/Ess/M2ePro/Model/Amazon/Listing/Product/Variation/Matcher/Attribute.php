<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute
{
    /** @var Ess_M2ePro_Model_Magento_Product $_magentoProduct */
    protected $_magentoProduct = null;

    protected $_sourceAttributes = array();

    protected $_destinationAttributes = array();

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute_Resolver $_resolver */
    protected $_resolver = null;

    protected $_matchedAttributes = null;

    protected $_canUseDictionary = true;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $product
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $product)
    {
        $this->_magentoProduct   = $product;
        $this->_sourceAttributes = array();

        $this->_matchedAttributes = null;

        return $this;
    }

    // ---------------------------------------

    /**
     * @param array $attributes
     * @return $this
     */
    public function setSourceAttributes(array $attributes)
    {
        $this->_sourceAttributes = $attributes;
        $this->_magentoProduct   = null;

        $this->_matchedAttributes = null;

        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setDestinationAttributes(array $attributes)
    {
        $this->_destinationAttributes = $attributes;
        $this->_matchedAttributes     = null;

        return $this;
    }

    // ---------------------------------------

    /**
     * @param bool $flag
     * @return $this
     */
    public function canUseDictionary($flag = true)
    {
        $this->_canUseDictionary = $flag;
        return $this;
    }

    //########################################

    /**
     * @return bool
     */
    public function isAmountEqual()
    {
        return count($this->getSourceAttributes()) == count($this->getDestinationAttributes());
    }

    /**
     * @return bool
     */
    public function isSourceAmountGreater()
    {
        return count($this->getSourceAttributes()) > count($this->getDestinationAttributes());
    }

    /**
     * @return bool
     */
    public function isDestinationAmountGreater()
    {
        return count($this->getSourceAttributes()) < count($this->getDestinationAttributes());
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getMatchedAttributes()
    {
        if ($this->_matchedAttributes === null) {
            $this->match();
        }

        return $this->_matchedAttributes;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isFullyMatched()
    {
        return empty($this->getMagentoUnmatchedAttributes()) && empty($this->getChannelUnmatchedAttributes());
    }

    /**
     * @return bool
     */
    public function isNotMatched()
    {
        return empty($this->getMatchedAttributes());
    }

    /**
     * @return bool
     */
    public function isPartiallyMatched()
    {
        return !$this->isFullyMatched() && !$this->isNotMatched();
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getMagentoUnmatchedAttributes()
    {
        return array_keys($this->getMatchedAttributes(), null);
    }

    /**
     * @return array
     */
    public function getChannelUnmatchedAttributes()
    {
        $matchedChannelAttributes = array_values($this->getMatchedAttributes());
        return array_diff($this->_destinationAttributes, $matchedChannelAttributes);
    }

    //########################################

    protected function match()
    {
        if ($this->_magentoProduct !== null && $this->_magentoProduct->isGroupedType() &&
            !$this->_magentoProduct->getVariationVirtualAttributes()
        ) {
            $channelAttribute = reset($this->_destinationAttributes);

            $this->_matchedAttributes = array(
                Ess_M2ePro_Model_Magento_Product_Variation::GROUPED_PRODUCT_ATTRIBUTE_LABEL => $channelAttribute
            );

            return;
        }

        if ($this->matchByNames()) {
            return;
        }

        if (!$this->_canUseDictionary) {
            return;
        }

        if ($this->matchByLocalVocabulary()) {
            return;
        }

        $this->matchByServerVocabulary();
    }

    protected function matchByNames()
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

        $this->_matchedAttributes = $this->getResolver()->resolve()->getResolvedAttributes();

        return $this->isFullyMatched();
    }

    protected function matchByLocalVocabulary()
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

        $this->_matchedAttributes = $this->getResolver()->resolve()->getResolvedAttributes();

        return $this->isFullyMatched();
    }

    protected function matchByServerVocabulary()
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

        $this->_matchedAttributes = $this->getResolver()->resolve()->getResolvedAttributes();

        return $this->isFullyMatched();
    }

    //########################################

    protected function getSourceAttributes()
    {
        if (!empty($this->_sourceAttributes)) {
            return $this->_sourceAttributes;
        }

        if ($this->_magentoProduct !== null) {
            $magentoVariations = $this->_magentoProduct
                ->getVariationInstance()
                ->getVariationsTypeStandard();

            $this->_sourceAttributes = array_keys($magentoVariations['set']);
        }

        return $this->_sourceAttributes;
    }

    protected function getSourceAttributesData()
    {
        if ($this->_magentoProduct !== null) {
            $magentoAttributesNames = $this->_magentoProduct
                ->getVariationInstance()
                ->getTitlesVariationSet();

            $magentoStandardVariations = $this->_magentoProduct
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

    protected function getDestinationAttributes()
    {
        return $this->_destinationAttributes;
    }

    protected function getDestinationAttributesLocalVocabularyData()
    {
        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        $resultData = array();
        foreach ($this->getDestinationAttributes() as $attribute) {
            $resultData[$attribute] = $vocabularyHelper->getLocalAttributeNames($attribute);
        }

        return $resultData;
    }

    protected function getDestinationAttributesServerVocabularyData()
    {
        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        $resultData = array();
        foreach ($this->getDestinationAttributes() as $attribute) {
            $resultData[$attribute] = $vocabularyHelper->getServerAttributeNames($attribute);
        }

        return $resultData;
    }

    // ---------------------------------------

    protected function getResolver()
    {
        if ($this->_resolver !== null) {
            return $this->_resolver;
        }

        $this->_resolver = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute_Resolver');
        return $this->_resolver;
    }

    protected function prepareAttributeNames($attribute, array $names = array())
    {
        $names[] = $attribute;
        $names = array_unique($names);

        $names = array_map('trim', $names);
        $names = array_map('strtolower', $names);

        return $names;
    }

    //########################################
}
