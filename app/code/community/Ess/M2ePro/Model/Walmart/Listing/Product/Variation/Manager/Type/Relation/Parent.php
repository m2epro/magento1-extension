<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_LogicalUnit
{
    /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor $_processor */
    protected $_processor = null;

    /** @var Ess_M2ePro_Model_Listing_Product[] $_childListingsProducts */
    protected $_childListingsProducts = null;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor
     */
    public function getProcessor()
    {
        if ($this->_processor === null) {
            $this->_processor = Mage::getModel(
                'M2ePro/Walmart_Listing_Product_Variation_Manager'
                . '_Type_Relation_Parent_Processor'
            );
            $this->_processor->setListingProduct($this->getListingProduct());
        }

        return $this->_processor;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    public function getChildListingsProducts()
    {
        if ($this->isCacheEnabled() && $this->_childListingsProducts !== null) {
            return $this->_childListingsProducts;
        }

        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->addFieldToFilter('variation_parent_id', $this->getListingProduct()->getId());

        /** @var Ess_M2ePro_Model_Listing_Product[] $childListingsProducts */
        $childListingsProducts = $collection->getItems();

        if (!$this->isCacheEnabled()) {
            return $childListingsProducts;
        }

        foreach ($childListingsProducts as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartChildListingProduct */
            $walmartChildListingProduct = $childListingProduct->getChildObject();
            $walmartChildListingProduct->getVariationManager()->getTypeModel()->enableCache();
        }

        return $this->_childListingsProducts = $childListingsProducts;
    }

    //########################################

    /**
     * @return bool
     */
    public function isNeedProcessor()
    {
        return (bool)$this->getWalmartListingProduct()->getData('variation_parent_need_processor');
    }

    //########################################

    /**
     * @return bool
     */
    public function isActualProductAttributes()
    {
        $productAttributes = array_map('strtolower', (array)$this->getProductAttributes());
        $magentoAttributes = array_map('strtolower', (array)$this->getMagentoAttributes());

        sort($productAttributes);
        sort($magentoAttributes);

        return $productAttributes == $magentoAttributes;
    }

    /**
     * @return bool
     */
    public function isActualRealProductAttributes()
    {
        $realProductAttributes = array_map('strtolower', (array)$this->getRealProductAttributes());
        $realMagentoAttributes = array_map('strtolower', (array)$this->getRealMagentoAttributes());

        sort($realProductAttributes);
        sort($realMagentoAttributes);

        return $realProductAttributes == $realMagentoAttributes;
    }

    //########################################

    /**
     * @return array
     */
    public function getProductAttributes()
    {
        return array_merge($this->getRealProductAttributes(), array_keys($this->getVirtualProductAttributes()));
    }

    /**
     * @return mixed
     */
    public function getRealProductAttributes()
    {
        return parent::getProductAttributes();
    }

    //########################################

    /**
     * @param bool $save
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function resetProductAttributes($save = true)
    {
        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_product_attributes', $this->getRealMagentoAttributes()
        );

        $this->setVirtualChannelAttributes(array(), false);

        $this->restoreAllRemovedProductOptions(false);

        $save && $this->getListingProduct()->save();
    }

    //########################################

    /**
     * @return bool
     */
    public function hasChannelGroupId()
    {
        return (bool)$this->getChannelGroupId();
    }

    public function setChannelGroupId($groupId, $save = true)
    {
        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_channel_group_id', $groupId
        );

        $save && $this->getListingProduct()->save();
    }

    public function getChannelGroupId()
    {
        return $this->getListingProduct()->getSetting(
            'additional_data', 'variation_channel_group_id', null
        );
    }

    //########################################

    /**
     * @return bool
     */
    public function hasChannelAttributes()
    {
        $channelAttributes = $this->getChannelAttributes();

        return !empty($channelAttributes);
    }

    public function setChannelAttributes(array $attributes, $save = true)
    {
        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_channel_attributes', $attributes
        );

        $this->setVirtualProductAttributes(array(), false);
        $this->setVirtualChannelAttributes(array(), false);

        $save && $this->getListingProduct()->save();
    }

    /**
     * @return array
     */
    public function getChannelAttributes()
    {
        $attributes = $this->getListingProduct()->getSetting(
            'additional_data', 'variation_channel_attributes', null
        );

        if (empty($attributes)) {
            return array();
        }

        $attributes = array_merge($attributes, array_keys($this->getVirtualChannelAttributes()));

        return array_unique($attributes);
    }

    /**
     * @return array
     */
    public function getRealChannelAttributes()
    {
        return $this->getListingProduct()->getSetting(
            'additional_data', 'variation_channel_attributes', array()
        );
    }

    //########################################

    /**
     * @return bool
     */
    public function hasMatchedAttributes()
    {
        return (bool)$this->getMatchedAttributes();
    }

    /**
     * @return mixed
     */
    public function getMatchedAttributes()
    {
        $matchedAttributes = $this->getRealMatchedAttributes();
        if (empty($matchedAttributes)) {
            return array();
        }

        foreach ($this->getVirtualProductAttributes() as $attribute => $value) {
            $matchedAttributes[$attribute] = $attribute;
        }

        foreach ($this->getVirtualChannelAttributes() as $attribute => $value) {
            $matchedAttributes[$attribute] = $attribute;
        }

        return $matchedAttributes;
    }

    /**
     * @return mixed
     */
    public function getRealMatchedAttributes()
    {
        $matchedAttributes = $this->getListingProduct()->getSetting(
            'additional_data', 'variation_matched_attributes', null
        );

        if (empty($matchedAttributes)) {
            return array();
        }

        ksort($matchedAttributes);

        return $matchedAttributes;
    }

    // ---------------------------------------

    /**
     * @param array $matchedAttributes
     * @param bool $save
     */
    public function setMatchedAttributes(array $matchedAttributes, $save = true)
    {
        foreach ($this->getVirtualProductAttributes() as $attribute => $value) {
            unset($matchedAttributes[$attribute]);
        }

        foreach ($this->getVirtualChannelAttributes() as $attribute => $value) {
            unset($matchedAttributes[array_search($attribute, $matchedAttributes)]);
        }

        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_matched_attributes', $matchedAttributes
        );

        $save && $this->getListingProduct()->save();
    }

    //########################################

    public function getVirtualProductAttributes()
    {
        return $this->getListingProduct()->getSetting(
            'additional_data', 'variation_virtual_product_attributes', array()
        );
    }

    public function setVirtualProductAttributes(array $attributes, $save = true)
    {
        if (array_intersect(array_keys($attributes), $this->getRealProductAttributes())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Virtual product attributes are intersect with real attributes');
        }

        if (!empty($attributes)) {
            $this->setVirtualChannelAttributes(array(), false);
        }

        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_virtual_product_attributes', $attributes
        );

        $save && $this->getListingProduct()->save();
    }

    public function isActualVirtualProductAttributes()
    {
        if (!$this->getVirtualProductAttributes()) {
            return true;
        }

        $channelAttributes = $this->getRealChannelAttributes();

        if (empty($channelAttributes)) {
            return true;
        }

        return !array_diff(array_keys($this->getVirtualProductAttributes()), $channelAttributes);
    }

    // ---------------------------------------

    public function getVirtualChannelAttributes()
    {
        return $this->getListingProduct()->getSetting(
            'additional_data', 'variation_virtual_channel_attributes', array()
        );
    }

    public function setVirtualChannelAttributes(array $attributes, $save = true)
    {
        if (array_intersect(array_keys($attributes), $this->getRealChannelAttributes())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Virtual channel attributes are intersect with real attributes');
        }

        if (!empty($attributes)) {
            $this->setVirtualProductAttributes(array(), false);
        }

        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_virtual_channel_attributes', $attributes
        );

        $save && $this->getListingProduct()->save();
    }

    /**
     * @return bool
     */
    public function isActualVirtualChannelAttributes()
    {
        if (!$this->getVirtualChannelAttributes()) {
            return true;
        }

        $magentoVariations = $this->getRealMagentoVariations();
        $magentoVariationsSet = $magentoVariations['set'];

        foreach ($this->getVirtualChannelAttributes() as $attribute => $value) {
            if (!isset($magentoVariationsSet[$attribute])) {
                return false;
            }

            $productAttributeValues = $magentoVariationsSet[$attribute];
            if (!in_array($value, $productAttributeValues)) {
                return false;
            }
        }

        return true;
    }

    //########################################

    public function getRemovedProductOptions()
    {
        return $this->getListingProduct()->getSetting(
            'additional_data', 'variation_removed_product_variations', array()
        );
    }

    public function isProductsOptionsRemoved(array $productOptions)
    {
        foreach ($this->getRemovedProductOptions() as $removedProductOptions) {
            if ($productOptions != $removedProductOptions) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function addRemovedProductOptions(array $productOptions, $save = true)
    {
        if ($this->isProductsOptionsRemoved($productOptions)) {
            return;
        }

        $removedProductOptions = $this->getListingProduct()->getSetting(
            'additional_data', 'variation_removed_product_variations', array()
        );

        $removedProductOptions[] = $productOptions;

        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_removed_product_variations', $removedProductOptions
        );
        $save && $this->getListingProduct()->save();
    }

    public function restoreRemovedProductOptions(array $productOptions, $save = true)
    {
        if (!$this->isProductsOptionsRemoved($productOptions)) {
            return;
        }

        $removedProductOptions = $this->getRemovedProductOptions();

        foreach ($removedProductOptions as $key => $removedOptions) {
            if ($productOptions != $removedOptions) {
                continue;
            }

            unset($removedProductOptions[$key]);
            break;
        }

        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_removed_product_variations', $removedProductOptions
        );
        $save && $this->getListingProduct()->save();
    }

    public function restoreAllRemovedProductOptions($save = true)
    {
        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_removed_product_variations', array()
        );
        $save && $this->getListingProduct()->save();
    }

    //########################################

    /**
     * @param bool $freeOptionsFilter
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getUsedProductOptions($freeOptionsFilter = false)
    {
        $usedVariations = array();

        foreach ($this->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                continue;
            }

            if ($freeOptionsFilter
                && ($childListingProduct->isLocked()
                    || $childListingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
            ) {
                continue;
            }

            $usedVariations[] = $childTypeModel->getProductOptions();
        }

        return $usedVariations;
    }

    // ---------------------------------------

    public function getUnusedProductOptions()
    {
        return $this->getUnusedOptions($this->getCurrentProductOptions(), $this->getUsedProductOptions());
    }

    public function getNotRemovedUnusedProductOptions()
    {
        return $this->getUnusedOptions($this->getUnusedProductOptions(), $this->getRemovedProductOptions());
    }

    protected function getUnusedOptions($currentOptions, $usedOptions)
    {
        if (empty($currentOptions)) {
            return array();
        }

        if (empty($usedOptions)) {
            return $currentOptions;
        }

        $unusedOptions = array();

        foreach ($currentOptions as $id => $currentOption) {
            $isExist = false;
            foreach ($usedOptions as $option) {
                if ($option != $currentOption) {
                    continue;
                }

                $isExist = true;
                break;
            }

            if ($isExist) {
                continue;
            }

            $unusedOptions[$id] = $currentOption;
        }

        return $unusedOptions;
    }

    // ---------------------------------------

    protected function getCurrentProductOptions()
    {
        $magentoProductVariations = $this->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();

        $productOptions = array();

        foreach ($magentoProductVariations['variations'] as $option) {
            $productOption = array();

            foreach ($option as $attribute) {
                $productOption[$attribute['attribute']] = $attribute['option'];
            }

            $productOptions[] = $productOption;
        }

        return $productOptions;
    }

    //########################################

    /**
     * @param array $productOptions
     * @param array $channelOptions
     * @return Ess_M2ePro_Model_Listing_Product
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function createChildListingProduct(array $productOptions, array $channelOptions)
    {
        $data = array(
            'listing_id' => $this->getListingProduct()->getListingId(),
            'product_id' => $this->getListingProduct()->getProductId(),
            'status'     => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            'status_changer'      => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN,
            'is_variation_product' => 1,
            'is_variation_parent'  => 0,
            'variation_parent_id'  => $this->getListingProduct()->getId(),
            'template_category_id' => $this->getWalmartListingProduct()->getTemplateCategoryId(),
        );

        /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */
        $childListingProduct = Mage::helper('M2ePro/Component_Walmart')->getModel('Listing_Product')->setData($data);
        $childListingProduct->save();

        $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
        $instruction->setData(
            array(
            'listing_product_id' => $childListingProduct->getId(),
            'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'type'               => Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_ADDED,
            'initiator'          => Ess_M2ePro_Model_Listing::INSTRUCTION_INITIATOR_ADDING_PRODUCT,
            'priority'           => 70,
            )
        );
        $instruction->save();

        if ($this->isCacheEnabled()) {
            $this->_childListingsProducts[$childListingProduct->getId()] = $childListingProduct;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartChildListingProduct */
        $walmartChildListingProduct = $childListingProduct->getChildObject();

        $childTypeModel = $walmartChildListingProduct->getVariationManager()->getTypeModel();

        if (!empty($productOptions)) {
            $productVariation = $this->getListingProduct()
                ->getMagentoProduct()
                ->getVariationInstance()
                ->getVariationTypeStandard($productOptions);

            $childTypeModel->setProductVariation($productVariation);
            $childTypeModel->setChannelVariation($channelOptions);
        }

        return $childListingProduct;
    }

    /**
     * @param $listingProductId
     * @return bool
     */
    public function removeChildListingProduct($listingProductId)
    {
        $childListingsProducts = $this->getChildListingsProducts();
        if (!isset($childListingsProducts[$listingProductId])) {
            return false;
        }

        if (!$childListingsProducts[$listingProductId]->deleteInstance()) {
            return false;
        }

        if ($this->isCacheEnabled()) {
            unset($this->_childListingsProducts[$listingProductId]);
        }

        return true;
    }

    //########################################

    public function clearTypeData()
    {
        parent::clearTypeData();

        $additionalData = $this->getListingProduct()->getAdditionalData();

        unset($additionalData['variation_channel_theme']);
        unset($additionalData['is_variation_channel_theme_set_manually']);
        unset($additionalData['variation_channel_theme_product_attributes_snapshot']);

        unset($additionalData['variation_matched_attributes']);
        unset($additionalData['variation_virtual_product_attributes']);
        unset($additionalData['variation_virtual_channel_attributes']);
        unset($additionalData['variation_channel_attributes']);
        unset($additionalData['variation_channel_variations']);
        unset($additionalData['variation_removed_product_variations']);

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $this->getListingProduct()->setData('variation_parent_need_processor', 0);
        $this->getListingProduct()->save();

        foreach ($this->getChildListingsProducts() as $childListingProduct) {

            /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager $childVariationManager */
            $childVariationManager = $childListingProduct->getChildObject()->getVariationManager();

            if ($this->getMagentoProduct()->isProductWithVariations()) {
                $childVariationManager->getTypeModel()->unsetChannelVariation();
                $childVariationManager->setIndividualType();
            } else {
                $childVariationManager->setSimpleType();
            }

            $childListingProduct->save();
        }
    }

    //########################################

    public function getRealMagentoAttributes()
    {
        $magentoVariations = $this->getRealMagentoVariations();
        return array_keys($magentoVariations['set']);
    }

    public function getRealMagentoVariations()
    {
        $this->getMagentoProduct()->setIgnoreVariationVirtualAttributes(true);
        $this->getMagentoProduct()->setIgnoreVariationFilterAttributes(true);

        $magentoVariations = $this->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();

        $this->getMagentoProduct()->setIgnoreVariationVirtualAttributes(false);
        $this->getMagentoProduct()->setIgnoreVariationFilterAttributes(false);

        return $magentoVariations;
    }

    //########################################

    public function setSwatchImagesAttribute($attribute, $save = true)
    {
        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_swatch_images_attribute', $attribute
        );
        $save && $this->getListingProduct()->save();
    }

    public function getSwatchImagesAttribute()
    {
        return $this->getListingProduct()->getSetting(
            'additional_data', 'variation_swatch_images_attribute'
        );
    }

    //########################################
}
