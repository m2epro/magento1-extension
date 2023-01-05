<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Variation
{
    const GROUPED_PRODUCT_ATTRIBUTE_LABEL              = 'Option';
    const DOWNLOADABLE_PRODUCT_DEFAULT_ATTRIBUTE_LABEL = 'Links';

    /** @var Ess_M2ePro_Model_Magento_Product $_magentoProduct */
    protected $_magentoProduct = null;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->_magentoProduct;
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->_magentoProduct = $magentoProduct;
        return $this;
    }

    //########################################

    public function getVariationTypeStandard(array $options)
    {
        $variations = $this->getVariationsTypeStandard();

        foreach ($variations['variations'] as $variation) {
            $tempOption = array();
            foreach ($variation as $variationOption) {
                $tempOption[$variationOption['attribute']] = $variationOption['option'];
            }

            if (!array_diff_assoc($options,$tempOption)) {
                return $variation;
            }
        }

        return null;
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getVariationsTypeStandard()
    {
        $variations = array();
        $variationsSet = array();
        $additional = array();

        if ($this->getMagentoProduct()->isConfigurableType()) {
            $tempInfo = $this->getConfigurableVariationsTypeStandard();
            isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
            isset($tempInfo['variations']) && $variations = $tempInfo['variations'];
            isset($tempInfo['additional']) && $additional = $tempInfo['additional'];
        } else {
            if ($this->getMagentoProduct()->isSimpleType()) {
                $tempInfo = $this->getSimpleVariationsTypeStandard();
                isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
                isset($tempInfo['variations']) && $variations = $tempInfo['variations'];
            } else if ($this->getMagentoProduct()->isBundleType()) {
                $tempInfo = $this->getBundleVariationsTypeStandard();
                isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
                isset($tempInfo['variations']) && $variations = $tempInfo['variations'];
            } elseif ($this->getMagentoProduct()->isGroupedType()) {
                $tempInfo = $this->getGroupedVariationsTypeStandard();
                isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
                isset($tempInfo['variations']) && $variations = $tempInfo['variations'];
            } elseif ($this->getMagentoProduct()->isDownloadableType()) {
                $tempInfo = $this->getDownloadableVariationsTypeStandard();
                isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
                isset($tempInfo['variations']) && $variations = $tempInfo['variations'];
            }

            $countOfCombinations = 1;

            foreach ($variationsSet as $set) {
                $countOfCombinations *= count($set);
            }

            if ($countOfCombinations > 100000) {
                $variationsSet = array();
                $variations = array();
            } else {
                $this->prepareVariationsScopeTypeStandard($variations);
                $variations = $this->prepareVariationsTypeStandard($variations, $variationsSet);
            }
        }

        if ($this->getMagentoProduct()->getVariationVirtualAttributes() &&
            !$this->getMagentoProduct()->isIgnoreVariationVirtualAttributes()
        ) {
            $this->injectVirtualAttributesTypeStandard($variations, $variationsSet);
        }

        if ($this->getMagentoProduct()->getVariationFilterAttributes() &&
            !$this->getMagentoProduct()->isIgnoreVariationFilterAttributes()
        ) {
            $this->filterByAttributesTypeStandard($variations, $variationsSet);
        }

        return array(
            'set'        => $variationsSet,
            'variations' => $variations,
            'additional' => $additional
        );
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getSimpleVariationsTypeStandard()
    {
        if (!$this->getMagentoProduct()->isSimpleType()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        $variationOptionsTitle = array();
        $variationOptionsList = array();

        foreach ($product->getOptions() as $productCustomOptions) {
            if (!(bool)(int)$productCustomOptions->getData('is_require')) {
                continue;
            }

            if (in_array($productCustomOptions->getType(), $this->getCustomOptionsAllowedTypes())) {
                $optionCombinationTitle = array();
                $possibleVariationProductOptions = array();

                $optionTitle = $productCustomOptions->getTitle();
                if ($optionTitle == '') {
                    $optionTitle = $productCustomOptions->getDefaultTitle();
                }

                foreach ($productCustomOptions->getValues() as $option) {
                    $optionCombinationTitle[] = $option->getTitle();

                    $possibleVariationProductOptions[] = array(
                        'product_id'   => $product->getId(),
                        'product_type' => $product->getTypeId(),
                        'attribute'    => $optionTitle,
                        'option'       => $option->getTitle(),
                    );
                }

                $variationOptionsTitle[$optionTitle] = $optionCombinationTitle;
                $variationOptionsList[] = $possibleVariationProductOptions;
            }
        }

        return array(
            'set'        => $variationOptionsTitle,
            'variations' => $variationOptionsList,
        );
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getConfigurableVariationsTypeStandard()
    {
        if (!$this->getMagentoProduct()->isConfigurableType()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();
        $productTypeInstance->setStoreFilter($this->getMagentoProduct()->getStoreId(), $product);

        $attributes = array();
        $set = array();

        foreach ($productTypeInstance->getConfigurableAttributes($product) as $configurableAttribute) {

            /** @var Mage_Catalog_Model_Product_Type_Configurable_Attribute $configurableAttribute */
            $configurableAttribute->setStoreId($this->getMagentoProduct()->getStoreId());

            /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
            $attribute = $configurableAttribute->getProductAttribute();
            if (!$attribute) {
                $message = "Configurable Magento Product (ID {$this->getMagentoProduct()->getProductId()})";
                $message .= ' has no selected configurable attribute.';
                throw new \Ess_M2ePro_Model_Exception($message);
            }

            $attribute->setStoreId($this->getMagentoProduct()->getStoreId());

            $attributeLabel = '';

            if (!(int)$configurableAttribute->getData('use_default') && $configurableAttribute->getData('label')) {
                $attributeLabel = $configurableAttribute->getData('label');
            }

            if ($attributeLabel == '') {
                if ($this->getMagentoProduct()->getStoreId() && $tempStoreLabels = $attribute->getStoreLabels()) {
                    if (isset($tempStoreLabels[$this->getMagentoProduct()->getStoreId()])) {
                        $attributeLabel = $tempStoreLabels[$this->getMagentoProduct()->getStoreId()];
                    }
                }

                $attributeLabel == '' && $attributeLabel = $attribute->getFrontendLabel();
            }

            $attributes[$attribute->getAttributeCode()] = $attributeLabel;
            $set[$attribute->getAttributeCode()] = array(
                'label'   => $attributeLabel,
                'options' => array(),
            );
        }

        $variations = array();

        foreach ($productTypeInstance->getUsedProducts(null, $product) as $childProduct) {
            $variation = array();
            $childProduct->setStoreId($this->getMagentoProduct()->getStoreId());

            foreach ($attributes as $attributeCode => $attributeLabel) {
                $attributeValue = Mage::getModel('M2ePro/Magento_Product')
                    ->setProduct($childProduct)
                    ->getAttributeValue($attributeCode);

                if ($attributeValue === '' || $attributeValue === null) {
                    break;
                }

                $variation[] = array(
                    'product_id'     => $childProduct->getId(),
                    'product_type'   => $product->getTypeId(),
                    'attribute'      => $attributeLabel,
                    'attribute_code' => $attributeCode,
                    'option'         => $attributeValue,
                );
            }

            if (count($attributes) == count($variation)) {
                $variations[] = $variation;
            }
        }

        foreach ($variations as $variation) {
            foreach ($variation as $option) {
                $set[$option['attribute_code']]['options'][] = $option['option'];
            }
        }

        $resultSet = array();
        foreach ($set as $code => $data) {
            $options = array();
            if (!empty($data['options'])) {
                $options = $this->sortAttributeOptions($code, array_values(array_unique($data['options'])));
            }

            $resultSet[$data['label']] = $options;
        }

        return array(
            'set'        => $resultSet,
            'variations' => $variations,
            'additional' => array(
                'attributes' => $attributes
            )
        );
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getGroupedVariationsTypeStandard()
    {
        if (!$this->getMagentoProduct()->isGroupedType()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        $optionCombinationTitle = array();

        $possibleVariationProductOptions = array();
        $associatedProducts = $this->getMagentoProduct()->getTypeInstance()->getAssociatedProducts();

        foreach ($associatedProducts as $singleProduct) {
            $optionCombinationTitle[] = $singleProduct->getName();

            $possibleVariationProductOptions[] = array(
                'product_id'   => $singleProduct->getId(),
                'product_type' => $product->getTypeId(),
                'attribute'    => self::GROUPED_PRODUCT_ATTRIBUTE_LABEL,
                'option'       => $singleProduct->getName(),
            );
        }

        $variationOptionsTitle[self::GROUPED_PRODUCT_ATTRIBUTE_LABEL] = $optionCombinationTitle;
        $variationOptionsList[] = $possibleVariationProductOptions;

        return array(
            'set'        => $variationOptionsTitle,
            'variations' => $variationOptionsList,
        );
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getBundleVariationsTypeStandard()
    {
        if (!$this->getMagentoProduct()->isBundleType()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        $productInstance = $this->getMagentoProduct()->getTypeInstance();
        $productInstance->setStoreFilter($this->getMagentoProduct()->getStoreId(), $product);

        $optionCollection = $productInstance->getOptionsCollection();

        $variationOptionsTitle = array();
        $variationOptionsList = array();

        foreach ($optionCollection as $singleOption) {
            if (!(bool)(int)$singleOption->getData('required')) {
                continue;
            }

            $optionTitle = $singleOption->getTitle();
            if ($optionTitle == '') {
                $optionTitle = $singleOption->getDefaultTitle();
            }

            if (isset($variationOptionsTitle[$optionTitle])) {
                continue;
            }

            $optionCombinationTitle = array();
            $possibleVariationProductOptions = array();

            $selectionsCollectionItems = $productInstance->getSelectionsCollection(
                array(0 => $singleOption->getId()), $product
            )->getItems();

            if (empty($selectionsCollectionItems)) {
                continue;
            }

            foreach ($selectionsCollectionItems as $item) {
                $optionCombinationTitle[] = $item->getName();
                $possibleVariationProductOptions[] = array(
                    'product_id'   => $item->getProductId(),
                    'product_type' => $product->getTypeId(),
                    'attribute'    => $optionTitle,
                    'option'       => $item->getName(),
                );
            }

            $variationOptionsTitle[$optionTitle] = $optionCombinationTitle;
            $variationOptionsList[] = $possibleVariationProductOptions;
        }

        return array(
            'set'        => $variationOptionsTitle,
            'variations' => $variationOptionsList,
        );
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getDownloadableVariationsTypeStandard()
    {
        if (!$this->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        $attributeTitle = $product->getData('links_title');

        if (empty($attributeTitle)) {
            $attributeTitle = Mage::getStoreConfig(
                Mage_Downloadable_Model_Link::XML_PATH_LINKS_TITLE, $this->getMagentoProduct()->getStoreId()
            );
        }

        if (empty($attributeTitle)) {
            $attributeTitle = $this->getMagentoProduct()->getProduct()->getAttributeDefaultValue('links_title');
        }

        if (empty($attributeTitle)) {
            $attributeTitle = Mage::getStoreConfig(Mage_Downloadable_Model_Link::XML_PATH_LINKS_TITLE);
        }

        if (empty($attributeTitle)) {
            $attributeTitle = self::DOWNLOADABLE_PRODUCT_DEFAULT_ATTRIBUTE_LABEL;
        }

        $optionCombinationTitle          = array();
        $possibleVariationProductOptions = array();

        /** @var Mage_Downloadable_Model_Link[] $links */
        $links = $this->getMagentoProduct()->getTypeInstance()->getLinks();

        foreach ($links as $link) {
            $linkTitle = $link->getStoreTitle();
            if (empty($linkTitle)) {
                $linkTitle = $link->getDefaultTitle();
            }

            $optionCombinationTitle[] = $linkTitle;
            $possibleVariationProductOptions[] = array(
                'product_id'   => $product->getId(),
                'product_type' => $product->getTypeId(),
                'attribute'    => $attributeTitle,
                'option'       => $linkTitle,
            );
        }

        $variationOptionsTitle[$attributeTitle] = $optionCombinationTitle;
        $variationOptionsList[] = $possibleVariationProductOptions;

        return array(
            'set'        => $variationOptionsTitle,
            'variations' => $variationOptionsList,
        );
    }

    protected function prepareVariationsScopeTypeStandard(&$optionsScope)
    {
        $tempArray = array();

        foreach ($optionsScope as $key => $optionScope) {
            $temp = reset($optionScope);
            $attribute = $temp['attribute'];

            if (isset($tempArray[$attribute])) {
                unset($optionsScope[$key]);
                continue;
            }

            $tempArray[$attribute] = 1;
        }
    }

    protected function prepareVariationsTypeStandard(&$optionsScope, &$set, $optionScopeIndex = 0)
    {
        $resultVariations = array();

        if (!isset($optionsScope[$optionScopeIndex])) {
            return $resultVariations;
        }

        $subVariations = $this->prepareVariationsTypeStandard($optionsScope, $set, $optionScopeIndex+1);

        if (empty($subVariations)) {
            foreach ($optionsScope[$optionScopeIndex] as $option) {
                $resultVariations[] = array($option);
            }

            return $resultVariations;
        }

        foreach ($optionsScope[$optionScopeIndex] as $option) {
            if (!isset($set[$option['attribute']]) ||
                !in_array($option['option'], $set[$option['attribute']], true)) {
                continue;
            }

            foreach ($subVariations as $subVariation) {
                $subVariation[] = $option;
                $resultVariations[] = $subVariation;
            }
        }

        return $resultVariations;
    }

    protected function sortAttributeOptions($attributeCode, $options)
    {
        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);

        /** @var Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection $optionCollection */
        $optionCollection = Mage::getModel('eav/entity_attribute_option')->getCollection();
        $optionCollection->setAttributeFilter($attribute->getId());
        $optionCollection->setPositionOrder();
        $optionCollection->setStoreFilter($this->getMagentoProduct()->getStoreId());

        $sortedOptions = array();
        foreach ($optionCollection as $option) {
            if (!in_array($option->getValue(), $options, true) ||
                in_array($option->getValue(), $sortedOptions, true)) {
                continue;
            }

            $sortedOptions[] = $option->getValue();
        }

        return $sortedOptions;
    }

    protected function injectVirtualAttributesTypeStandard(&$variations, &$set)
    {
        $virtualAttributes = $this->getMagentoProduct()->getVariationVirtualAttributes();
        if (empty($virtualAttributes)) {
            return;
        }

        foreach ($variations as $variationKey => $variation) {
            foreach ($virtualAttributes as $virtualAttribute => $virtualValue) {
                $existOption = reset($variation);

                $virtualOption = array(
                    'product_id'   => null,
                    'product_type' => $existOption['product_type'],
                    'attribute'    => $virtualAttribute,
                    'option'       => $virtualValue,
                );

                $variations[$variationKey][] = $virtualOption;
            }
        }

        foreach ($virtualAttributes as $virtualAttribute => $virtualValue) {
            $set[$virtualAttribute] = array($virtualValue);
        }
    }

    protected function filterByAttributesTypeStandard(&$variations, &$set)
    {
        $filterAttributes = $this->getMagentoProduct()->getVariationFilterAttributes();
        if (empty($filterAttributes)) {
            return;
        }

        foreach ($variations as $variationKey => $variation) {
            foreach ($variation as $optionKey => $option) {
                if (!isset($filterAttributes[$option['attribute']])) {
                    continue;
                }

                $filterValue = $filterAttributes[$option['attribute']];
                if ($option['option'] == $filterValue) {
                    continue;
                }

                unset($variations[$variationKey]);
                break;
            }
        }

        $variations = array_values($variations);

        foreach ($set as $attribute => $values) {
            if (!isset($filterAttributes[$attribute])) {
                continue;
            }

            $filterValue = $filterAttributes[$attribute];
            if (!in_array($filterValue, $values)) {
                $set[$attribute] = array();
                continue;
            }

            $set[$attribute] = array($filterValue);
        }
    }

    // ---------------------------------------

    public function getVariationsTypeRaw()
    {
        if ($this->getMagentoProduct()->isSimpleType()) {
            return $this->getSimpleVariationsTypeRaw();
        }

        if ($this->getMagentoProduct()->isConfigurableType()) {
            return $this->getConfigurableVariationsTypeRaw();
        }

        if ($this->getMagentoProduct()->isGroupedType()) {
            return $this->getGroupedVariationsTypeRaw();
        }

        if ($this->getMagentoProduct()->isBundleType()) {
            return $this->getBundleVariationsTypeRaw();
        }

        if ($this->getMagentoProduct()->isDownloadableType()) {
            return $this->getDownloadableVariationTypeRaw();
        }

        return array();
    }

    protected function getSimpleVariationsTypeRaw()
    {
        if (!$this->getMagentoProduct()->isSimpleType()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        $customOptions = array();

        $productOptions = $product->getOptions();

        foreach ($productOptions as $option) {
            if (!(bool)(int)$option->getData('is_require')) {
                continue;
            }

            if (!in_array($option->getType(), $this->getCustomOptionsAllowedTypes())) {
                continue;
            }

            $customOption = array(
                'option_id' => $option->getData('option_id'),
                'values'    => array(),
                'labels'    => array_filter(
                    array(
                    trim($option->getData('store_title')),
                    trim($option->getData('title')),
                    trim($option->getData('default_title')),
                    )
                )
            );

            $values = $option->getValues();

            foreach ($values as $value) {
                $customOption['values'][] = array(
                    'product_ids' => array($this->getMagentoProduct()->getProductId()),
                    'value_id' => $value->getData('option_type_id'),
                    'labels'   => array_filter(
                        array(
                        trim($value->getData('store_title')),
                        trim($value->getData('title')),
                        trim($value->getData('default_title'))
                        )
                    )
                );
            }

            if (empty($customOption['values'])) {
                continue;
            }

            $customOptions[] = $customOption;
        }

        return $customOptions;
    }

    protected function getConfigurableVariationsTypeRaw()
    {
        if (!$this->getMagentoProduct()->isConfigurableType()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();

        $configurableOptions = array();

        foreach ($productTypeInstance->getConfigurableAttributes($product) as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            if (!$productAttribute) {
                $message = "Configurable Magento Product (ID {$this->getMagentoProduct()->getProductId()})";
                $message .= ' has no selected configurable attribute.';
                throw new \Ess_M2ePro_Model_Exception($message);
            }

            $storeId = $this->getMagentoProduct()->getStoreId();
            $productAttribute->setStoreId($storeId);

            $configurableOption = array(
                'option_id' => $attribute->getAttributeId(),
                'labels' => array_filter(
                    array(
                    trim($attribute->getData('label')),
                    trim($productAttribute->getFrontendLabel()),
                    trim($productAttribute->getStoreLabel($storeId)),
                    trim($this->getStoreLabel($productAttribute, $storeId)),
                    )
                ),
                'values' => $this->getConfigurableAttributeValues($attribute),
            );

            if (empty($configurableOption['values'])) {
                continue;
            }

            $configurableOptions[] = $configurableOption;
        }

        return $configurableOptions;
    }

    /**
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $productAttribute
     * @param int $storeId
     * @return string|null
     */
    protected function getStoreLabel($productAttribute, $storeId)
    {
        $labels = $productAttribute->getStoreLabels();
        if (!isset($labels[$storeId])) {
            return null;
        }

        return $labels[$storeId];
    }

    protected function getGroupedVariationsTypeRaw()
    {
        if (!$this->getMagentoProduct()->isGroupedType()) {
            return array();
        }

        return $this->getMagentoProduct()->getTypeInstance()->getAssociatedProducts();
    }

    protected function getBundleVariationsTypeRaw()
    {
        if (!$this->getMagentoProduct()->isBundleType()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        $bundleOptions = array();

        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();

        $optionsCollection = $productTypeInstance->getOptionsCollection();
        $selectionsCollection = $productTypeInstance
            ->getSelectionsCollection($optionsCollection->getAllIds(), $product);

        foreach ($optionsCollection as $option) {
            if (!$option->getData('required')) {
                continue;
            }

            $bundleOption = array(
                'option_id' => $option->getData('option_id'),
                'values'    => array(),
                'labels'    => array_filter(
                    array(
                    trim($option->getData('default_title')),
                    trim($option->getData('title')),
                    )
                ),
            );

            foreach ($selectionsCollection as $selection) {
                if ($option->getData('option_id') != $selection->getData('option_id')) {
                    continue;
                }

                $bundleOption['values'][] = array(
                    'product_ids' => array($selection->getData('product_id')),
                    'value_id'    => $selection->getData('selection_id'),
                    'labels'      => array(trim($selection->getData('name'))),
                );
            }

            if (empty($bundleOption['values'])) {
                continue;
            }

            $bundleOptions[] = $bundleOption;
        }

        return $bundleOptions;
    }

    protected function getDownloadableVariationTypeRaw()
    {
        if (!$this->getMagentoProduct()->isDownloadableType()) {
            return array();
        }

        /** @var Mage_Downloadable_Model_Link[] $links */
        $links = $this->getMagentoProduct()->getTypeInstance()->getLinks();
        if (empty($links)) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        $labels = array();

        $labels[] = $product->getData('links_title');
        $labels[] = Mage::getStoreConfig(
            Mage_Downloadable_Model_Link::XML_PATH_LINKS_TITLE, $this->getMagentoProduct()->getStoreId()
        );
        $labels[] = $this->getMagentoProduct()->getProduct()->getAttributeDefaultValue('links_title');
        $labels[] = Mage::getStoreConfig(Mage_Downloadable_Model_Link::XML_PATH_LINKS_TITLE);
        $labels[] = self::DOWNLOADABLE_PRODUCT_DEFAULT_ATTRIBUTE_LABEL;

        $resultOptions = array(
            'option_id' => $product->getId(),
            'values'    => array(),
            'labels'    => array_values(array_filter($labels))
        );

        foreach ($links as $link) {
            $resultOptions['values'][] = array(
                'product_ids' => array($product->getId()),
                'value_id'    => $link->getId(),
                'labels'      => array_filter(
                    array(
                    $link->getStoreTitle(),
                    $link->getDefaultTitle(),
                    )
                ),
            );
        }

        return array($resultOptions);
    }

    protected function getConfigurableAttributeValues($attribute)
    {
        $product = $this->getMagentoProduct()->getProduct();
        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();

        $productAttribute = $attribute->getProductAttribute();
        if (!$productAttribute) {
            $message = "Configurable Magento Product (ID {$this->getMagentoProduct()->getProductId()})";
            $message .= ' has no selected configurable attribute.';
            throw new \Ess_M2ePro_Model_Exception($message);
        }

        $options = $this->getConfigurableAttributeOptions($productAttribute);
        $values = array();

        foreach ($options as $option) {
            foreach ($productTypeInstance->getUsedProducts(null, $product) as $associatedProduct) {
                if ($option['value_id'] != $associatedProduct->getData($productAttribute->getAttributeCode())) {
                    continue;
                }

                $attributeOptionKey = $attribute->getAttributeId() . ':' . $option['value_id'];
                if (!isset($values[$attributeOptionKey])) {
                    $values[$attributeOptionKey] = $option;
                }

                $values[$attributeOptionKey]['product_ids'][] = $associatedProduct->getId();
            }
        }

        return array_values($values);
    }

    protected function getConfigurableAttributeOptions($productAttribute)
    {
        $options = $productAttribute->getSource()->getAllOptions(false, false);
        $defaultOptions = $productAttribute->getSource()->getAllOptions(false, true);

        $mergedOptions = array();
        foreach ($options as $option) {
            $mergedOption = array(
                'product_ids' => array(),
                'value_id' => $option['value'],
                'labels' => array(
                    trim($option['label'])
                )
            );

            foreach ($defaultOptions as $defaultOption) {
                if ($defaultOption['value'] == $option['value']) {
                    $mergedOption['labels'][] = trim($defaultOption['label']);
                    break;
                }
            }

            $mergedOptions[] = $mergedOption;
        }

        return $mergedOptions;
    }

    // ---------------------------------------

    public function getTitlesVariationSet()
    {
        if ($this->getMagentoProduct()->isSimpleType()) {
            return $this->getSimpleTitlesVariationSet();
        }

        if ($this->getMagentoProduct()->isConfigurableType()) {
            return $this->getConfigurableTitlesVariationSet();
        }

        if ($this->getMagentoProduct()->isGroupedType()) {
            return $this->getGroupedTitlesVariationSet();
        }

        if ($this->getMagentoProduct()->isBundleType()) {
            return $this->getBundleTitlesVariationSet();
        }

        if ($this->getMagentoProduct()->isDownloadableType()) {
            return $this->getDownloadableTitlesVariationSet();
        }

        return array();
    }

    protected function getSimpleTitlesVariationSet()
    {
        if (!$this->getMagentoProduct()->isSimpleType()) {
            return array();
        }

        /** @var Mage_Catalog_Model_Resource_Eav_Resource_Product_Option_Collection $optionsCollection */
        $optionsCollection = Mage::getResourceModel('catalog/product_option_collection');
        $optionsCollection->addProductToFilter($this->getMagentoProduct()->getProductId());

        $storesTitles = array();

        foreach (Mage::app()->getStores(true) as $store) {
            /** @var Mage_Core_Model_Store $store */

            $optionsCollection->reset();

            $storeId = (int)$store->getId();

            $optionsCollection->getOptions($storeId);
            $optionsCollection->addValuesToResult($storeId);

            foreach ($optionsCollection as $option) {
                /** @var Mage_Catalog_Model_Product_Option $option */

                if (!$option->getData('is_require')
                    || !in_array($option->getType(), $this->getCustomOptionsAllowedTypes())
                    || $option->getProductId() != $this->getMagentoProduct()->getProductId()
                ) {
                    continue;
                }

                $optionId = (int)$option->getId();

                if (!isset($storesTitles[$optionId])) {
                    $storesTitles[$optionId] = array(
                        'titles' => array(),
                        'values' => array(),
                    );
                }

                if ($option->getData('store_title') !== null) {
                    $storesTitles[$optionId]['titles'][$storeId] = $option->getData('store_title');
                }

                foreach ($option->getValues() as $value) {
                    /** @var Mage_Catalog_Model_Product_Option_Value $value */

                    if ($value->getData('store_title') === null) {
                        continue;
                    }

                    $storesTitles[$optionId]['values'][(int)$value->getId()][$storeId]
                        = $value->getData('store_title');
                }
            }
        }

        $resultTitles = array();
        foreach ($storesTitles as $storeOption) {
            $titles = array_values(array_unique($storeOption['titles']));

            $values = array();
            foreach ($storeOption['values'] as $valueStoreTitles) {
                $keyValue = $valueStoreTitles[Mage_Core_Model_App::ADMIN_STORE_ID];
                if (isset($valueStoreTitles[$this->getMagentoProduct()->getStoreId()])) {
                    $keyValue = $valueStoreTitles[$this->getMagentoProduct()->getStoreId()];
                }

                $valueStoreTitles = array_unique($valueStoreTitles);
                $valueStoreTitles = array_values($valueStoreTitles);

                $values[$keyValue] = $valueStoreTitles;
            }

            $keyValue = $storeOption['titles'][Mage_Core_Model_App::ADMIN_STORE_ID];
            if (isset($storeOption['titles'][$this->getMagentoProduct()->getStoreId()])) {
                $keyValue = $storeOption['titles'][$this->getMagentoProduct()->getStoreId()];
            }

            $resultTitles[$keyValue] = array(
                'titles' => $titles,
                'values' => $values,
            );
        }

        return $resultTitles;
    }

    protected function getConfigurableTitlesVariationSet()
    {
        if (!$this->getMagentoProduct()->isConfigurableType()) {
            return array();
        }

        $resultTitles = array();
        foreach ($this->getMagentoProduct()->getTypeInstance()->getConfigurableAttributes() as $configurableAttribute) {
            $productAttribute = $configurableAttribute->getProductAttribute();
            if (!$productAttribute) {
                $message = "Configurable Magento Product (ID {$this->getMagentoProduct()->getProductId()})";
                $message .= ' has no selected configurable attribute.';
                throw new \Ess_M2ePro_Model_Exception($message);
            }

            $attributeStoreTitles = $productAttribute->getStoreLabels();

            $attributeKeyTitle = $productAttribute->getFrontendLabel();
            if (isset($attributeStoreTitles[$this->getMagentoProduct()->getStoreId()])) {
                $attributeKeyTitle = $attributeStoreTitles[$this->getMagentoProduct()->getStoreId()];
            }

            if (!(int)$configurableAttribute->getData('use_default') && $configurableAttribute->getData('label')) {
                $attributeKeyTitle = $configurableAttribute->getData('label');
                $attributeStoreTitles[] = $configurableAttribute->getData('label');
            }

            if (isset($resultTitles[$attributeKeyTitle])) {
                continue;
            }

            $attributeStoreTitles[] = $productAttribute->getFrontendLabel();

            $resultTitles[$attributeKeyTitle]['titles'] = array_values(array_unique($attributeStoreTitles));

            $attributeValues = array();
            foreach (Mage::app()->getStores(true) as $store) {
                /** @var Mage_Core_Model_Store $store */

                $storeId = (int)$store->getId();

                foreach ($productAttribute->getSource()->getAllOptions() as $option) {
                    $valueId = $option['value'];
                    $value   = $option['label'];

                    if (!isset($attributeValues[$valueId])) {
                        $attributeValues[$valueId] = array();
                    }

                    if (!in_array($value, $attributeValues[$valueId], true)) {
                        $attributeValues[$valueId][$storeId] = $value;
                    }
                }
            }

            $resultTitles[$attributeKeyTitle]['values'] = array();

            foreach ($attributeValues as $attributeValue) {
                $keyValue = $attributeValue[Mage_Core_Model_App::ADMIN_STORE_ID];
                if (isset($attributeValue[$this->getMagentoProduct()->getStoreId()])) {
                    $keyValue = $attributeValue[$this->getMagentoProduct()->getStoreId()];
                }

                $resultTitles[$attributeKeyTitle]['values'][$keyValue] = array_unique(array_values($attributeValue));
            }
        }

        return $resultTitles;
    }

    protected function getGroupedTitlesVariationSet()
    {
        $storesTitles = array();

        foreach (Mage::app()->getStores(true) as $store) {
            /** @var Mage_Core_Model_Store $store */

            $storeId = (int)$store->getId();

            $associatedProductsCollection = $this->getMagentoProduct()->getProduct()
                ->getLinkInstance()
                ->useGroupedLinks()
                ->getProductCollection()
                ->setIsStrongMode()
                ->setProduct($this->getMagentoProduct()->getProduct())
                ->addAttributeToSelect('name')
                ->addStoreFilter($storeId)
                ->setStoreId($storeId);

            foreach ($associatedProductsCollection as $associatedProduct) {
                /** @var Mage_Catalog_Model_Product $associatedProduct */

                $productId = (int)$associatedProduct->getId();

                if (!isset($storesTitles[$productId])) {
                    $storesTitles[$productId] = array();
                }

                $storesTitles[$productId][$storeId] = $associatedProduct->getName();
            }
        }

        $resultTitles = array(
            self::GROUPED_PRODUCT_ATTRIBUTE_LABEL => array(
                'titles' => array(),
                'values' => array(),
            ),
        );
        foreach ($storesTitles as $productTitles) {
            $keyValue = $productTitles[Mage_Core_Model_App::ADMIN_STORE_ID];
            if (isset($productTitles[$this->getMagentoProduct()->getStoreId()])) {
                $keyValue = $productTitles[$this->getMagentoProduct()->getStoreId()];
            }

            $resultTitles[self::GROUPED_PRODUCT_ATTRIBUTE_LABEL]['values'][$keyValue]
                = array_values(array_unique($productTitles));
        }

        return $resultTitles;
    }

    protected function getBundleTitlesVariationSet()
    {
        $storesTitles = array();

        foreach (Mage::app()->getStores(true) as $store) {
            /** @var Mage_Core_Model_Store $store */

            $storeId = (int)$store->getId();

            $optionsCollection = Mage::getModel('bundle/option')->getResourceCollection()
                ->setProductIdFilter($this->getMagentoProduct()->getProductId())
                ->joinValues($storeId);

            foreach ($optionsCollection as $option) {
                /** @var Mage_Bundle_Model_Option $option */

                if (!$option->getData('required')) {
                    continue;
                }

                $optionId = (int)$option->getOptionId();

                if (!isset($storesTitles[$optionId])) {
                    $storesTitles[$optionId] = array(
                        'titles' => array(),
                        'values' => array(),
                    );
                }

                $storesTitles[$optionId]['titles'][$storeId] = $option->getTitle();

                $selectionsCollection = Mage::getResourceModel('bundle/selection_collection')
                    ->addAttributeToSelect('name')
                    ->setFlag('require_stock_items', true)
                    ->setFlag('product_children', true)
                    ->addStoreFilter($storeId)
                    ->setStoreId($storeId)
                    ->addFilterByRequiredOptions()
                    ->setOptionIdsFilter(array($optionId));

                foreach ($selectionsCollection as $selectionProduct) {
                    /** @var Mage_Catalog_Model_Product $selectionProduct */

                    $productId = (int)$selectionProduct->getId();

                    if (!isset($storesTitles[$optionId]['values'][$productId])) {
                        $storesTitles[$optionId]['values'][$productId] = array();
                    }

                    $selectionName = $selectionProduct->getName();
                    $storesTitles[$optionId]['values'][$productId][$storeId] = $selectionName;
                }
            }
        }

        $resultTitles = array();
        foreach ($storesTitles as $storeOption) {
            $titles = array_values(array_unique($storeOption['titles']));

            $values = array();
            foreach ($storeOption['values'] as $valueStoreTitles) {
                $keyValue = $valueStoreTitles[Mage_Core_Model_App::ADMIN_STORE_ID];
                if (isset($valueStoreTitles[$this->getMagentoProduct()->getStoreId()])) {
                    $keyValue = $valueStoreTitles[$this->getMagentoProduct()->getStoreId()];
                }

                $valueStoreTitles = array_unique($valueStoreTitles);
                $valueStoreTitles = array_values($valueStoreTitles);

                $values[$keyValue] = $valueStoreTitles;
            }

            $keyValue = $storeOption['titles'][Mage_Core_Model_App::ADMIN_STORE_ID];
            if (isset($storeOption['titles'][$this->getMagentoProduct()->getStoreId()])) {
                $keyValue = $storeOption['titles'][$this->getMagentoProduct()->getStoreId()];
            }

            $resultTitles[$keyValue] = array(
                'titles' => $titles,
                'values' => $values,
            );
        }

        return $resultTitles;
    }

    protected function getDownloadableTitlesVariationSet()
    {
        if (!$this->getMagentoProduct()->isDownloadableType()) {
            return array();
        }

        $storesTitles  = array();
        $storesOptions = array();

        foreach (Mage::app()->getStores(true) as $store) {
            /** @var Mage_Core_Model_Store $store */

            $storeId = (int)$store->getId();

            $productValue = Mage::getResourceModel('catalog/product')->getAttributeRawValue(
                $this->getMagentoProduct()->getProductId(), 'links_title', $storeId
            );
            $configValue = Mage::getStoreConfig(
                Mage_Downloadable_Model_Link::XML_PATH_LINKS_TITLE, $storeId
            );

            $storesTitles[$storeId] = array(
                $productValue,
                $configValue
            );

            $linkCollection = Mage::getModel('downloadable/link')->getCollection()
                ->addProductToFilter($this->getMagentoProduct()->getProductId())
                ->addTitleToResult($storeId);

            /** @var Mage_Downloadable_Model_Link[] $links */
            $links = $linkCollection->getItems();

            foreach ($links as $link) {
                $linkId = (int)$link->getId();
                $storeTitle = $link->getStoreTitle();
                if (!empty($storeTitle)) {
                    $storesOptions[$linkId][$storeId] = $storeTitle;
                }
            }
        }

        $titleKeyValue = reset($storesTitles[Mage_Core_Model_App::ADMIN_STORE_ID]);
        if (!empty($storesTitles[$this->getMagentoProduct()->getStoreId()])) {
            $titleKeyValue = reset($storesTitles[$this->getMagentoProduct()->getStoreId()]);
        }

        $resultTitles = array(
            $titleKeyValue => array(
                'titles' => array(self::DOWNLOADABLE_PRODUCT_DEFAULT_ATTRIBUTE_LABEL),
                'values' => array(),
            )
        );

        foreach ($storesTitles as $storeTitles) {
            $resultTitles[$titleKeyValue]['titles'] = array_values(
                array_unique(
                    array_merge(
                        $resultTitles[$titleKeyValue]['titles'], $storeTitles
                    )
                )
            );
        }

        foreach ($storesOptions as $optionValues) {
            if (empty($optionValues)) {
                continue;
            }

            $optionKeyValue = reset($optionValues);

            if (!empty($optionValues[Mage_Core_Model_App::ADMIN_STORE_ID])) {
                $optionKeyValue = $optionValues[Mage_Core_Model_App::ADMIN_STORE_ID];
            }

            if (!empty($optionValues[$this->getMagentoProduct()->getStoreId()])) {
                $optionKeyValue = $optionValues[$this->getMagentoProduct()->getStoreId()];
            }

            $resultTitles[$titleKeyValue]['values'][$optionKeyValue] = array_values(array_unique($optionValues));
        }

        return $resultTitles;
    }

    //########################################

    protected function getCustomOptionsAllowedTypes()
    {
        return array('drop_down', 'radio', 'multiple', 'checkbox');
    }

    //########################################
}
