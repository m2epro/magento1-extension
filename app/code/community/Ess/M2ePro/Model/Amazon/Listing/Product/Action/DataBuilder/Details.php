<?php

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Details
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Abstract
{
    public function getData()
    {
        $listingProduct = $this->getListingProduct();
        $amazonListingProduct = $listingProduct->getChildObject();

        $data = array();

        if ($amazonListingProduct->isGeneralIdOwner()) {
            $variationManager = $amazonListingProduct->getVariationManager();

            if (
                $variationManager->isRelationParentType()
                && !$this->isValidGeneralIdOwner($listingProduct)
            ) {
                return $data;
            }

            if ($variationManager->isRelationChildType()) {
                /** @var Ess_M2ePro_Model_Listing_Product $variationParent */
                $variationParent = Mage::getModel('M2ePro/Listing_Product')
                    ->load($variationManager->getVariationParentId());

                if (
                    !$variationParent->getId()
                    || !$this->isValidGeneralIdOwner($variationParent)
                ) {
                    return $data;
                }
            }
        }

        $data = array_merge($data, $this->getSpecifics());

        $listingProduct->getId();

        if (!$this->getVariationManager()->isRelationParentType()) {
            $data = array_merge(
                $data,
                $this->getGiftData()
            );
        }

        $data = array_merge($data, $this->getTaxCodeData());
        $conditionData = $this->getConditionData();

        if (!isset($data['attributes'])) {
            $data['attributes'] = array();
        }

        if (!isset($conditionData['attributes'])) {
            $conditionData['attributes'] = array();
        }


        $data['attributes'] = array_merge($data['attributes'], $conditionData['attributes']);
        unset($conditionData['attributes']);


        $data = array_merge($data, $conditionData);

        if (!$amazonListingProduct->isAfnChannel()) {
            $data = array_merge($data, $this->getShippingData());
        }

        return $data;
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function getSpecifics()
    {
        $listingProduct = $this->getListingProduct();
        $productType = $listingProduct->getChildObject()->getProductTypeTemplate();
        if ($productType === null || !$productType->getId()) {
            return array();
        }

        $this->searchNotFoundAttributes(); // clear previously not found attributes

        $result = array(
            'product_type_nick' => $productType->getNick(),
            'attributes' => $this->buildSpecificsData($productType->getSettings('settings')),
        );
        $this->processNotFoundAttributes('Product Specifics'); // add message about not found attributes

        return $result;
    }

    /**
     * @param array $specifics
     *
     * @return array
     */
    private function buildSpecificsData(array $specifics)
    {
        $result = array();
        foreach ($specifics as $name => $values) {
            if (empty($values)) {
                continue;
            }

            $finalValues = array();
            foreach ($values as $value) {
                if ($finalValue = $this->buildSingleSpecificData($value)) {
                    $finalValues[] = $finalValue;
                }
            }

            if (!empty($finalValues)) {
                $result[$name] = (count($finalValues) === 1) ?
                    $finalValues[0] : $finalValues;
            }
        }

        return $result;
    }

    /**
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function buildSingleSpecificData(array $setting)
    {
        $magentoProduct = $this->getAmazonListingProduct()->getActualMagentoProduct();
        if (!$magentoProduct->exists()) {
            return null;
        }

        switch ((int)$setting['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_ProductType::FIELD_CUSTOM_VALUE:
                /** @var \Ess_M2ePro_Helper_Module_Renderer_Description $renderer */
                $renderer = Mage::helper('M2ePro/Module_Renderer_Description');
                return $renderer->parseWithoutMagentoTemplate($setting['value'], $magentoProduct);
            case Ess_M2ePro_Model_Amazon_Template_ProductType::FIELD_CUSTOM_ATTRIBUTE:
                $attributeValue = $magentoProduct->getAttributeValue($setting['attribute_code'], false);

                if (!empty($setting['images_limit'])) {
                    $imagesList = explode(',', $attributeValue);
                    $imagesList = array_slice($imagesList, 0, (int)$setting['images_limit']);
                    $attributeValue = implode(',', $imagesList);
                }

                return $attributeValue;
        }

        return null;
    }

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
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getGiftData()
    {
        $giftWrap = $this->getAmazonListingProduct()->getListingSource()->getGiftWrap();
        $giftMessage = $this->getAmazonListingProduct()->getListingSource()->getGiftMessage();

        $isOnlineGiftSettingsDisabled = $this->getListingProduct()->getSetting(
            'additional_data',
            'online_gift_settings_disabled',
            true
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

    /**
     * @return array
     */
    private function getShippingData()
    {
        $amazonListingProduct = $this->getAmazonListingProduct();
        $amazonListing = $this->getAmazonListing();

        /** @var Ess_M2ePro_Model_Amazon_Template_Shipping|null $shippingTemplate */
        $shippingTemplate = $amazonListingProduct->isExistShippingTemplate()
            ? $amazonListingProduct->getShippingTemplate()
            : $amazonListing->getShippingTemplate();

        if (
            $amazonListingProduct->isAfnChannel()
            || $shippingTemplate === null
        ) {
            return array();
        }

        return array(
            'shipping_data' => array(
                'template_name' => $shippingTemplate->getTemplateId()
            ),
        );
    }

    /**
     * @return array
     */
    protected function getTaxCodeData()
    {
        if (
            !$this->getAmazonMarketplace()->isProductTaxCodePolicyAvailable()
            || !$this->getAmazonAccount()->isVatCalculationServiceEnabled()
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

    private function isValidGeneralIdOwner(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $additionalData = $listingProduct->getAdditionalData();
        if (
            empty($additionalData['variation_channel_theme'])
            || empty($additionalData['variation_matched_attributes'])
        ) {
            return false;
        }

        return true;
    }
}
