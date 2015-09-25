<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Description_Source
{
    const GALLERY_IMAGES_COUNT_MAX = 11;
    const VARIATION_IMAGES_COUNT_MAX = 12;

    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $descriptionTemplateModel Ess_M2ePro_Model_Template_Description
     */
    private $descriptionTemplateModel = null;

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

    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
        $this->descriptionTemplateModel = $instance;
        return $this;
    }

    public function getDescriptionTemplate()
    {
        return $this->descriptionTemplateModel;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getEbayDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    // ########################################

    public function getTitle()
    {
        $title = '';
        $src = $this->getEbayDescriptionTemplate()->getTitleSource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Ebay_Template_Description::TITLE_MODE_PRODUCT:
                $title = $this->getMagentoProduct()->getName();
                break;

            case Ess_M2ePro_Model_Ebay_Template_Description::TITLE_MODE_CUSTOM:
                $title = Mage::helper('M2ePro/Module_Renderer_Description')
                    ->parseTemplate($src['template'], $this->getMagentoProduct());
                break;

            default:
                $title = $this->getMagentoProduct()->getName();
                break;
        }

        if ($this->getEbayDescriptionTemplate()->isCutLongTitles()) {
            $title = $this->cutLongTitles($title);
        }

        return $title;
    }

    public function getSubTitle()
    {
        $subTitle = '';
        $src = $this->getEbayDescriptionTemplate()->getSubTitleSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Description::SUBTITLE_MODE_CUSTOM) {

            $subTitle = Mage::helper('M2ePro/Module_Renderer_Description')
                ->parseTemplate($src['template'], $this->getMagentoProduct());

            if ($this->getEbayDescriptionTemplate()->isCutLongTitles()) {
                $subTitle = $this->cutLongTitles($subTitle, 55);
            }
        }

        return $subTitle;
    }

    public function getDescription()
    {
        $description = '';
        $src = $this->getEbayDescriptionTemplate()->getDescriptionSource();
        $templateProcessor = Mage::getModel('Core/Email_Template_Filter');

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_PRODUCT:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_SHORT:
                $description = $this->getMagentoProduct()->getProduct()->getShortDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_CUSTOM:
                $description = Mage::helper('M2ePro/Module_Renderer_Description')
                    ->parseTemplate($src['template'], $this->getMagentoProduct());
                $this->addWatermarkForCustomDescription($description);
                break;

            default:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;
        }

        return str_replace(array('<![CDATA[', ']]>'), '', $description);
    }

    // ########################################

    public function getCondition()
    {
        $src = $this->getEbayDescriptionTemplate()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getConditionNote()
    {
        $note = '';
        $src = $this->getEbayDescriptionTemplate()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_NOTE_MODE_CUSTOM) {
            $note = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                $src['template'], $this->getMagentoProduct()
            );
        }

        return $note;
    }

    // ----------------------------------------

    public function getProductDetail($type)
    {
        if (!$this->getEbayDescriptionTemplate()->isProductDetailsModeAttribute($type)) {
            return NULL;
        }

        $attribute = $this->getEbayDescriptionTemplate()->getProductDetailAttribute($type);

        if (!$attribute) {
            return NULL;
        }

        return $this->getMagentoProduct()->getAttributeValue($attribute);
    }

    // #######################################

    public function getMainImageLink()
    {
        $imageLink = '';

        if ($this->getEbayDescriptionTemplate()->isImageMainModeProduct()) {
            $imageLink = $this->getMagentoProduct()->getImageLink('image');
        }

        if ($this->getEbayDescriptionTemplate()->isImageMainModeAttribute()) {
            $src = $this->getEbayDescriptionTemplate()->getImageMainSource();
            $imageLink = $this->getMagentoProduct()->getImageLink($src['attribute']);
        }

        if (empty($imageLink)) {
            return $imageLink;
        }

        return $this->addWatermarkIfNeed($imageLink);
    }

    public function getGalleryImages()
    {
        if ($this->getEbayDescriptionTemplate()->isImageMainModeNone()) {
            return array();
        }

        $mainImage = $this->getMainImageLink();

        if ($mainImage == '') {
            $defaultImage = $this->getEbayDescriptionTemplate()->getDefaultImageUrl();
            if (!empty($defaultImage)) {
                return array($defaultImage);
            }

            return array();
        }

        $mainImage = array($mainImage);

        if ($this->getEbayDescriptionTemplate()->isGalleryImagesModeNone()) {
            return $mainImage;
        }

        $galleryImages = array();
        $gallerySource = $this->getEbayDescriptionTemplate()->getGalleryImagesSource();
        $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;

        if ($this->getEbayDescriptionTemplate()->isGalleryImagesModeProduct()) {
            $limitGalleryImages = (int)$gallerySource['limit'];
            $galleryImages = $this->getMagentoProduct()->getGalleryImagesLinks((int)$gallerySource['limit']+1);
        }

        if ($this->getEbayDescriptionTemplate()->isGalleryImagesModeAttribute()) {

            $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;

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

        foreach ($galleryImages as &$image) {
            $image = $this->addWatermarkIfNeed($image);
        }

        $mainImagePosition = array_search($mainImage[0], $galleryImages);
        if ($mainImagePosition !== false) {
            unset($galleryImages[$mainImagePosition]);
        }

        $galleryImages = array_slice($galleryImages,0,$limitGalleryImages);
        return array_merge($mainImage, $galleryImages);
    }

    public function getVariationImages()
    {
        if ($this->getEbayDescriptionTemplate()->isImageMainModeNone() ||
            $this->getEbayDescriptionTemplate()->isVariationImagesModeNone()) {
            return array();
        }

        $variationImages = array();
        $variationSource = $this->getEbayDescriptionTemplate()->getVariationImagesSource();
        $limitVariationImages = self::VARIATION_IMAGES_COUNT_MAX;

        if ($this->getEbayDescriptionTemplate()->isVariationImagesModeProduct()) {
            $limitVariationImages = (int)$variationSource['limit'];
            $variationImages = $this->getMagentoProduct()->getGalleryImagesLinks((int)$variationSource['limit']);
        }

        if ($this->getEbayDescriptionTemplate()->isVariationImagesModeAttribute()) {

            $limitVariationImages = self::VARIATION_IMAGES_COUNT_MAX;

            $variationImagesTemp = $this->getMagentoProduct()->getAttributeValue($variationSource['attribute']);
            $variationImagesTemp = (array)explode(',', $variationImagesTemp);

            foreach ($variationImagesTemp as $tempImageLink) {
                $tempImageLink = trim($tempImageLink);
                if (!empty($tempImageLink)) {
                    $variationImages[] = $tempImageLink;
                }
            }
        }

        $variationImages = array_unique($variationImages);

        if (count($variationImages) <= 0) {
            return array();
        }

        foreach ($variationImages as &$image) {
            $image = $this->addWatermarkIfNeed($image);
        }

        return array_slice($variationImages, 0, $limitVariationImages);
    }

    // ########################################

    private function cutLongTitles($str, $length = 80)
    {
        $str = trim($str);

        if ($str === '' || strlen($str) <= $length) {
            return $str;
        }

        return Mage::helper('core/string')->truncate($str, $length, '');
    }

    // ----------------------------------------

    private function imageLinkToPath($imageLink)
    {
        $imageLink = str_replace('%20', ' ', $imageLink);

        $baseMediaUrl = Mage::app()->getStore($this->getMagentoProduct()->getStoreId())
                                   ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false).'catalog/product';

        $imageLink = preg_replace('/^http(s)?:\/\//i', '', $imageLink);
        $baseMediaUrl = preg_replace('/^http(s)?:\/\//i', '', $baseMediaUrl);

        $baseMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        $imagePath = str_replace($baseMediaUrl, $baseMediaPath, $imageLink);
        $imagePath = str_replace('/', DS, $imagePath);
        $imagePath = str_replace('\\', DS, $imagePath);

        return $imagePath;
    }

    private function pathToImageLink($path)
    {
        $baseMediaUrl = Mage::app()->getStore($this->getMagentoProduct()->getStoreId())
                                   ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false).'catalog/product';

        $baseMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        $imageLink = str_replace($baseMediaPath, $baseMediaUrl, $path);
        $imageLink = str_replace(DS, '/', $imageLink);

        return str_replace(' ', '%20', $imageLink);
    }

    // ----------------------------------------

    public function addWatermarkIfNeed($imageLink)
    {
        if (!$this->getEbayDescriptionTemplate()->isWatermarkEnabled()) {
            return $imageLink;
        }

        $imagePath = $this->imageLinkToPath($imageLink);
        if (!is_file($imagePath)) {
            return $imageLink;
        }

        $fileExtension = pathinfo($imagePath, PATHINFO_EXTENSION);
        $pathWithoutExtension = preg_replace('/\.'.$fileExtension.'$/', '', $imagePath);

        $markingImagePath = $pathWithoutExtension.'-'.$this->getEbayDescriptionTemplate()->getWatermarkHash()
                            .'.'.$fileExtension;
        if (is_file($markingImagePath)) {
            $currentTime = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            if (filemtime($markingImagePath) + Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_CACHE_TIME >
                $currentTime) {
                return $this->pathToImageLink($markingImagePath);
            }

            @unlink($markingImagePath);
        }

        $prevMarkingImagePath = $pathWithoutExtension.'-'
                                .$this->getEbayDescriptionTemplate()->getWatermarkPreviousHash().'.'.$fileExtension;
        if (is_file($prevMarkingImagePath)) {
            @unlink($prevMarkingImagePath);
        }

        $varDir = new Ess_M2ePro_Model_VariablesDir(array(
            'child_folder' => 'ebay/template/description/watermarks'
        ));
        $watermarkPath = $varDir->getPath().$this->getEbayDescriptionTemplate()->getId().'.png';
        if (!is_file($watermarkPath)) {
            $varDir->create();
            @file_put_contents($watermarkPath, $this->getEbayDescriptionTemplate()->getWatermarkImage());
        }

        $watermarkPositions = array(
            Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_POSITION_TOP =>
                                                                Varien_Image_Adapter_Abstract::POSITION_TOP_RIGHT,
            Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_POSITION_MIDDLE =>
                                                                Varien_Image_Adapter_Abstract::POSITION_CENTER,
            Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_POSITION_BOTTOM =>
                                                                Varien_Image_Adapter_Abstract::POSITION_BOTTOM_RIGHT
        );

        $image = new Varien_Image($imagePath);
        $imageOriginalHeight = $image->getOriginalHeight();
        $imageOriginalWidth = $image->getOriginalWidth();
        $image->open();
        $image->setWatermarkPosition($watermarkPositions[$this->getEbayDescriptionTemplate()->getWatermarkPosition()]);

        $watermark = new Varien_Image($watermarkPath);
        $watermarkOriginalHeight = $watermark->getOriginalHeight();
        $watermarkOriginalWidth = $watermark->getOriginalWidth();

        if ($this->getEbayDescriptionTemplate()->isWatermarkScaleModeStretch()) {
            $image->setWatermarkPosition(Varien_Image_Adapter_Abstract::POSITION_STRETCH);
        }

        if ($this->getEbayDescriptionTemplate()->isWatermarkScaleModeInWidth()) {
            $watermarkWidth = $imageOriginalWidth;
            $heightPercent = $watermarkOriginalWidth / $watermarkWidth;
            $watermarkHeight = (int)($watermarkOriginalHeight / $heightPercent);

            $image->setWatermarkWidth($watermarkWidth);
            $image->setWatermarkHeigth($watermarkHeight);
        }

        if ($this->getEbayDescriptionTemplate()->isWatermarkScaleModeNone()) {
            $image->setWatermarkWidth($watermarkOriginalWidth);
            $image->setWatermarkHeigth($watermarkOriginalHeight);

            if ($watermarkOriginalHeight > $imageOriginalHeight) {
                $image->setWatermarkHeigth($imageOriginalHeight);
                $widthPercent = $watermarkOriginalHeight / $imageOriginalHeight;
                $watermarkWidth = (int)($watermarkOriginalWidth / $widthPercent);
                $image->setWatermarkWidth($watermarkWidth);
            }

            if ($watermarkOriginalWidth > $imageOriginalWidth) {
                $image->setWatermarkWidth($imageOriginalWidth);
                $heightPercent = $watermarkOriginalWidth / $imageOriginalWidth;
                $watermarkHeight = (int)($watermarkOriginalHeight / $heightPercent);
                $image->setWatermarkHeigth($watermarkHeight);
            }
        }

        $opacity = 100;
        if ($this->getEbayDescriptionTemplate()->isWatermarkTransparentEnabled()) {
            $opacity = 30;
        }

        $image->setWatermarkImageOpacity($opacity);
        $image->watermark($watermarkPath);
        $image->save($markingImagePath);

        return $this->pathToImageLink($markingImagePath);
    }

    private function addWatermarkForCustomDescription(&$description)
    {
        if (strpos($description, 'm2e_watermark') !== false) {
            preg_match_all('/<(img|a) [^>]*\bm2e_watermark[^>]*>/i', $description, $tagsArr);

            $tags = $tagsArr[0];
            $tagsNames = $tagsArr[1];

            $count = count($tags);
            for($i = 0; $i < $count; $i++){
                $dom = new DOMDocument();
                $dom->loadHTML($tags[$i]);
                $tag = $dom->getElementsByTagName($tagsNames[$i])->item(0);

                $newTag = str_replace(' m2e_watermark="1"', '', $tags[$i]);
                if($tagsNames[$i] === 'a') {
                    $newTag = str_replace($tag->getAttribute('href'),
                        $this->addWatermarkIfNeed($tag->getAttribute('href')), $newTag);
                }
                if($tagsNames[$i] === 'img') {
                    $newTag = str_replace($tag->getAttribute('src'),
                        $this->addWatermarkIfNeed($tag->getAttribute('src')), $newTag);
                }
                $description = str_replace($tags[$i], $newTag, $description);
            }
        }
    }

    // ########################################
}