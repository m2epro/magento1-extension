<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Details
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description
     */
    protected $_descriptionTemplate = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Definition
     */
    protected $_definitionTemplate = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source
     */
    protected $_definitionSource = null;

    //########################################

    public function getData()
    {
        $data = array();

        if (!$this->getVariationManager()->isRelationParentType()) {
            $data = array_merge(
                $data,
                $this->getConditionData(),
                $this->getGiftData()
            );
        }

        $data = array_merge($data, $this->getTaxCodeData());

        if (!$this->getAmazonListingProduct()->isAfnChannel()) {
            $data = array_merge($data, $this->getShippingData());
        }

        $isUseDescriptionTemplate = false;

        do {
            if (!$this->getAmazonListingProduct()->isExistDescriptionTemplate()) {
                break;
            }

            $variationManager = $this->getAmazonListingProduct()->getVariationManager();

            if (($variationManager->isRelationChildType() || $variationManager->isIndividualType()) &&
                ($this->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
                 $this->getMagentoProduct()->isBundleType() ||
                 $this->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks())) {
                break;
            }

            $isUseDescriptionTemplate = true;
        } while (false);

        if (!$isUseDescriptionTemplate) {
            $descriptionData = $this->getDescriptionDataWithoutDescriptionTemplate($data);
            if (!empty($descriptionData)) {
                $data['description_data'] = $descriptionData;
            }

            return $data;
        }

        $data = array_merge($data, $this->getDescriptionData());

        $data['number_of_items']       = $this->getDefinitionSource()->getNumberOfItems();
        $data['item_package_quantity'] = $this->getDefinitionSource()->getItemPackageQuantity();

        $browsenodeId = $this->getDescriptionTemplate()->getBrowsenodeId();
        if (empty($browsenodeId)) {
            return $data;
        }

        // browsenode_id requires description_data
        $data['browsenode_id'] = $browsenodeId;

        return array_merge(
            $data,
            $this->getProductData()
        );
    }

    //########################################

    /**
     * @return array
     */
    protected function getConditionData()
    {
        $condition = array();

        $this->searchNotFoundAttributes();
        $condition['condition'] = $this->getAmazonListingProduct()->getListingSource()->getCondition();
        $this->processNotFoundAttributes('Condition');

        if ($condition['condition'] != Ess_M2ePro_Model_Amazon_Listing::CONDITION_NEW) {
            $this->searchNotFoundAttributes();
            $condition['condition_note'] = $this->getAmazonListingProduct()->getListingSource()->getConditionNote();
            $this->processNotFoundAttributes('Condition Note');
        }

        return $condition;
    }

    /**
     * @return array
     */
    protected function getGiftData()
    {
        $giftWrap    = $this->getAmazonListingProduct()->getListingSource()->getGiftWrap();
        $giftMessage = $this->getAmazonListingProduct()->getListingSource()->getGiftMessage();

        $isOnlineGiftSettingsDisabled = $this->getListingProduct()->getSetting(
            'additional_data', 'online_gift_settings_disabled', true
        );

        if ($isOnlineGiftSettingsDisabled && $giftWrap === false && $giftMessage === false) {
            return array();
        }

        $data = array();

        if ($giftWrap !== null) {
            $data['gift_wrap'] = $giftWrap;
        }

        if ($giftMessage !== null) {
            $data['gift_message'] = $giftMessage;
        }

        return $data;
    }

    // ---------------------------------------

    /**
     * @param array $data
     * @return array[]
     * @throws \Ess_M2ePro_Model_Exception_Logic
     */
    private function getDescriptionDataWithoutDescriptionTemplate(array $data)
    {
        $descriptionData = array();

        $listPrice = $this->findListPrice(
            $this->getListing()->getStoreId(),
            $this->getAmazonListingProduct()
        );
        if ($listPrice !== null) {
            $descriptionData['msrp_rrp'] = $listPrice;
        }

        // If description_data is not empty, then title must be filled to pass validation on the server worker
        if (!empty($descriptionData)
            || isset($data['gift_wrap'])
            || isset($data['gift_message'])
            || isset($data['shipping_data'])
        ) {
            $descriptionData['title'] = $this->getAmazonListingProduct()
                ->getMagentoProduct()
                ->getName();
        }

        return $descriptionData;
    }

    /**
     * @return array
     */
    protected function getDescriptionData()
    {
        $source = $this->getDefinitionSource();

        $data = array(
            'brand'                    => $source->getBrand(),

            'manufacturer'             => $source->getManufacturer(),
            'manufacturer_part_number' => $source->getManufacturerPartNumber(),
        );

        $this->searchNotFoundAttributes();
        $data['title'] = $this->getDefinitionSource()->getTitle();
        $this->processNotFoundAttributes('Title');

        $data['msrp_rrp'] = $this->findSuggestedRetailPrice(
            $this->getListing()->getStoreId(),
            $this->getAmazonListingProduct()
        );

        $this->searchNotFoundAttributes();
        $data['description'] = $this->getDefinitionSource()->getDescription();
        $this->processNotFoundAttributes('Description');

        $this->searchNotFoundAttributes();
        $data['bullet_points'] = $this->getDefinitionSource()->getBulletPoints();
        $this->processNotFoundAttributes('Bullet Points');

        $this->searchNotFoundAttributes();
        $data['search_terms'] = $this->getDefinitionSource()->getSearchTerms();
        $this->processNotFoundAttributes('Search Terms');

        $this->searchNotFoundAttributes();
        $data['target_audience'] = $this->getDefinitionSource()->getTargetAudience();
        $this->processNotFoundAttributes('Target Audience');

        $this->searchNotFoundAttributes();
        $data['item_dimensions_volume'] = $source->getItemDimensionsVolume();
        $this->processNotFoundAttributes('Product Dimensions Volume');

        $this->searchNotFoundAttributes();
        $data['item_dimensions_volume_unit_of_measure'] = $source->getItemDimensionsVolumeUnitOfMeasure();
        $this->processNotFoundAttributes('Product Dimensions Measure Units');

        $this->searchNotFoundAttributes();
        $data['item_dimensions_weight'] = $source->getItemDimensionsWeight();
        $this->processNotFoundAttributes('Product Dimensions Weight');

        $this->searchNotFoundAttributes();
        $data['item_dimensions_weight_unit_of_measure'] = $source->getItemDimensionsWeightUnitOfMeasure();
        $this->processNotFoundAttributes('Product Dimensions Weight Units');

        $this->searchNotFoundAttributes();
        $data['package_dimensions_volume'] = $source->getPackageDimensionsVolume();
        $this->processNotFoundAttributes('Package Dimensions Volume');

        $this->searchNotFoundAttributes();
        $data['package_dimensions_volume_unit_of_measure'] = $source->getPackageDimensionsVolumeUnitOfMeasure();
        $this->processNotFoundAttributes('Package Dimensions Measure Units');

        $this->searchNotFoundAttributes();
        $data['package_weight'] = $source->getPackageWeight();
        $this->processNotFoundAttributes('Package Weight');

        $this->searchNotFoundAttributes();
        $data['package_weight_unit_of_measure'] = $source->getPackageWeightUnitOfMeasure();
        $this->processNotFoundAttributes('Package Weight Units');

        $this->searchNotFoundAttributes();
        $data['shipping_weight'] = $source->getShippingWeight();
        $this->processNotFoundAttributes('Shipping Weight');

        $this->searchNotFoundAttributes();
        $data['shipping_weight_unit_of_measure'] = $source->getShippingWeightUnitOfMeasure();
        $this->processNotFoundAttributes('Shipping Weight Units');

        if ($data['package_weight'] === null || $data['package_weight'] === '' ||
            $data['package_weight_unit_of_measure'] === ''
        ) {
            unset(
                $data['package_weight'],
                $data['package_weight_unit_of_measure']
            );
        }

        if ($data['shipping_weight'] === null || $data['shipping_weight'] === '' ||
            $data['shipping_weight_unit_of_measure'] === ''
        ) {
            unset(
                $data['shipping_weight'],
                $data['shipping_weight_unit_of_measure']
            );
        }

        if (!$this->getVariationManager()->isRelationParentType()) {
            return array(
                'description_data' => $data
            );
        }

        if (in_array('', $data['item_dimensions_volume']) || $data['item_dimensions_volume_unit_of_measure'] === '') {
            unset(
                $data['item_dimensions_volume'],
                $data['item_dimensions_volume_unit_of_measure']
            );
        }

        if ($data['item_dimensions_weight'] === '' || $data['item_dimensions_weight_unit_of_measure'] === '') {
            unset(
                $data['item_dimensions_weight'],
                $data['item_dimensions_weight_unit_of_measure']
            );
        }

        if (in_array('', $data['package_dimensions_volume']) ||
            $data['package_dimensions_volume_unit_of_measure'] === ''
        ) {
            unset(
                $data['package_dimensions_volume'],
                $data['package_dimensions_volume_unit_of_measure']
            );
        }

        return array(
            'description_data' => $data
        );
    }

    // ---------------------------------------

    /**
     * @return array
     */
    protected function getProductData()
    {
        $data = array();

        $this->searchNotFoundAttributes();

        foreach ($this->getDescriptionTemplate()->getSpecifics(true) as $specific) {
            $source = $specific->getSource($this->getAmazonListingProduct()->getActualMagentoProduct());

            if (!$specific->isRequired() && !$specific->isModeNone()
                && ($source->getValue() === null || $source->getValue() === '' || $source->getValue() === array())) {
                continue;
            }

            $data = Mage::helper('M2ePro')->arrayReplaceRecursive(
                $data, Mage::helper('M2ePro')->jsonDecode($source->getPath())
            );
        }

        $this->processNotFoundAttributes('Product Specifics');

        return array(
            'product_data'      => $data,
            'product_data_nick' => $this->getDescriptionTemplate()->getProductDataNick(),
        );
    }

    /**
     * @return array
     */
    protected function getShippingData()
    {
        if ($this->getAmazonListingProduct()->isAfnChannel() ||
            !$this->getAmazonListingProduct()->isExistShippingTemplate() &&
            !$this->getAmazonListing()->isExistShippingTemplate()
        ) {
            return array();
        }

        if (!$this->getAmazonListingProduct()->isExistShippingTemplate()) {
            return array(
                'shipping_data' => array(
                    'template_name' => $this->getAmazonListing()->getShippingTemplateSource(
                        $this->getAmazonListingProduct()->getActualMagentoProduct()
                    )->getTemplateName(),
                )
            );
        }

        return array(
            'shipping_data' => array(
                'template_name' => $this->getAmazonListingProduct()->getShippingTemplateSource()->getTemplateName(),
            )
        );
    }

    /**
     * @return array
     */
    protected function getTaxCodeData()
    {
        if (!$this->getAmazonMarketplace()->isProductTaxCodePolicyAvailable() ||
            !$this->getAmazonAccount()->isVatCalculationServiceEnabled()
        ) {
            return array();
        }

        if (!$this->getAmazonListingProduct()->isExistProductTaxCodeTemplate()) {
            return array();
        }

        $data = array();

        $this->searchNotFoundAttributes();

        $data['tax_code'] = $this->getAmazonListingProduct()->getProductTaxCodeTemplateSource()->getProductTaxCode();

        $this->processNotFoundAttributes('Product Tax Code');

        return $data;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description
     */
    protected function getDescriptionTemplate()
    {
        if ($this->_descriptionTemplate === null) {
            $this->_descriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();
        }

        return $this->_descriptionTemplate;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Definition
     */
    protected function getDefinitionTemplate()
    {
        if ($this->_definitionTemplate === null) {
            $this->_definitionTemplate = $this->getDescriptionTemplate()->getDefinitionTemplate();
        }

        return $this->_definitionTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source
     */
    protected function getDefinitionSource()
    {
        if ($this->_definitionSource === null) {
            $this->_definitionSource = $this->getDefinitionTemplate()
                                            ->getSource($this->getAmazonListingProduct()->getActualMagentoProduct());
        }

        return $this->_definitionSource;
    }

    // ---------------------------------------

    /**
     * @param int $storeId
     * @param \Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct
     * @return float|null
     * @throws \Ess_M2ePro_Model_Exception_Logic
     */
    private function findSuggestedRetailPrice($storeId, Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct)
    {
        $listPrice = $this->findListPrice($storeId, $amazonListingProduct);
        if ($listPrice !== null) {
            return $listPrice;
        }

        $this->searchNotFoundAttributes();
        $msrpRrp = $this->getDefinitionSource()->getMsrpRrp($storeId);
        $this->processNotFoundAttributes('MSRP / RRP');

        return $msrpRrp;
    }

    /**
     * @param int $storeId
     * @param Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct
     * @return float|null
     * @throws \Ess_M2ePro_Model_Exception_Logic
     */
    private function findListPrice($storeId, Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct)
    {
        $sellingTemplate = $amazonListingProduct->getAmazonSellingFormatTemplate();

        if ($sellingTemplate->isListPriceModeNone()) {
            return null;
        }

        $attribute = $sellingTemplate->getListPriceAttribute();
        if (empty($attribute)) {
            return null;
        }

        $defaultCurrency = $amazonListingProduct
            ->getMarketplace()
            ->getChildObject()
            ->getDefaultCurrency();

        /** @var Ess_M2ePro_Helper_Magento_Attribute $magentoAttributeHelper */
        $magentoAttributeHelper = Mage::helper('M2ePro/Magento_Attribute');

        $this->searchNotFoundAttributes();
        $attributeValue = $magentoAttributeHelper->convertAttributeTypePriceFromStoreToMarketplace(
            $amazonListingProduct->getMagentoProduct(),
            $attribute,
            $defaultCurrency,
            $storeId
        );
        $this->processNotFoundAttributes('List Price Attribute');

        if (empty($attributeValue)) {
            return 0.00;
        }

        if (is_string($attributeValue)) {
            $attributeValue = str_replace(',', '.', $attributeValue);
        }

        return round((float)$attributeValue, 2);
    }

    // ---------------------------------------
}
