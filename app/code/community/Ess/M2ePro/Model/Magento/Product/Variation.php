<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Variation
{
    const GROUPED_PRODUCT_ATTRIBUTE_LABEL = 'Option';

    /** @var Ess_M2ePro_Model_Magento_Product $magentoProduct */
    protected $magentoProduct = null;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
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

            if ($options == $tempOption) {
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

        $attributes = array();
        $set = array();

        foreach ($productTypeInstance->getConfigurableAttributes($product) as $configurableAttribute) {

            /** @var Mage_Catalog_Model_Product_Type_Configurable_Attribute $configurableAttribute */
            $configurableAttribute->setStoreId($this->getMagentoProduct()->getStoreId());

            /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
            $attribute = $configurableAttribute->getProductAttribute();
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

                if (empty($attributeValue)) {
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

        if (count($subVariations) <= 0) {

            foreach ($optionsScope[$optionScopeIndex] as $option) {
                $resultVariations[] = array($option);
            }

            return $resultVariations;
        }

        foreach ($optionsScope[$optionScopeIndex] as $option) {

            if (!isset($set[$option['attribute']]) ||
                !in_array($option['option'],$set[$option['attribute']],true)) {
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

            $customOption = array(
                'option_id' => $option->getData('option_id'),
                'values'    => array(),
                'labels'    => array_filter(array(
                    trim($option->getData('store_title')),
                    trim($option->getData('title')),
                    trim($option->getData('default_title')),
                ))
            );

            $values = $option->getValues();

            foreach ($values as $value) {
                $customOption['values'][] = array(
                    'product_ids' => array($this->getMagentoProduct()->getProductId()),
                    'value_id' => $value->getData('option_type_id'),
                    'labels'   => array_filter(array(
                        trim($value->getData('store_title')),
                        trim($value->getData('title')),
                        trim($value->getData('default_title'))
                    ))
                );
            }

            if (count($customOption['values']) == 0) {
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
            $productAttribute->setStoreId($this->getMagentoProduct()->getStoreId());

            $configurableOption = array(
                'option_id' => $attribute->getAttributeId(),
                'labels' => array_filter(array(
                    trim($attribute->getData('label')),
                    trim($productAttribute->getFrontendLabel()),
                    trim($productAttribute->getStoreLabel()),
                )),
                'values' => $this->getConfigurableAttributeValues($attribute),
            );

            if (count($configurableOption['values']) == 0) {
                continue;
            }

            $configurableOptions[] = $configurableOption;
        }

        return $configurableOptions;
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
                'labels'    => array_filter(array(
                    trim($option->getData('default_title')),
                    trim($option->getData('title')),
                )),
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

            if (count($bundleOption['values']) == 0) {
                continue;
            }

            $bundleOptions[] = $bundleOption;
        }

        return $bundleOptions;
    }

    protected function getConfigurableAttributeValues($attribute)
    {
        $product = $this->getMagentoProduct()->getProduct();
        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();

        $productAttribute = $attribute->getProductAttribute();

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

        return array();
    }

    protected function getSimpleTitlesVariationSet()
    {
        if (!$this->getMagentoProduct()->isSimpleType()) {
            return array();
        }

        /** @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Option_Collection $optionsCollection */
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

                if (!is_null($option->getData('store_title'))) {
                    $storesTitles[$optionId]['titles'][$storeId] = $option->getData('store_title');
                }

                foreach ($option->getValues() as $value) {
                    /** @var Mage_Catalog_Model_Product_Option_Value $value */

                    if (is_null($value->getData('store_title'))) {
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

                $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                    ->setAttributeFilter($productAttribute->getId())
                    ->setStoreFilter($storeId, false);

                foreach ($valuesCollection as $attributeValue) {
                    $valueId = (int)$attributeValue->getId();

                    if (!isset($attributeValues[$valueId])) {
                        $attributeValues[$valueId] = array();
                    }

                    if (!in_array($attributeValue->getValue(), $attributeValues[$valueId], true)) {
                        $attributeValues[$valueId][$storeId] = $attributeValue->getValue();
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
                $valueStoreTitles = array_unique($valueStoreTitles);
                $valueStoreTitles = array_values($valueStoreTitles);

                $keyValue = $valueStoreTitles[Mage_Core_Model_App::ADMIN_STORE_ID];
                if (isset($valueStoreTitles[$this->getMagentoProduct()->getStoreId()])) {
                    $keyValue = $valueStoreTitles[$this->getMagentoProduct()->getStoreId()];
                }

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

    //########################################

    protected function getCustomOptionsAllowedTypes()
    {
        return array('drop_down', 'radio', 'multiple', 'checkbox');
    }

    //########################################
}