<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Source
{
    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var $_listing Ess_M2ePro_Model_Listing
     */
    protected $_listing = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->_magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->_magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing $listing
     * @return $this
     */
    public function setListing(Ess_M2ePro_Model_Listing $listing)
    {
        $this->_listing = $listing;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->_listing;
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
            $result = null;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = str_replace('-', '', $result);
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
            $result = null;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = str_replace('-', '', $result);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    //########################################

    /**
     * @return int|null
     */
    public function getHandlingTime()
    {
        $src = $this->getAmazonListing()->getHandlingTimeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_NONE) {
            return null;
        }

        $result = 0;

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_RECOMMENDED) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = filter_var($result, FILTER_VALIDATE_INT);

            if ($result === false) {
                return null;
            }
        }

        $result = (int)$result;

        if ($result < 0) {
            return 0;
        }

        if ($result > 30) {
            return 30;
        }

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
     * @return Ess_M2ePro_Model_Magento_Product_Image|null
     */
    public function getMainImage()
    {
        $image = null;

        if ($this->getAmazonListing()->isImageMainModeProduct()) {
            $image = $this->getMagentoProduct()->getImage('image');
        }

        if ($this->getAmazonListing()->isImageMainModeAttribute()) {
            $src = $this->getAmazonListing()->getImageMainSource();
            $image = $this->getMagentoProduct()->getImage($src['attribute']);
        }

        return $image;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Image[]
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

        if (!$mainImage = $this->getMainImage()) {
            return array();
        }

        if ($this->getAmazonListing()->isGalleryImagesModeNone()) {
            return array($mainImage);
        }

        $galleryImages = array();
        $gallerySource = $this->getAmazonListing()->getGalleryImagesSource();
        $limitGalleryImages = Ess_M2ePro_Model_Amazon_Listing::GALLERY_IMAGES_COUNT_MAX;

        if ($this->getAmazonListing()->isGalleryImagesModeProduct()) {
            $limitGalleryImages = (int)$gallerySource['limit'];
            $galleryImagesTemp = $this->getMagentoProduct()->getGalleryImages($limitGalleryImages + 1);

            foreach ($galleryImagesTemp as $image) {
                if (array_key_exists($image->getHash(), $galleryImages)) {
                    continue;
                }

                $galleryImages[$image->getHash()] = $image;
            }
        }

        if ($this->getAmazonListing()->isGalleryImagesModeAttribute()) {
            $limitGalleryImages = Ess_M2ePro_Model_Amazon_Listing::GALLERY_IMAGES_COUNT_MAX;

            $galleryImagesTemp = $this->getMagentoProduct()->getAttributeValue($gallerySource['attribute']);
            $galleryImagesTemp = (array)explode(',', $galleryImagesTemp);

            foreach ($galleryImagesTemp as $tempImageLink) {
                $tempImageLink = trim($tempImageLink);
                if (empty($tempImageLink)) {
                    continue;
                }

                $image = new Ess_M2ePro_Model_Magento_Product_Image($tempImageLink);
                $image->setStoreId($this->getMagentoProduct()->getStoreId());

                if (array_key_exists($image->getHash(), $galleryImages)) {
                    continue;
                }

                $galleryImages[$image->getHash()] = $image;
            }
        }

        unset($galleryImages[$mainImage->getHash()]);

        if (empty($galleryImages)) {
            return array($mainImage);
        }

        $galleryImages = array_slice($galleryImages, 0, $limitGalleryImages);
        array_unshift($galleryImages, $mainImage);

        return $galleryImages;
    }

    // ---------------------------------------

    /**
     * @return mixed
     */
    public function getGiftWrap()
    {
        $result = null;
        $src = $this->getAmazonListing()->getGiftWrapSource();

        if ($this->getAmazonListing()->isGiftWrapModeYes()) {
            $result = true;
        }

        if ($this->getAmazonListing()->isGiftWrapModeNo()) {
            $result = false;
        }

        if ($this->getAmazonListing()->isGiftWrapModeAttribute()) {
            $attributeValue = $this->getMagentoProduct()->getAttributeValue($src['attribute'], false);

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
        $result = null;
        $src = $this->getAmazonListing()->getGiftMessageSource();

        if ($this->getAmazonListing()->isGiftMessageModeYes()) {
            $result = true;
        }

        if ($this->getAmazonListing()->isGiftMessageModeNo()) {
            $result = false;
        }

        if ($this->getAmazonListing()->isGiftMessageModeAttribute()) {
            $attributeValue = $this->getMagentoProduct()->getAttributeValue($src['attribute'], false);

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
