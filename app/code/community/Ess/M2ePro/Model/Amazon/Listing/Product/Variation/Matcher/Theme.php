<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Theme
{
    /** @var Ess_M2ePro_Model_Magento_Product $_magentoProduct */
    protected $_magentoProduct = null;

    protected $_sourceAttributes = array();

    protected $_themes = array();

    protected $_matchedTheme = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $product
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $product)
    {
        $this->_magentoProduct   = $product;
        $this->_sourceAttributes = array();

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

        return $this;
    }

    // ---------------------------------------

    /**
     * @param array $themes
     * @return $this
     */
    public function setThemes(array $themes)
    {
        $this->_themes = $themes;
        return $this;
    }

    //########################################

    /**
     * @return mixed
     */
    public function getMatchedTheme()
    {
        if ($this->_matchedTheme === null) {
            $this->match();
        }

        return $this->_matchedTheme;
    }

    //########################################

    protected function match()
    {
        $this->validate();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute $attributeMatcher */
        $attributeMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute');

        if ($this->_magentoProduct !== null) {
            if ($this->_magentoProduct->isGroupedType()) {
                $this->_matchedTheme = null;
                return $this;
            }

            $attributeMatcher->setMagentoProduct($this->_magentoProduct);
        }

        if (!empty($this->_sourceAttributes)) {
            $attributeMatcher->setSourceAttributes($this->_sourceAttributes);
            $attributeMatcher->canUseDictionary(false);
        }

        foreach ($this->_themes as $themeName => $themeAttributes) {
            $attributeMatcher->setDestinationAttributes($themeAttributes['attributes']);

            if ($attributeMatcher->isAmountEqual() && $attributeMatcher->isFullyMatched()) {
                $this->_matchedTheme = $themeName;
                break;
            }
        }

        return $this;
    }

    protected function validate()
    {
        if ($this->_magentoProduct === null && empty($this->_sourceAttributes)) {
            throw new Ess_M2ePro_Model_Exception('Magento Product and Channel Attributes were not set.');
        }
    }

    //########################################
}
