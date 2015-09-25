<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Template_NewProduct_Core_Source
{
    const ADDITIONAL_IMAGES_COUNT_MAX = 4;

    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $newProductSpecificTemplateModel Ess_M2ePro_Model_Buy_Template_NewProduct_Core
     */
    private $newProductCoreTemplateModel = null;

    // ########################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ----------------------------------------

    public function setNewProductCoreTemplate(Ess_M2ePro_Model_Buy_Template_NewProduct_Core $instance)
    {
        $this->newProductCoreTemplateModel = $instance;
        return $this;
    }

    public function getNewProductCoreTemplate()
    {
        return $this->newProductCoreTemplateModel;
    }

    // ########################################

    public function getSellerSku()
    {
        $src = $this->getNewProductCoreTemplate()->getSellerSkuSource();
        return $this->getMagentoProduct()->getAttributeValue($src['custom_attribute']);
    }

    public function getGtin()
    {
        $gtin = NULL;

        if ($this->getNewProductCoreTemplate()->isGtinCustomAttribute()) {
            $gtin = $this->getMagentoProduct()->getAttributeValue(
                $this->getNewProductCoreTemplate()->getGtinCustomAttribute()
            );
        }

        return $gtin;
    }

    public function getIsbn()
    {
        $isbn = NULL;

        if ($this->getNewProductCoreTemplate()->isIsbnCustomAttribute()) {
            $isbn = $this->getMagentoProduct()->getAttributeValue(
                $this->getNewProductCoreTemplate()->getIsbnCustomAttribute()
            );
        }

        return $isbn;
    }

    public function getAsin()
    {
        $asin = NULL;

        if ($this->getNewProductCoreTemplate()->isAsinCustomAttribute()) {
            $asin = $this->getMagentoProduct()->getAttributeValue(
                $this->getNewProductCoreTemplate()->getAsinCustomAttribute()
            );
        }

        return $asin;
    }

    public function getMfgName()
    {
        $mfgName = NULL;
        $src = $this->getNewProductCoreTemplate()->getMfgSource();

        if ($src['template'] != '') {
            $mfgName = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                $src['template'], $this->getMagentoProduct()
            );
        }

        is_string($mfgName) && trim($mfgName);

        return $mfgName;
    }

    public function getMfgPartNumber()
    {
        $mfgPartNumber = null;
        $src = $this->getNewProductCoreTemplate()->getMfgPartNumberSource();

        if ($this->getNewProductCoreTemplate()->isMfgPartNumberCustomValue()) {
            $mfgPartNumber = $src['custom_value'];
        }

        if ($this->getNewProductCoreTemplate()->isMfgPartNumberCustomAttribute()) {
            $mfgPartNumber = $this->getMagentoProduct()->getAttributeValue($src['custom_attribute']);
        }

        return $mfgPartNumber;
    }

    public function getProductSetId()
    {
        $productSetId = NULL;
        $src = $this->getNewProductCoreTemplate()->getProductSetIdSource();

        if ($this->getNewProductCoreTemplate()->isProductSetIdCustomValue()) {
            $productSetId = $src['custom_value'];
        }

        if ($this->getNewProductCoreTemplate()->isProductSetIdCustomAttribute()) {
            $productSetId = $this->getMagentoProduct()->getAttributeValue($src['custom_attribute']);
        }

        return $productSetId;
    }

    public function getTitle()
    {
        $src = $this->getNewProductCoreTemplate()->getTitleSource();

        switch ($src['mode']) {

            case Ess_M2ePro_Model_Buy_Template_NewProduct_Core::TITLE_MODE_CUSTOM_TEMPLATE:
                $title = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                    $src['template'], $this->getMagentoProduct()
                );
                break;

            case Ess_M2ePro_Model_Buy_Template_NewProduct_Core::TITLE_MODE_PRODUCT_NAME:
            default:
                $title = $this->getMagentoProduct()->getName();
                break;
        }

        is_string($title) && trim($title);

        return $title;
    }

    public function getDescription()
    {
        $src = $this->getNewProductCoreTemplate()->getDescriptionSource();

        /* @var $templateProcessor Mage_Core_Model_Email_Template_Filter */
        $templateProcessor = Mage::getModel('Core/Email_Template_Filter');

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Buy_Template_NewProduct_Core::DESCRIPTION_MODE_PRODUCT_FULL:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Buy_Template_NewProduct_Core::DESCRIPTION_MODE_PRODUCT_SHORT:
                $description = $this->getMagentoProduct()->getProduct()->getShortDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Buy_Template_NewProduct_Core::DESCRIPTION_MODE_CUSTOM_TEMPLATE:
                $description = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                    $src['template'], $this->getMagentoProduct()
                );
                break;

            default:
                $description = '';
                break;
        }

        $description = str_replace(array('<![CDATA[', ']]>'), '', $description);
        $description = preg_replace('/[^(\x20-\x7F)]*/','', $description);

        return trim(strip_tags($description));
    }

    public function getMainImage()
    {
        $imageLink = NULL;

        if ($this->getNewProductCoreTemplate()->isMainImageBroductBase()) {
            $imageLink = $this->getMagentoProduct()->getImageLink('image');
        }

        if ($this->getNewProductCoreTemplate()->isMainImageAttribute()) {
            $src = $this->getNewProductCoreTemplate()->getMainImageSource();
            $imageLink = $this->getMagentoProduct()->getImageLink($src['attribute']);
        }

        return trim($imageLink);
    }

    public function getAdditionalImages()
    {
        $limitImages = self::ADDITIONAL_IMAGES_COUNT_MAX;
        $src = $this->getNewProductCoreTemplate()->getAdditionalImageSource();

        $galleryImages = array();

        if ($this->getNewProductCoreTemplate()->isAdditionalImageNone()) {
            return NULL;
        }

        if ($this->getNewProductCoreTemplate()->isAdditionalImageProduct()) {
            $limitImages = (int)$src['limit'];
            $galleryImages = $this->getMagentoProduct()->getGalleryImagesLinks((int)$src['limit']);
        }

        if ($this->getNewProductCoreTemplate()->isAdditionalImageCustomAttribute()) {
            $galleryImagesTemp = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $galleryImagesTemp = (array)explode(',', $galleryImagesTemp);

            foreach ($galleryImagesTemp as $tempImageLink) {
                $tempImageLink = trim($tempImageLink);
                if (!empty($tempImageLink)) {
                    $galleryImages[] = $tempImageLink;
                }
            }
        }

        $mainImageLink = $this->getMagentoProduct()->getImageLink('image');

        $isMainImageInArray = array_search($mainImageLink,$galleryImages);
        if ($isMainImageInArray !== false) {
            unset($galleryImages[$isMainImageInArray]);
        }

        $galleryImages = array_unique($galleryImages);
        if (count($galleryImages) <= 0) {
            return NULL;
        }

        $galleryImages = array_slice($galleryImages,0,$limitImages);

        return implode('|',$galleryImages);
    }

    public function getFeatures()
    {
        $features = NULL;

        if ($this->getNewProductCoreTemplate()->isFeaturesCustomTemplate()) {

            $src = $this->getNewProductCoreTemplate()->getFeaturesSource();
            foreach ($src['template'] as $feature) {
                $features[] = trim(strip_tags(
                    Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                        $feature, $this->getMagentoProduct()
                    )
                ));
            }

            $features = implode('|',$features);
            $features = preg_replace('/[^(\x20-\x7F)]*/','', $features);
        }

        return $features;
    }

    public function getKeywords()
    {
        $keywords = '';

        if ($this->getNewProductCoreTemplate()->isKeywordsNone()) {
            return NULL;
        }

        $src = $this->getNewProductCoreTemplate()->getKeywordsSource();

        if ($this->getNewProductCoreTemplate()->isKeywordsCustomValue()) {
            $keywords = $src['custom_value'];
        }

        if ($this->getNewProductCoreTemplate()->isKeywordsCustomAttribute()) {
            $keywords = $this->getMagentoProduct()->getAttributeValue($src['custom_attribute']);
        }

        $keywords = preg_replace('/(?<=,)\s/','',$keywords);
        $keywords = strip_tags(str_replace(',','|',$keywords));
        $keywords = preg_replace('/[^(\x20-\x7F)]*/','',$keywords);

        return $keywords;
    }

    public function getWeight()
    {
        $weight = '';
        $src = $this->getNewProductCoreTemplate()->getWeightSource();

        if ($this->getNewProductCoreTemplate()->isWeightCustomValue()) {
            $weight = $src['custom_value'];
        }

        if ($this->getNewProductCoreTemplate()->isWeightCustomAttribute()) {
            $weight = $this->getMagentoProduct()->getAttributeValue($src['custom_attribute']);
        }

        $weight = str_replace(',','.',$weight);
        $weight = round((float)$weight,2);

        return $weight;
    }

    // ########################################
}