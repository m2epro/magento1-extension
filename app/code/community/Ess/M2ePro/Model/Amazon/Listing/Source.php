<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $listing Ess_M2ePro_Model_Listing
     */
    private $listing = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing $listing
     * @return $this
     */
    public function setListing(Ess_M2ePro_Model_Listing $listing)
    {
        $this->listing = $listing;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing
     */
    public function getAmazonListing()
    {
        return $this->getListing()->getChildObject();
    }

    //########################################

    /**
     * @return string
     */
    public function getSku()
    {
        $result = '';
        $src = $this->getAmazonListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_DEFAULT) {
            $result = $this->getMagentoProduct()->getSku();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_PRODUCT_ID) {
            $result = $this->getMagentoProduct()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);

        if (!empty($result)) {
            return $this->applySkuModification($result);
        }

        return $result;
    }

    // ---------------------------------------

    protected function applySkuModification($sku)
    {
        if ($this->getAmazonListing()->isSkuModificationModeNone()) {
            return $sku;
        }

        $source = $this->getAmazonListing()->getSkuModificationSource();

        if ($this->getAmazonListing()->isSkuModificationModePrefix()) {
            $sku = $source['value'] . $sku;
        } elseif ($this->getAmazonListing()->isSkuModificationModePostfix()) {
            $sku = $sku . $source['value'];
        } elseif ($this->getAmazonListing()->isSkuModificationModeTemplate()) {
            $sku = str_replace('%value%', $sku, $source['value']);
        }

        return $sku;
    }

    // ---------------------------------------

    /**
     * @return mixed
     */
    public function getSearchGeneralId()
    {
        $result = '';
        $src = $this->getAmazonListing()->getGeneralIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::GENERAL_ID_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = str_replace('-','',$result);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    /**
     * @return mixed
     */
    public function getSearchWorldwideId()
    {
        $result = '';
        $src = $this->getAmazonListing()->getWorldwideIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::WORLDWIDE_ID_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = str_replace('-','',$result);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    //########################################

    /**
     * @return int|string
     */
    public function getHandlingTime()
    {
        $result = 0;
        $src = $this->getAmazonListing()->getHandlingTimeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_RECOMMENDED) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $result = (int)$result;
        $result < 0  && $result = 0;
        $result > 30 && $result = 30;

        return $result;
    }

    /**
     * @return string
     */
    public function getRestockDate()
    {
        $result = '';
        $src = $this->getAmazonListing()->getRestockDateSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::RESTOCK_DATE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getCondition()
    {
        $result = '';
        $src = $this->getAmazonListing()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    /**
     * @return string
     */
    public function getConditionNote()
    {
        $result = '';
        $src = $this->getAmazonListing()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $renderer = Mage::helper('M2ePro/Module_Renderer_Description');
            $result = $renderer->parseTemplate($src['value'], $this->getMagentoProduct());
        }

        return trim($result);
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getMainImageLink()
    {
        $imageLink = '';

        if ($this->getAmazonListing()->isImageMainModeProduct()) {
            $imageLink = $this->getMagentoProduct()->getImageLink('image');
        }

        if ($this->getAmazonListing()->isImageMainModeAttribute()) {
            $src = $this->getAmazonListing()->getImageMainSource();
            $imageLink = $this->getMagentoProduct()->getImageLink($src['attribute']);
        }

        return $imageLink;
    }

    /**
     * @return array|string
     */
    public function getGalleryImages()
    {
        if ($this->getAmazonListing()->isImageMainModeNone()) {
            return array();
        }

        $allowedConditionValues = array(
            Ess_M2ePro_Model_Amazon_Listing::CONDITION_USED_LIKE_NEW,
            Ess_M2ePro_Model_Amazon_Listing::CONDITION_USED_VERY_GOOD,
            Ess_M2ePro_Model_Amazon_Listing::CONDITION_USED_GOOD,
            Ess_M2ePro_Model_Amazon_Listing::CONDITION_USED_ACCEPTABLE,
            Ess_M2ePro_Model_Amazon_Listing::CONDITION_COLLECTIBLE_LIKE_NEW,
            Ess_M2ePro_Model_Amazon_Listing::CONDITION_COLLECTIBLE_VERY_GOOD,
            Ess_M2ePro_Model_Amazon_Listing::CONDITION_COLLECTIBLE_GOOD,
            Ess_M2ePro_Model_Amazon_Listing::CONDITION_COLLECTIBLE_ACCEPTABLE
        );

        $conditionData = $this->getAmazonListing()->getConditionSource();

        if ($this->getAmazonListing()->isConditionDefaultMode() &&
            !in_array($conditionData['value'], $allowedConditionValues)) {
            return array();
        }

        if ($this->getAmazonListing()->isConditionAttributeMode()) {
            $tempConditionValue = $this->getMagentoProduct()->getAttributeValue($conditionData['attribute']);

            if (!in_array($tempConditionValue, $allowedConditionValues)) {
                return array();
            }
        }

        $mainImage = $this->getMainImageLink();

        if ($mainImage == '') {
            return array();
        }

        $mainImage = array($mainImage);

        if ($this->getAmazonListing()->isGalleryImagesModeNone()) {
            return $mainImage;
        }

        $galleryImages = array();
        $gallerySource = $this->getAmazonListing()->getGalleryImagesSource();
        $limitGalleryImages = Ess_M2ePro_Model_Amazon_Listing::GALLERY_IMAGES_COUNT_MAX;

        if ($this->getAmazonListing()->isGalleryImagesModeProduct()) {

            $limitGalleryImages = (int)$gallerySource['limit'];
            $galleryImages = $this->getMagentoProduct()->getGalleryImagesLinks($limitGalleryImages + 1);
        }

        if ($this->getAmazonListing()->isGalleryImagesModeAttribute()) {

            $limitGalleryImages = Ess_M2ePro_Model_Amazon_Listing::GALLERY_IMAGES_COUNT_MAX;
            $galleryImagesTemp = $this->getMagentoProduct()->getAttributeValue($gallerySource['attribute']);

            $galleryImagesTemp = (array)explode(',', $galleryImagesTemp);
            foreach ($galleryImagesTemp as $tempImageLink) {

                $tempImageLink = trim($tempImageLink);
                if (!empty($tempImageLink)) {
                    $galleryImages[] = $tempImageLink;
                }
            }
        }

        $galleryImages = array_unique($galleryImages);

        if (count($galleryImages) <= 0) {
            return $mainImage;
        }

        $mainImagePosition = array_search($mainImage[0], $galleryImages);
        if ($mainImagePosition !== false) {
            unset($galleryImages[$mainImagePosition]);
        }

        $galleryImages = array_slice($galleryImages,0,$limitGalleryImages);
        return array_merge($mainImage, $galleryImages);
    }

    // ---------------------------------------

    /**
     * @return mixed
     */
    public function getGiftWrap()
    {
        $result = NULL;
        $src = $this->getAmazonListing()->getGiftWrapSource();

        if ($this->getAmazonListing()->isGiftWrapModeYes()) {
            $result = true;
        }

        if ($this->getAmazonListing()->isGiftWrapModeNo()) {
            $result = false;
        }

        if ($this->getAmazonListing()->isGiftWrapModeAttribute()) {
            $attributeValue = $this->getMagentoProduct()->getAttributeValue($src['attribute']);

            if ($attributeValue == Mage::helper('M2ePro')->__('Yes')) {
                $result = true;
            }

            if ($attributeValue == Mage::helper('M2ePro')->__('No')) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @return null|bool
     */
    public function getGiftMessage()
    {
        $result = NULL;
        $src = $this->getAmazonListing()->getGiftMessageSource();

        if ($this->getAmazonListing()->isGiftMessageModeYes()) {
            $result = true;
        }

        if ($this->getAmazonListing()->isGiftMessageModeNo()) {
            $result = false;
        }

        if ($this->getAmazonListing()->isGiftMessageModeAttribute()) {
            $attributeValue = $this->getMagentoProduct()->getAttributeValue($src['attribute']);

            if ($attributeValue == Mage::helper('M2ePro')->__('Yes')) {
                $result = true;
            }

            if ($attributeValue == Mage::helper('M2ePro')->__('No')) {
                $result = false;
            }
        }

        return $result;
    }

    //########################################
}