<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_Product_PriceCalculator
{
    /**
     * @var null|array
     */
    private $source = NULL;

    /**
     * @var null|Ess_M2ePro_Model_Listing_Product
     */
    private $product = NULL;

    /**
     * @var bool
     */
    private $modifyByCoefficient = false;

    /**
     * @var null|int
     */
    private $priceVariationMode = NULL;

    /**
     * @var null|float
     */
    private $productValueCache = NULL;

    //########################################

    /**
     * @param array $source
     * @return Ess_M2ePro_Model_Listing_Product_PriceCalculator
     */
    public function setSource(array $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param null|string $key
     * @return array|mixed
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getSource($key = NULL)
    {
        if (empty($this->source)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Initialize all parameters first.');
        }

        return (!is_null($key) && isset($this->source[$key])) ?
                $this->source[$key] : $this->source;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $product
     * @return Ess_M2ePro_Model_Listing_Product_PriceCalculator
     */
    public function setProduct(Ess_M2ePro_Model_Listing_Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getProduct()
    {
        if (is_null($this->product)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Initialize all parameters first.');
        }

        return $this->product;
    }

    // ---------------------------------------

    /**
     * @param bool $value
     * @return Ess_M2ePro_Model_Listing_Product_PriceCalculator
     */
    public function setModifyByCoefficient($value)
    {
        $this->modifyByCoefficient = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    protected function isModifyByCoefficient()
    {
        return $this->modifyByCoefficient;
    }

    // ---------------------------------------

    /**
     * @param $mode
     * @return Ess_M2ePro_Model_Listing_Product_PriceCalculator
     */
    public function setPriceVariationMode($mode)
    {
        $this->priceVariationMode = $mode;
        return $this;
    }

    /**
     * @return int|null
     */
    protected function getPriceVariationMode()
    {
        return $this->priceVariationMode;
    }

    /**
     * @return bool
     */
    abstract protected function isPriceVariationModeParent();

    /**
     * @return bool
     */
    abstract protected function isPriceVariationModeChildren();

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    protected function getListing()
    {
        return $this->getProduct()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Component_Child_Abstract
     */
    protected function getComponentListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    protected function getSellingFormatTemplate()
    {
        return $this->getComponentProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Component_Child_Abstract
     */
    protected function getComponentSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Component_Child_Abstract
     */
    protected function getComponentProduct()
    {
        return $this->getProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected function getMagentoProduct()
    {
        return $this->getProduct()->getMagentoProduct();
    }

    //########################################

    public function getProductValue()
    {
        if ($this->getSource('mode') == Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE) {
            return 0;
        }

        $value = $this->getProductBaseValue();
        return $this->prepareFinalValue($value);
    }

    public function getVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($this->getSource('mode') == Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE) {
            return 0;
        }

        $value = $this->getVariationBaseValue($variation);
        return $this->prepareFinalValue($value);
    }

    //########################################

    protected function getProductBaseValue()
    {
        if (!is_null($this->productValueCache)) {
            return $this->productValueCache;
        }

        switch ($this->getSource('mode')) {

            case Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT:

                if ($this->getMagentoProduct()->isGroupedType()) {

                    $value = $this->getGroupedProductValue($this->getMagentoProduct());

                } else if ($this->getMagentoProduct()->isBundleType() &&
                           $this->getMagentoProduct()->isBundlePriceTypeDynamic()) {

                    $value = $this->getBundleProductDynamicValue($this->getMagentoProduct());

                } else {
                    $value = $this->getExistedProductValue($this->getMagentoProduct());
                }

                break;

            case Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL:

                if ($this->getMagentoProduct()->isGroupedType()) {

                    $value = $this->getGroupedProductValue($this->getMagentoProduct());

                } else if ($this->getMagentoProduct()->isBundleType() &&
                           $this->getMagentoProduct()->isBundlePriceTypeDynamic()) {

                    $value = $this->getBundleProductDynamicSpecialValue($this->getMagentoProduct());

                } else {
                    $value = $this->getExistedProductSpecialValue($this->getMagentoProduct());
                }

                break;

            case Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE:

                if ($this->getMagentoProduct()->isGroupedType()) {

                    if ($this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::PRICE_CODE ||
                        $this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::SPECIAL_PRICE_CODE) {
                        $value = $this->getGroupedProductValue($this->getMagentoProduct());
                    } else {
                        $value = $this->getMagentoProduct()->getAttributeValue($this->getSource('attribute'));
                    }

                } else if ($this->getMagentoProduct()->isBundleType() &&
                           $this->getMagentoProduct()->isBundlePriceTypeDynamic()) {

                    if ($this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::PRICE_CODE) {
                        $value = $this->getBundleProductDynamicValue($this->getMagentoProduct());
                    } else if ($this->getSource('attribute') ==
                                Ess_M2ePro_Helper_Magento_Attribute::SPECIAL_PRICE_CODE) {
                        $value = $this->getBundleProductDynamicSpecialValue($this->getMagentoProduct());
                    } else {
                        $value = $this->getMagentoProduct()->getAttributeValue($this->getSource('attribute'));
                    }

                } else {
                    $value = $this->getMagentoProduct()->getAttributeValue($this->getSource('attribute'));
                }

                break;

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown Mode in Database.');
        }

        $value < 0 && $value = 0;

        return $this->productValueCache = $value;
    }

    protected function getVariationBaseValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($this->getMagentoProduct()->isConfigurableType()) {
            $value = $this->getConfigurableVariationValue($variation);
        } else if ($this->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            $value = $this->getSimpleWithCustomOptionsVariationValue($variation);
        } else if ($this->getMagentoProduct()->isBundleType()) {
            $value = $this->getBundleVariationValue($variation);
        } else if ($this->getMagentoProduct()->isGroupedType()) {
            $value = $this->getGroupedVariationValue($variation);
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic('Unknown Product type.');
        }

        $value < 0 && $value = 0;

        return $value;
    }

    protected function getOptionBaseValue(Ess_M2ePro_Model_Listing_Product_Variation_Option $option)
    {
        switch ($this->getSource('mode')) {

            case Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT:
                $value = $this->getExistedProductValue($option->getMagentoProduct());
                break;

            case Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL:
                $value = $this->getExistedProductSpecialValue($option->getMagentoProduct());
                break;

            case Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE:
                $value = $option->getMagentoProduct()->getAttributeValue($this->getSource('attribute'));
                break;

            default:
                throw new Ess_M2ePro_Model_Exception_Logic('Unknown Mode in Database.');
        }

        $value < 0 && $value = 0;

        return $value;
    }

    //########################################

    protected function getConfigurableVariationValue(
        Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($this->isPriceVariationModeChildren()) {
            $options = $variation->getOptions(true);
            return $this->getOptionBaseValue(reset($options));
        }

        $value = $this->getProductBaseValue();
        return $this->applyAdditionalOptionValuesModifications($variation, $value);
    }

    protected function getSimpleWithCustomOptionsVariationValue(
        Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        $value = $this->getProductBaseValue();
        return $this->applyAdditionalOptionValuesModifications($variation, $value);
    }

    protected function getBundleVariationValue(
        Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($this->isPriceVariationModeChildren()) {

            $value = 0;

            foreach ($variation->getOptions(true) as $option) {
                if (!$option->getProductId()) {
                    continue;
                }

                $value += $this->getOptionBaseValue($option);
            }

            return $value;
        }

        if ($this->getMagentoProduct()->isBundlePriceTypeFixed() ||
            ($this->getSource('mode') == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE &&
             $this->getSource('attribute') != Ess_M2ePro_Helper_Magento_Attribute::PRICE_CODE &&
             $this->getSource('attribute') != Ess_M2ePro_Helper_Magento_Attribute::SPECIAL_PRICE_CODE)) {

            $value = $this->getProductBaseValue();

        } else {

            $value = 0;

            foreach ($variation->getOptions(true) as $option) {
                if (!$option->getProductId()) {
                    continue;
                }

                $tempValue = (float)$option->getMagentoProduct()->getSpecialPrice();
                $tempValue <= 0 && $tempValue = (float)$option->getMagentoProduct()->getPrice();

                $value += $tempValue;
            }

            if ($this->getSource('mode') == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL &&
                $value > 0 && $this->getMagentoProduct()->isSpecialPriceActual()) {

                $percent = (double)$this->getMagentoProduct()->getProduct()->getSpecialPrice();
                $value = round((($value * $percent) / 100), 2);
            }

            if ($this->getSource('mode') != Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
                $value = $this->convertValueFromStoreToMarketplace($value);
            }
        }

        return $this->applyAdditionalOptionValuesModifications($variation, $value);
    }

    protected function getGroupedVariationValue(
        Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        $options = $variation->getOptions(true);
        return $this->getOptionBaseValue(reset($options));
    }

    //########################################

    protected function applyAdditionalOptionValuesModifications(
        Ess_M2ePro_Model_Listing_Product_Variation $variation, $value)
    {
        foreach ($variation->getOptions(true) as $option) {

            if ($this->getMagentoProduct()->isConfigurableType()) {
                $value += $this->getConfigurableAdditionalOptionValue($option);
            } else if ($this->getMagentoProduct()->isSimpleType()) {
                $value += $this->getSimpleWithCustomOptionsAdditionalOptionValue($option);
            } else if ($this->getMagentoProduct()->isBundleType() && $option->getProductId()) {
                $value += $this->getBundleAdditionalOptionValue($option);
            }
        }

        return $value;
    }

    // ---------------------------------------

    protected function getConfigurableAdditionalOptionValue(
        Ess_M2ePro_Model_Listing_Product_Variation_Option $option)
    {
        $value = 0;

        $attributeName = strtolower($option->getAttribute());
        $optionName = strtolower($option->getOption());

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();

        foreach ($productTypeInstance->getConfigurableAttributes() as $configurableAttribute) {

            /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            $configurableAttribute->setStoteId($this->getMagentoProduct()->getStoreId());

            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attribute = $configurableAttribute->getProductAttribute();
            $attribute->setStoreId($this->getMagentoProduct()->getStoreId());

            $tempAttributeNames = array_values($attribute->getStoreLabels());
            $tempAttributeNames[] = $configurableAttribute->getData('label');
            $tempAttributeNames[] = $attribute->getFrontendLabel();

            if (!in_array($attributeName,array_map('strtolower',array_filter($tempAttributeNames)))) {
                continue;
            }

            $childOptions = $attribute->getSource()->getAllOptions(false);

            foreach ((array)$configurableAttribute->getPrices() as $configurableOption) {

                $tempOptionNames = array();

                isset($configurableOption['label']) &&
                $tempOptionNames[] = $configurableOption['label'];
                isset($configurableOption['default_label']) &&
                $tempOptionNames[] = $configurableOption['default_label'];
                isset($configurableOption['store_label']) &&
                $tempOptionNames[] = $configurableOption['store_label'];

                foreach ($childOptions as $childOption) {
                    if ((int)$childOption['value'] == (int)$configurableOption['value_index']) {
                        $tempOptionNames[] = $childOption['label'];
                        break;
                    }
                }

                $tempOptionNames = array_map('strtolower', array_filter($tempOptionNames));
                $tempOptionNames = $this->prepareOptionTitles($tempOptionNames);

                if (!in_array($optionName, $tempOptionNames)) {
                    continue;
                }

                if ((bool)(int)$configurableOption['is_percent']) {
                    $value = ($this->getProductBaseValue() * (float)$configurableOption['pricing_value']) / 100;
                } else {
                    $value = (float)$configurableOption['pricing_value'];
                    $value = $this->convertValueFromStoreToMarketplace($value);
                }

                break 2;
            }
        }

        return $value;
    }

    protected function getSimpleWithCustomOptionsAdditionalOptionValue(
        Ess_M2ePro_Model_Listing_Product_Variation_Option $option)
    {
        $value = 0;

        $attributeName = strtolower($option->getAttribute());
        $optionName = strtolower($option->getOption());

        $simpleAttributes = $this->getMagentoProduct()->getProduct()->getOptions();

        foreach ($simpleAttributes as $tempAttribute) {

            if (!(bool)(int)$tempAttribute->getData('is_require')) {
                continue;
            }

            if (!in_array($tempAttribute->getType(), array('drop_down', 'radio', 'multiple', 'checkbox'))) {
                continue;
            }

            $tempAttributeTitles = array(
                $tempAttribute->getData('default_title'),
                $tempAttribute->getData('store_title'),
                $tempAttribute->getData('title')
            );

            $tempAttributeTitles = array_map('strtolower', array_filter($tempAttributeTitles));

            if (!in_array($attributeName, $tempAttributeTitles)) {
                continue;
            }

            foreach ($tempAttribute->getValues() as $tempOption) {

                $tempOptionTitles = array(
                    $tempOption->getData('default_title'),
                    $tempOption->getData('store_title'),
                    $tempOption->getData('title')
                );

                $tempOptionTitles = array_map('strtolower', array_filter($tempOptionTitles));
                $tempOptionTitles = $this->prepareOptionTitles($tempOptionTitles);

                if (!in_array($optionName, $tempOptionTitles)) {
                    continue;
                }

                if (!is_null($tempOption->getData('price_type')) &&
                    $tempOption->getData('price_type') !== false) {

                    switch ($tempOption->getData('price_type')) {
                        case 'percent':
                            $value = ($this->getProductBaseValue() * (float)$tempOption->getData('price')) / 100;
                            break;
                        case 'fixed':
                            $value = (float)$tempOption->getData('price');
                            $value = $this->convertValueFromStoreToMarketplace($value);
                            break;
                    }
                }

                break 2;
            }
        }

        return $value;
    }

    protected function getBundleAdditionalOptionValue(
        Ess_M2ePro_Model_Listing_Product_Variation_Option $option)
    {
        $value = 0;

        if ($this->getMagentoProduct()->isBundlePriceTypeDynamic()) {
            return $value;
        }

        $product = $this->getMagentoProduct()->getProduct();
        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();
        $bundleAttributes = $productTypeInstance->getOptionsCollection();

        $attributeName = strtolower($option->getAttribute());

        foreach ($bundleAttributes as $tempAttribute) {

            if (!(bool)(int)$tempAttribute->getData('required')) {
                continue;
            }

            if ((is_null($tempAttribute->getData('title')) ||
                    strtolower($tempAttribute->getData('title')) != $attributeName) &&
                (is_null($tempAttribute->getData('default_title')) ||
                    strtolower($tempAttribute->getData('default_title')) != $attributeName)) {
                continue;
            }

            $tempOptions = $productTypeInstance
                ->getSelectionsCollection(array(0 => $tempAttribute->getId()), $product)
                ->getItems();

            foreach ($tempOptions as $tempOption) {

                if ((int)$tempOption->getId() != $option->getProductId()) {
                    continue;
                }

                if ((bool)(int)$tempOption->getData('selection_price_type')) {
                    $value = ($this->getProductBaseValue() * (float)$tempOption->getData('selection_price_value'))/100;
                } else {

                    $value = (float)$tempOption->getData('selection_price_value');

                    if ($this->getSource('mode') == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL &&
                        $this->getMagentoProduct()->isSpecialPriceActual()) {
                            $value = ($value * $product->getSpecialPrice()) / 100;
                    }

                    $value = $this->convertValueFromStoreToMarketplace($value);
                }

                break 2;
            }
        }

        return $value;
    }

    //########################################

    protected function getExistedProductValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        $value = $product->getPrice();
        return $this->convertValueFromStoreToMarketplace($value);
    }

    protected function getExistedProductSpecialValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        $value = (float)$product->getSpecialPrice();

        if ($value <= 0) {
            return $this->getExistedProductValue($product);
        }

        return $this->convertValueFromStoreToMarketplace($value);
    }

    // ---------------------------------------

    protected function getGroupedProductValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        $value = 0;

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Grouped */
        $productTypeInstance = $product->getTypeInstance();

        foreach ($productTypeInstance->getAssociatedProducts() as $childProduct) {

            /** @var $childProduct Ess_M2ePro_Model_Magento_Product */
            $childProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($childProduct);

            $variationValue = (float)$childProduct->getSpecialPrice();
            $variationValue <= 0 && $variationValue = (float)$childProduct->getPrice();

            if ($variationValue < $value || $value == 0) {
                $value = $variationValue;
            }
        }

        return $this->convertValueFromStoreToMarketplace($value);
    }

    protected function getBundleProductDynamicValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        $value = 0;

        $variationsData = $product->getVariationInstance()->getVariationsTypeStandard();

        foreach ($variationsData['variations'] as $variation) {

            $variationValue = 0;

            foreach ($variation as $option) {

                /** @var $childProduct Ess_M2ePro_Model_Magento_Product */
                $childProduct = Mage::getModel('M2ePro/Magento_Product')->setProductId($option['product_id']);

                $optionValue = (float)$childProduct->getSpecialPrice();
                $optionValue <= 0 && $optionValue = (float)$childProduct->getPrice();

                $variationValue += $optionValue;
            }

            if ($variationValue < $value || $value == 0) {
                $value = $variationValue;
            }
        }

        return $this->convertValueFromStoreToMarketplace($value);
    }

    protected function getBundleProductDynamicSpecialValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        $value = $this->getBundleProductDynamicValue($product);

        if ($value <= 0 || !$product->isSpecialPriceActual()) {
            return $value;
        }

        $percent = (double)$product->getProduct()->getSpecialPrice();
        return round((($value * $percent) / 100), 2);
    }

    //########################################

    protected function prepareFinalValue($value)
    {
        if ($this->isModifyByCoefficient()) {
            $value = $this->modifyValueByCoefficient($value);
        }

        $value < 0 && $value = 0;

        return round($value, 2);
    }

    protected function modifyValueByCoefficient($value)
    {
        if ($value <= 0) {
            return $value;
        }

        $coefficient = $this->getSource('coefficient');

        if (is_string($coefficient)) {
            $coefficient = trim($coefficient);
        }

        if (!$coefficient) {
            return $value;
        }

        if (strpos($coefficient, '%')) {

            $coefficient = str_replace('%', '', $coefficient);

            if (preg_match('/^[+-]/', $coefficient)) {
                return $value + $value * (float)$coefficient / 100;
            }

            return $value * (float)$coefficient / 100;
        }

        if (preg_match('/^[+-]/', $coefficient)) {
            return $value + (float)$coefficient;
        }

        return $value * (float)$coefficient;
    }

    protected function convertValueFromStoreToMarketplace($value)
    {
        return $this->getComponentListing()->convertPriceFromStoreToMarketplace($value);
    }

    // ---------------------------------------

    protected function prepareOptionTitles($optionTitles)
    {
        return $optionTitles;
    }

    //########################################
}