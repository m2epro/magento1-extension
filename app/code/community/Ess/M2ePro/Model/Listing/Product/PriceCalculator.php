<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_Product_PriceCalculator
{
    const MODE_NONE      = 0;
    const MODE_PRODUCT   = 1;
    const MODE_SPECIAL   = 2;
    const MODE_ATTRIBUTE = 3;
    const MODE_TIER      = 4;

    /**
     * @var null|array
     */
    protected $_source = null;

    /**
     * @var array
     */
    protected $_sourceModeMapping = array(
        self::MODE_NONE      => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE,
        self::MODE_PRODUCT   => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT,
        self::MODE_SPECIAL   => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL,
        self::MODE_ATTRIBUTE => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE,
        self::MODE_TIER      => Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_TIER,
    );

    /**
     * @var null|Ess_M2ePro_Model_Listing_Product
     */
    protected $_product = null;

    /**
     * @var null|string
     */
    protected $_coefficient = null;

    /**
     * @var null|float
     */
    protected $_vatPercent = null;

    /**
     * @var null|int
     */
    protected $_priceVariationMode = null;

    /**
     * @var null|float
     */
    protected $_productValueCache = null;

    //########################################

    /**
     * @param array $source
     * @return Ess_M2ePro_Model_Listing_Product_PriceCalculator
     */
    public function setSource(array $source)
    {
        $this->_source = $source;
        return $this;
    }

    /**
     * @param null|string $key
     * @return array|mixed
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getSource($key = null)
    {
        if (empty($this->_source)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Initialize all parameters first.');
        }

        if ($key === null) {
            return $this->_source;
        }

        return isset($this->_source[$key]) ? $this->_source[$key] : null;
    }

    // ---------------------------------------

    public function setSourceModeMapping(array $mapping)
    {
        $this->_sourceModeMapping = $mapping;
        return $this;
    }

    protected function getSourceMode()
    {
        if (!in_array($this->getSource('mode'), $this->_sourceModeMapping)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Unknown source mode.');
        }

        return array_search($this->getSource('mode'), $this->_sourceModeMapping);
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $product
     * @return Ess_M2ePro_Model_Listing_Product_PriceCalculator
     */
    public function setProduct(Ess_M2ePro_Model_Listing_Product $product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getProduct()
    {
        if ($this->_product === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Initialize all parameters first.');
        }

        return $this->_product;
    }

    // ---------------------------------------

    /**
     * @param string $value
     * @return Ess_M2ePro_Model_Listing_Product_PriceCalculator
     */
    public function setCoefficient($value)
    {
        $this->_coefficient = $value;
        return $this;
    }

    /**
     * @return string
     */
    protected function getCoefficient()
    {
        return $this->_coefficient;
    }

    // ---------------------------------------

    public function setVatPercent($value)
    {
        $this->_vatPercent = $value;
        return $this;
    }

    /**
     * @return float|null
     */
    protected function getVatPercent()
    {
        return $this->_vatPercent;
    }

    // ---------------------------------------

    /**
     * @param $mode
     * @return Ess_M2ePro_Model_Listing_Product_PriceCalculator
     */
    public function setPriceVariationMode($mode)
    {
        $this->_priceVariationMode = $mode;
        return $this;
    }

    /**
     * @return int|null
     */
    protected function getPriceVariationMode()
    {
        return $this->_priceVariationMode;
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
        if ($this->isSourceModeNone()) {
            return 0;
        }

        $value = $this->getProductBaseValue();
        return $this->prepareFinalValue($value);
    }

    public function getVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($this->isSourceModeNone()) {
            return 0;
        }

        $value = $this->getVariationBaseValue($variation);
        return $this->prepareFinalValue($value);
    }

    //########################################

    protected function getProductBaseValue()
    {
        if ($this->_productValueCache !== null) {
            return $this->_productValueCache;
        }

        if ($this->isSourceModeProduct()) {
            if ($this->getMagentoProduct()->isGroupedType()) {
                $value = $this->getGroupedProductValue($this->getMagentoProduct());
            } else if ($this->getMagentoProduct()->isBundleType() &&
                $this->getMagentoProduct()->isBundlePriceTypeDynamic()) {
                $value = $this->getBundleProductDynamicValue($this->getMagentoProduct());
            } else {
                $value = $this->getExistedProductValue($this->getMagentoProduct());
            }
        } elseif ($this->isSourceModeSpecial()) {
            if ($this->getMagentoProduct()->isGroupedType()) {
                $value = $this->getGroupedProductValue($this->getMagentoProduct());
            } else if ($this->getMagentoProduct()->isBundleType() &&
                $this->getMagentoProduct()->isBundlePriceTypeDynamic()) {
                $value = $this->getBundleProductDynamicSpecialValue($this->getMagentoProduct());
            } else {
                $value = $this->getExistedProductSpecialValue($this->getMagentoProduct());
            }
        } elseif ($this->isSourceModeAttribute()) {
            if ($this->getMagentoProduct()->isGroupedType()) {
                if ($this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::PRICE_CODE ||
                    $this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::SPECIAL_PRICE_CODE) {
                    $value = $this->getGroupedProductValue($this->getMagentoProduct());
                } else {
                    $value = Mage::helper('M2ePro/Magento_Attribute')->convertAttributeTypePriceFromStoreToMarketplace(
                        $this->getMagentoProduct(),
                        $this->getSource('attribute'),
                        $this->getCurrencyForPriceConvert(),
                        $this->getListing()->getStoreId()
                    );
                }
            } else if ($this->getMagentoProduct()->isBundleType() &&
                $this->getMagentoProduct()->isBundlePriceTypeDynamic()) {
                if ($this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::PRICE_CODE) {
                    $value = $this->getBundleProductDynamicValue($this->getMagentoProduct());
                } else if ($this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::SPECIAL_PRICE_CODE) {
                    $value = $this->getBundleProductDynamicSpecialValue($this->getMagentoProduct());
                } else {
                    $value = Mage::helper('M2ePro/Magento_Attribute')->convertAttributeTypePriceFromStoreToMarketplace(
                        $this->getMagentoProduct(),
                        $this->getSource('attribute'),
                        $this->getCurrencyForPriceConvert(),
                        $this->getListing()->getStoreId()
                    );
                }
            } else {
                $value = Mage::helper('M2ePro/Magento_Attribute')->convertAttributeTypePriceFromStoreToMarketplace(
                    $this->getMagentoProduct(),
                    $this->getSource('attribute'),
                    $this->getCurrencyForPriceConvert(),
                    $this->getListing()->getStoreId()
                );
            }
        } elseif ($this->isSourceModeTier()) {
            if ($this->getMagentoProduct()->isGroupedType()) {
                $value = $this->getGroupedTierValue($this->getMagentoProduct());
            } else if ($this->getMagentoProduct()->isBundleType()) {
                if ($this->getMagentoProduct()->isBundlePriceTypeDynamic()) {
                    $value = $this->getBundleTierDynamicValue($this->getMagentoProduct());
                } else {
                    $value = $this->getBundleTierFixedValue($this->getMagentoProduct());
                }
            } else {
                $value = $this->getExistedProductTierValue($this->getMagentoProduct());
            }
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic('Unknown Mode in Database.');
        }

        return $this->_productValueCache = !is_array($value) ? (float)$value : $value;
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
        } else if ($this->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks()) {
            $value = $this->getDownloadableWithSeparatedLinksVariationValue($variation);
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Unknown Product type.',
                array(
                                                           'listing_product_id' => $this->getProduct()->getId(),
                                                           'product_id' => $this->getMagentoProduct()->getProductId(),
                                                           'type'       => $this->getMagentoProduct()->getTypeId()
                )
            );
        }

        return !is_array($value) ? (float)$value : $value;
    }

    protected function getOptionBaseValue(Ess_M2ePro_Model_Listing_Product_Variation_Option $option)
    {
        if ($this->isSourceModeProduct()) {
            $value = $this->getExistedProductValue($option->getMagentoProduct());
        } elseif ($this->isSourceModeSpecial()) {
            $value = $this->getExistedProductSpecialValue($option->getMagentoProduct());
        } elseif ($this->isSourceModeAttribute()) {
            $value = Mage::helper('M2ePro/Magento_Attribute')->convertAttributeTypePriceFromStoreToMarketplace(
                $option->getMagentoProduct(),
                $this->getSource('attribute'),
                $this->getCurrencyForPriceConvert(),
                $this->getListing()->getStoreId()
            );
        } elseif ($this->isSourceModeTier()) {
            $value = $this->getExistedProductTierValue($option->getMagentoProduct());
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic('Unknown Mode in Database.');
        }

        return !is_array($value) ? (float)$value : $value;
    }

    //########################################

    protected function getConfigurableVariationValue(
        Ess_M2ePro_Model_Listing_Product_Variation $variation
    ) {
        if ($this->isPriceVariationModeChildren()) {
            $options = $variation->getOptions(true);
            return $this->getOptionBaseValue(reset($options));
        }

        $value = $this->getProductBaseValue();
        return $this->applyAdditionalOptionValuesModifications($variation, $value);
    }

    protected function getSimpleWithCustomOptionsVariationValue(
        Ess_M2ePro_Model_Listing_Product_Variation $variation
    ) {
        $value = $this->getProductBaseValue();
        return $this->applyAdditionalOptionValuesModifications($variation, $value);
    }

    protected function getBundleVariationValue(
        Ess_M2ePro_Model_Listing_Product_Variation $variation
    ) {
        if ($this->isPriceVariationModeChildren()) {
            $value = 0;

            foreach ($variation->getOptions(true) as $option) {
                if (!$option->getProductId()) {
                    continue;
                }

                if ($this->isSourceModeTier()) {
                    $value += $this->getExistedProductValue($option->getMagentoProduct());
                } else {
                    $value += $this->getOptionBaseValue($option);
                }
            }

            if ($this->isSourceModeTier()) {
                return $this->calculateBundleTierValue($this->getMagentoProduct(), $value);
            }

            return $value;
        }

        if ($this->getMagentoProduct()->isBundlePriceTypeFixed() ||
            ($this->isSourceModeAttribute() &&
                $this->getSource('attribute') != Ess_M2ePro_Helper_Magento_Attribute::PRICE_CODE)) {
            $value = $this->getProductBaseValue();

            if ($this->isSourceModeTier()) {
                return $this->applyAdditionalOptionValuesModifications($variation, $value);
            }
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

            if ($this->isSourceModeSpecial() && $this->getMagentoProduct()->isSpecialPriceActual()) {
                $percent = (double)$this->getMagentoProduct()->getProduct()->getSpecialPrice();
                $value = round((($value * $percent) / 100), 2);
            }

            if ($this->isSourceModeAttribute()) {
                if (Mage::helper('M2ePro/Module_Configuration')->isEnableMagentoAttributePriceTypeConvertingMode() &&
                    $this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::PRICE_CODE
                ) {
                    $value = $this->convertValueFromStoreToMarketplace($value);
                }
            } else {
                $value = $this->convertValueFromStoreToMarketplace($value);
            }
        }

        if ($this->isSourceModeTier()) {
            $value = $this->calculateBundleTierValue($this->getMagentoProduct(), $value);
        }

        return $this->applyAdditionalOptionValuesModifications($variation, $value);
    }

    protected function getGroupedVariationValue(
        Ess_M2ePro_Model_Listing_Product_Variation $variation
    ) {
        $options = $variation->getOptions(true);
        return $this->getOptionBaseValue(reset($options));
    }

    protected function getDownloadableWithSeparatedLinksVariationValue(
        Ess_M2ePro_Model_Listing_Product_Variation $variation
    ) {
        $value = $this->getProductBaseValue();
        return $this->applyAdditionalOptionValuesModifications($variation, $value);
    }

    //########################################

    protected function applyAdditionalOptionValuesModifications(
        Ess_M2ePro_Model_Listing_Product_Variation $variation,
        $value
    ) {
        foreach ($variation->getOptions(true) as $option) {
            $additionalValue = 0;

            if ($this->getMagentoProduct()->isConfigurableType()) {
                $additionalValue = $this->getConfigurableAdditionalOptionValue($option);
            } else if ($this->getMagentoProduct()->isSimpleType()) {
                $additionalValue = $this->getSimpleWithCustomOptionsAdditionalOptionValue($option);
            } else if ($this->getMagentoProduct()->isBundleType() && $option->getProductId()) {
                $additionalValue = $this->getBundleAdditionalOptionValue($option);
            } else if ($this->getMagentoProduct()->isDownloadableType()) {
                $additionalValue = $this->getDownloadableWithSeparatedLinksAdditionalOptionValue($option);
            }

            if (!$this->isSourceModeTier()) {
                $value += $additionalValue;
                continue;
            }

            foreach ($value as $key => &$item) {
                $item += is_array($additionalValue) ? $additionalValue[$key] : $additionalValue;
            }
        }

        return $value;
    }

    // ---------------------------------------

    protected function getConfigurableAdditionalOptionValue(
        Ess_M2ePro_Model_Listing_Product_Variation_Option $option
    ) {
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
            if (!$attribute) {
                $message = "Configurable Magento Product (ID {$this->getMagentoProduct()->getProductId()})";
                $message .= ' has no selected configurable attribute.';
                throw new \Ess_M2ePro_Model_Exception($message);
            }

            $attribute->setStoreId($this->getMagentoProduct()->getStoreId());

            $tempAttributeNames = array_values($attribute->getStoreLabels());
            $tempAttributeNames[] = $configurableAttribute->getData('label');
            $tempAttributeNames[] = $attribute->getFrontendLabel();

            $tempAttributeNames = array_map('strtolower', array_filter($tempAttributeNames));
            $tempAttributeNames = $this->prepareAttributeTitles($tempAttributeNames);

            if (!in_array($attributeName, $tempAttributeNames)) {
                continue;
            }

            $childOptions = $attribute->getSource()->getAllOptions(false);

            foreach ((array)$configurableAttribute->getPrices() as $configurableOption) {
                $tempOptionNames = array();

                isset($configurableOption['label']) && $tempOptionNames[] = $configurableOption['label'];
                isset($configurableOption['default_label'])
                    && $tempOptionNames[] = $configurableOption['default_label'];
                isset($configurableOption['store_label']) && $tempOptionNames[] = $configurableOption['store_label'];

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
                    if ($this->isSourceModeTier()) {
                        $value = $this->getProductBaseValue();
                        foreach ($value as &$item) {
                            $item = ($item * (float)$configurableOption['pricing_value']) / 100;
                        }
                    } else {
                        $value = ($this->getProductBaseValue() * (float)$configurableOption['pricing_value']) / 100;
                    }
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
        Ess_M2ePro_Model_Listing_Product_Variation_Option $option
    ) {
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
            $tempAttributeTitles = $this->prepareAttributeTitles($tempAttributeTitles);

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

                if ($tempOption->getData('price_type') !== null &&
                    $tempOption->getData('price_type') !== false) {
                    switch ($tempOption->getData('price_type')) {
                        case 'percent':

                            if ($this->isSourceModeTier()) {
                                $value = $this->getProductBaseValue();
                                foreach ($value as &$item) {
                                    $item = ($item * (float)$tempOption->getData('price')) / 100;
                                }
                            } else {
                                $value = ($this->getProductBaseValue() * (float)$tempOption->getData('price')) / 100;
                            }
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
        Ess_M2ePro_Model_Listing_Product_Variation_Option $option
    ) {
        $value = 0;

        if ($this->getMagentoProduct()->isBundlePriceTypeDynamic()) {
            return $value;
        }

        $magentoProduct = $this->getMagentoProduct();
        $product = $magentoProduct->getProduct();
        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();
        $bundleAttributes = $productTypeInstance->getOptionsCollection();

        $attributeName = strtolower($option->getAttribute());

        foreach ($bundleAttributes as $tempAttribute) {
            if (!(bool)(int)$tempAttribute->getData('required')) {
                continue;
            }

            $tempAttributeNames = array(
                $tempAttribute->getData('title'),
                $tempAttribute->getData('default_title')
            );

            $tempAttributeNames = array_map('strtolower', array_filter($tempAttributeNames));
            $tempAttributeNames = $this->prepareAttributeTitles($tempAttributeNames);

            if (!in_array($attributeName, $tempAttributeNames)) {
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
                    if ($this->isSourceModeTier()) {
                        $value = $this->getProductBaseValue();
                        foreach ($value as &$item) {
                            $item = ($item * (float)$tempOption->getData('selection_price_value'))/100;
                        }
                    } else {
                        $selectionPriceValue = (float)$tempOption->getData('selection_price_value');
                        $value = ($this->getProductBaseValue() * $selectionPriceValue)/100;
                    }
                } else {
                    $value = (float)$tempOption->getData('selection_price_value');

                    if ($this->isSourceModeSpecial() && $this->getMagentoProduct()->isSpecialPriceActual()) {
                        $value = ($value * $product->getSpecialPrice()) / 100;
                    }

                    if ($this->isSourceModeTier()) {
                        $value = $this->calculateBundleTierValue($magentoProduct, $value);

                        foreach ($value as &$item) {
                            $item = $this->convertValueFromStoreToMarketplace($item);
                        }
                    } else {
                        $value = $this->convertValueFromStoreToMarketplace($value);
                    }
                }

                break 2;
            }
        }

        return $value;
    }

    protected function getDownloadableWithSeparatedLinksAdditionalOptionValue(
        Ess_M2ePro_Model_Listing_Product_Variation_Option $option
    ) {
        $value = 0;

        $optionName = strtolower($option->getOption());

        /** @var Mage_Downloadable_Model_Link[] $links */
        $links = $this->getMagentoProduct()->getTypeInstance()->getLinks();

        foreach ($links as $link) {
            $tempLinkTitles = array(
                $link->getStoreTitle(),
                $link->getDefaultTitle(),
            );

            $tempLinkTitles = array_map('strtolower', array_filter($tempLinkTitles));
            $tempLinkTitles = $this->prepareOptionTitles($tempLinkTitles);

            if (!in_array($optionName, $tempLinkTitles)) {
                continue;
            }

            $value = (float)$link->getPrice();
            $value = $this->convertValueFromStoreToMarketplace($value);

            break;
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

    protected function getExistedProductTierValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        $tierPrice = $product->getTierPrice(
            $this->getSource('tier_website_id'), $this->getSource('tier_customer_group_id')
        );

        foreach ($tierPrice as $qty => $value) {
            $tierPrice[$qty] = $this->convertValueFromStoreToMarketplace($value);
        }

        return $tierPrice;
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

        if ($this->isSourceModeAttribute()) {
            if (Mage::helper('M2ePro/Module_Configuration')->isEnableMagentoAttributePriceTypeConvertingMode() &&
                ($this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::PRICE_CODE ||
                $this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::SPECIAL_PRICE_CODE)
            ) {
                return $this->convertValueFromStoreToMarketplace($value);
            }

            return $value;
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

        if ($this->isSourceModeAttribute()) {
            if (Mage::helper('M2ePro/Module_Configuration')->isEnableMagentoAttributePriceTypeConvertingMode() &&
                ($this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::PRICE_CODE ||
                 $this->getSource('attribute') == Ess_M2ePro_Helper_Magento_Attribute::SPECIAL_PRICE_CODE)
            ) {
                return $this->convertValueFromStoreToMarketplace($value);
            }

            return $value;
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

    protected function getGroupedTierValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Grouped */
        $productTypeInstance = $product->getTypeInstance();

        $lowestVariationValue = null;
        $resultChildProduct   = null;

        foreach ($productTypeInstance->getAssociatedProducts() as $childProduct) {

            /** @var $childProduct Ess_M2ePro_Model_Magento_Product */
            $childProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($childProduct);

            $variationValue = (float)$childProduct->getSpecialPrice();
            $variationValue <= 0 && $variationValue = (float)$childProduct->getPrice();

            if ($variationValue < $lowestVariationValue || $lowestVariationValue === null) {
                $lowestVariationValue = $variationValue;
                $resultChildProduct   = $childProduct;
            }
        }

        if ($resultChildProduct === null) {
            return null;
        }

        return $this->getExistedProductTierValue($resultChildProduct);
    }

    protected function getBundleTierFixedValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        return $this->calculateBundleTierValue($product, $this->getExistedProductValue($product));
    }

    protected function getBundleTierDynamicValue(Ess_M2ePro_Model_Magento_Product $product)
    {
        return $this->calculateBundleTierValue($product, $this->getBundleProductDynamicValue($product));
    }

    //########################################

    protected function prepareFinalValue($value)
    {
        if ($this->getCoefficient() !== null) {
            if (!$this->isSourceModeTier()) {
                $value = $this->modifyValueByCoefficient($value);
            } else {
                foreach ($value as $qty => $price) {
                    $value[$qty] = $this->modifyValueByCoefficient($price);
                }
            }
        }

        if ($this->getVatPercent() !== null) {
            if (!$this->isSourceModeTier()) {
                $value = $this->increaseValueByVatPercent($value);
            } else {
                foreach ($value as $qty => $price) {
                    $value[$qty] = $this->increaseValueByVatPercent($price);
                }
            }
        }

        if (!$this->isSourceModeTier()) {
            $value < 0 && $value = 0;
            $value = round($value, 2);
        } else {
            foreach ($value as $qty => $price) {
                $price < 0 && $value[$qty] = 0;
                $value[$qty] = round($value[$qty], 2);
            }
        }

        return $value;
    }

    // ---------------------------------------

    protected function modifyValueByCoefficient($value)
    {
        if ($value <= 0) {
            return $value;
        }

        $coefficient = $this->getCoefficient();

        if (is_string($coefficient)) {
            $coefficient = trim($coefficient);
        }

        if (!$coefficient) {
            return $value;
        }

        if (strpos($coefficient, '%') !== false) {
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

    protected function increaseValueByVatPercent($value)
    {
        return $value + (($this->getVatPercent()*$value) / 100);
    }

    // ---------------------------------------

    protected function convertValueFromStoreToMarketplace($value)
    {
        return Mage::getSingleton('M2ePro/Currency')->convertPrice(
            $value,
            $this->getCurrencyForPriceConvert(),
            $this->getListing()->getStoreId()
        );
    }

    abstract protected function getCurrencyForPriceConvert();

    // ---------------------------------------

    protected function calculateBundleTierValue(Ess_M2ePro_Model_Magento_Product $product, $baseValue)
    {
        $tierPrice = $product->getTierPrice(
            $this->getSource('tier_website_id'), $this->getSource('tier_customer_group_id')
        );

        $value = array();

        foreach ($tierPrice as $qty => $discount) {
            $value[$qty] = round(($baseValue - ($baseValue * (double)$discount) / 100), 2);
        }

        return $value;
    }

    // ---------------------------------------

    protected function prepareOptionTitles($optionTitles)
    {
        return $optionTitles;
    }

    protected function prepareAttributeTitles($attributeTitles)
    {
        return $attributeTitles;
    }

    //########################################

    protected function isSourceModeNone()
    {
        return $this->getSourceMode() == self::MODE_NONE;
    }

    protected function isSourceModeProduct()
    {
        return $this->getSourceMode() == self::MODE_PRODUCT;
    }

    protected function isSourceModeSpecial()
    {
        return $this->getSourceMode() == self::MODE_SPECIAL;
    }

    protected function isSourceModeAttribute()
    {
        return $this->getSourceMode() == self::MODE_ATTRIBUTE;
    }

    protected function isSourceModeTier()
    {
        return $this->getSourceMode() == self::MODE_TIER;
    }

    //########################################
}
