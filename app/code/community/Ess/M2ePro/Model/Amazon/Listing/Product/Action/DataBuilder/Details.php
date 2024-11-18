<?php

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Details
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Abstract
{
    public function getData()
    {
        $listingProduct = $this->getListingProduct();
        $amazonListingProduct = $listingProduct->getChildObject();

        $data = $this->getListPrice();

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

            $specificKeys = array(
                Ess_M2ePro_Helper_Component_Amazon_ProductType::SPECIFIC_KEY_NAME,
                Ess_M2ePro_Helper_Component_Amazon_ProductType::SPECIFIC_KEY_DESCRIPTION,
                Ess_M2ePro_Helper_Component_Amazon_ProductType::SPECIFIC_KEY_BULLET_POINT,
            );

            if (in_array($name, $specificKeys)) {
                $fieldData = $this->prepareFieldValue($values);

                if ($fieldData !== null && $fieldData !== '') {
                    $result[$name] = $fieldData;
                }
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
        switch ((int)$setting['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_ProductType::FIELD_CUSTOM_VALUE:
                return $setting['value'];
            case Ess_M2ePro_Model_Amazon_Template_ProductType::FIELD_CUSTOM_ATTRIBUTE:
                $magentoProduct = $this->getAmazonListingProduct()
                    ->getActualMagentoProduct();
                if (!$magentoProduct->exists()) {
                    return null;
                }

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

    private function getListPrice()
    {
        if ($this->getAmazonListingProduct()->isGeneralIdOwner()) {
            return array();
        }

        $variationManager = $this->getAmazonListingProduct()->getVariationManager();
        if ($variationManager->isVariationParent()) {
            return array();
        }

        $productTypeTemplate = $this->getAmazonListingProduct()->getProductTypeTemplate();
        if (
            $productTypeTemplate !== null
            && $productTypeTemplate->getNick()
            !== Ess_M2ePro_Model_Amazon_Template_ProductType::GENERAL_PRODUCT_TYPE_NICK
        ) {
            return array();
        }

        $regularListPrice = $this->getAmazonListingProduct()->getRegularPrice();
        if ($regularListPrice <= 0) {
            return array();
        }

        return array('list_price' => $regularListPrice);
    }

    protected function prepareFieldValue(array $fieldSpecifications)
    {
        $magentoProduct = $this->getAmazonListingProduct()
            ->getActualMagentoProduct();
        if (!$magentoProduct->exists()) {
            return null;
        }

        $resultData = array();

        foreach ($fieldSpecifications as $item) {
            if ($item['mode'] === Ess_M2ePro_Model_Amazon_Template_ProductType::FIELD_CUSTOM_VALUE) {
                $resultData[] = $this->replaceAttributesInValue($magentoProduct, $item['value']);
            } else {
                $resultData[] = $magentoProduct->getAttributeValue($item['attribute_code'], false);
            }
        }

        if (count($resultData) === 1) {
            return reset($resultData);
        }

        return $resultData;
    }

    private function replaceAttributesInValue($magentoProduct, $value)
    {
        preg_match_all("/#([a-z_0-9]+?)#/i", $value, $matches);

        if (empty($matches[0])) {
            return $value;
        }

        foreach ($matches[1] as $attributeCode) {
            $attributeValue = $magentoProduct->getAttributeValue($attributeCode);
            $value = str_replace("#$attributeCode#", $attributeValue, $value);
        }

        return $value;
    }
}