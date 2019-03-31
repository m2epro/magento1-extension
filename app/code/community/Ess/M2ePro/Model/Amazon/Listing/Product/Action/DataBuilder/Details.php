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
    private $descriptionTemplate = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Definition
     */
    private $definitionTemplate = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source
     */
    private $definitionSource = NULL;

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

            if (isset($data['gift_wrap']) || isset($data['gift_message']) || isset($data['shipping_data'])) {

                $data['description_data']['title'] = $this->getAmazonListingProduct()
                                                          ->getMagentoProduct()
                                                          ->getName();
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
    private function getConditionData()
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
    private function getGiftData()
    {
        $giftWrap    = $this->getAmazonListingProduct()->getListingSource()->getGiftWrap();
        $giftMessage = $this->getAmazonListingProduct()->getListingSource()->getGiftMessage();

        $isOnlineGiftSettingsDisabled = $this->getListingProduct()->getSetting(
            'additional_data', 'online_gift_settings_disabled'
        );

        if ($isOnlineGiftSettingsDisabled && $giftWrap === false && $giftMessage === false) {
            return array();
        }

        $data = array();

        if (!is_null($giftWrap)) {
            $data['gift_wrap'] = $giftWrap;
        }

        if (!is_null($giftMessage)) {
            $data['gift_message'] = $giftMessage;
        }

        return $data;
    }

    // ---------------------------------------

    /**
     * @return array
     */
    private function getDescriptionData()
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

        $this->searchNotFoundAttributes();
        $data['msrp_rrp'] = $this->getDefinitionSource()->getMsrpRrp($this->getListing()->getStoreId());
        $this->processNotFoundAttributes('MSRP / RRP');

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

        if (is_null($data['package_weight']) || $data['package_weight'] === '' ||
            $data['package_weight_unit_of_measure'] === ''
        ) {
            unset(
                $data['package_weight'],
                $data['package_weight_unit_of_measure']
            );
        }

        if (is_null($data['shipping_weight']) || $data['shipping_weight'] === '' ||
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
    private function getProductData()
    {
        $data = array();

        $this->searchNotFoundAttributes();

        foreach ($this->getDescriptionTemplate()->getSpecifics(true) as $specific) {

            $source = $specific->getSource($this->getAmazonListingProduct()->getActualMagentoProduct());

            if (!$specific->isRequired() && !$specific->isModeNone() && !$source->getValue()) {
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
    private function getShippingData()
    {
        if ($this->getAmazonListingProduct()->isAfnChannel() ||
            !$this->getAmazonListingProduct()->isExistShippingTemplate()
        ) {
            return array();
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
    private function getTaxCodeData()
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
    private function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplate)) {
            $this->descriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();
        }
        return $this->descriptionTemplate;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Definition
     */
    private function getDefinitionTemplate()
    {
        if (is_null($this->definitionTemplate)) {
            $this->definitionTemplate = $this->getDescriptionTemplate()->getDefinitionTemplate();
        }
        return $this->definitionTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source
     */
    private function getDefinitionSource()
    {
        if (is_null($this->definitionSource)) {
            $this->definitionSource = $this->getDefinitionTemplate()
                ->getSource($this->getAmazonListingProduct()->getActualMagentoProduct());
        }
        return $this->definitionSource;
    }

    //########################################
}