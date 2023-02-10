<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Description_Source
{
    const GALLERY_IMAGES_COUNT_MAX = 23;
    const VARIATION_IMAGES_COUNT_MAX = 12;

    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var $_descriptionTemplateModel Ess_M2ePro_Model_Template_Description
     */
    protected $_descriptionTemplateModel = null;

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
     * @param Ess_M2ePro_Model_Template_Description $instance
     * @return $this
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
        $this->_descriptionTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->_descriptionTemplateModel;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getEbayDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    //########################################

    /**
     * @return string
     */
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

    /**
     * @return string
     */
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

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception
     */
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
                $description = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                    $src['template'], $this->getMagentoProduct()
                );
                $this->addWatermarkForCustomDescription($description);
                break;

            default:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;
        }

        return str_replace(array('<![CDATA[', ']]>'), '', $description);
    }

    //########################################

    /**
     * @return int|string
     */
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

    /**
     * @return string
     */
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

    // ---------------------------------------

    public function getProductDetail($type)
    {
        if (!$this->getEbayDescriptionTemplate()->isProductDetailsModeAttribute($type)) {
            return null;
        }

        $attribute = $this->getEbayDescriptionTemplate()->getProductDetailAttribute($type);

        if (!$attribute) {
            return null;
        }

        return $this->getMagentoProduct()->getAttributeValue($attribute);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Image|null
     */
    public function getMainImage()
    {
        $image = null;

        if ($this->getEbayDescriptionTemplate()->isImageMainModeProduct()) {
            $image = $this->getMagentoProduct()->getImage('image');
        }

        if ($this->getEbayDescriptionTemplate()->isImageMainModeAttribute()) {
            $src = $this->getEbayDescriptionTemplate()->getImageMainSource();
            $image = $this->getMagentoProduct()->getImage($src['attribute']);
        }

        if ($image) {
            $this->addWatermarkIfNeed($image);
        }

        return $image;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Image[]
     */
    public function getGalleryImages()
    {
        if ($this->getEbayDescriptionTemplate()->isImageMainModeNone()) {
            return array();
        }

        if (!$mainImage = $this->getMainImage()) {
            $defaultImageUrl = $this->getEbayDescriptionTemplate()->getDefaultImageUrl();
            if (empty($defaultImageUrl)) {
                return array();
            }

            $image = new Ess_M2ePro_Model_Magento_Product_Image($defaultImageUrl);
            $image->setStoreId($this->getMagentoProduct()->getStoreId());

            return array($image);
        }

        if ($this->getEbayDescriptionTemplate()->isGalleryImagesModeNone()) {
            return array($mainImage);
        }

        $galleryImages = array();
        $gallerySource = $this->getEbayDescriptionTemplate()->getGalleryImagesSource();
        $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;

        if ($this->getEbayDescriptionTemplate()->isGalleryImagesModeProduct()) {
            $limitGalleryImages = (int)$gallerySource['limit'];
            $galleryImagesTemp = $this->getMagentoProduct()->getGalleryImages((int)$gallerySource['limit']+1);

            foreach ($galleryImagesTemp as $image) {
                if (array_key_exists($image->getHash(), $galleryImages)) {
                    continue;
                }

                $galleryImages[$image->getHash()] = $image;
            }
        }

        if ($this->getEbayDescriptionTemplate()->isGalleryImagesModeAttribute()) {
            $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;

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

        if (empty($galleryImages)) {
            return array($mainImage);
        }

        foreach ($galleryImages as $key => $image) {
            /** @var Ess_M2ePro_Model_Magento_Product_Image $image */

            $this->addWatermarkIfNeed($image);

            if ($image->getHash() == $mainImage->getHash()) {
                unset($galleryImages[$key]);
            }
        }

        $galleryImages = array_slice($galleryImages, 0, $limitGalleryImages);
        array_unshift($galleryImages, $mainImage);

        return $galleryImages;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Image[]
     */
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
            $variationImagesTemp = $this->getMagentoProduct()->getGalleryImages((int)$variationSource['limit']);

            foreach ($variationImagesTemp as $image) {
                if (array_key_exists($image->getHash(), $variationImages)) {
                    continue;
                }

                $variationImages[$image->getHash()] = $image;
            }
        }

        if ($this->getEbayDescriptionTemplate()->isVariationImagesModeAttribute()) {
            $limitVariationImages = self::VARIATION_IMAGES_COUNT_MAX;

            $variationImagesTemp = $this->getMagentoProduct()->getAttributeValue($variationSource['attribute']);
            $variationImagesTemp = (array)explode(',', $variationImagesTemp);

            foreach ($variationImagesTemp as $tempImageLink) {
                $tempImageLink = trim($tempImageLink);
                if (empty($tempImageLink)) {
                    continue;
                }

                $image = new Ess_M2ePro_Model_Magento_Product_Image($tempImageLink);
                $image->setStoreId($this->getMagentoProduct()->getStoreId());

                if (array_key_exists($image->getHash(), $variationImages)) {
                    continue;
                }

                $variationImages[$image->getHash()] = $image;
            }
        }

        if (empty($variationImages)) {
            return array();
        }

        foreach ($variationImages as $image) {
            /** @var Ess_M2ePro_Model_Magento_Product_Image $image */
            $this->addWatermarkIfNeed($image);
        }

        return array_slice($variationImages, 0, $limitVariationImages);
    }

    //########################################

    /**
     * @param string $str
     * @param int $length
     * @return string
     */
    protected function cutLongTitles($str, $length = 80)
    {
        $str = trim($str);

        if ($str === '' || strlen($str) <= $length) {
            return $str;
        }

        return Mage::helper('core/string')->truncate($str, $length, '');
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product_Image $imageObj
     */
    public function addWatermarkIfNeed($imageObj)
    {
        if (!$this->getEbayDescriptionTemplate()->isWatermarkEnabled()) {
            return;
        }

        if (!$imageObj->isSelfHosted()) {
            return;
        }

        $fileExtension = pathinfo($imageObj->getPath(), PATHINFO_EXTENSION);
        $pathWithoutExtension = preg_replace('/\.'.$fileExtension.'$/', '', $imageObj->getPath());

        $markingImagePath = $pathWithoutExtension.'-'.$this->getEbayDescriptionTemplate()->getWatermarkHash()
                            .'.'.$fileExtension;

        if (is_file($markingImagePath)) {
            $currentTime = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            if (filemtime($markingImagePath) + Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_CACHE_TIME >
                $currentTime) {
                $imageObj->setPath($markingImagePath)
                         ->setUrl($imageObj->getUrlByPath())
                         ->resetHash();

                return;
            }

            @unlink($markingImagePath);
        }

        $prevMarkingImagePath = $pathWithoutExtension.'-'
                                .$this->getEbayDescriptionTemplate()->getWatermarkPreviousHash().'.'.$fileExtension;

        if (is_file($prevMarkingImagePath)) {
            @unlink($prevMarkingImagePath);
        }

        $varDir = new Ess_M2ePro_Model_VariablesDir(
            array(
            'child_folder' => 'ebay/template/description/watermarks'
            )
        );
        $watermarkPath = $varDir->getPath().$this->getEbayDescriptionTemplate()->getId().'.png';
        if (!is_file($watermarkPath)) {
            $varDir->create();
            @file_put_contents($watermarkPath, base64_decode($this->getEbayDescriptionTemplate()->getWatermarkImage()));
        }

        $watermarkPositions = array(
            Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_POSITION_TOP =>
                                                                Varien_Image_Adapter_Abstract::POSITION_TOP_RIGHT,
            Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_POSITION_MIDDLE =>
                                                                Varien_Image_Adapter_Abstract::POSITION_CENTER,
            Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_POSITION_BOTTOM =>
                                                                Varien_Image_Adapter_Abstract::POSITION_BOTTOM_RIGHT
        );

        $image = new Varien_Image($imageObj->getPath());
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

        if (!is_file($markingImagePath)) {
            return;
        }

        $imageObj->setPath($markingImagePath)
                 ->setUrl($imageObj->getUrlByPath())
                 ->resetHash();
    }

    protected function addWatermarkForCustomDescription(&$description)
    {
        if (strpos($description, 'm2e_watermark') !== false) {
            preg_match_all('/<(img|a) [^>]*\bm2e_watermark[^>]*>/i', $description, $tagsArr);

            $tags = $tagsArr[0];
            $tagsNames = $tagsArr[1];

            $count = count($tags);
            for ($i = 0; $i < $count; $i++) {
                $dom = new DOMDocument();
                $dom->loadHTML($tags[$i]);
                $tag = $dom->getElementsByTagName($tagsNames[$i])->item(0);

                $newTag = str_replace(' m2e_watermark="1"', '', $tags[$i]);
                if ($tagsNames[$i] === 'a') {
                    $imageUrl = $tag->getAttribute('href');

                    $image = new Ess_M2ePro_Model_Magento_Product_Image($imageUrl);
                    $image->setStoreId($this->getMagentoProduct()->getStoreId());
                    $this->addWatermarkIfNeed($image);

                    $newTag = str_replace($imageUrl, $image->getUrl(), $newTag);
                }

                if ($tagsNames[$i] === 'img') {
                    $imageUrl = $tag->getAttribute('src');

                    $image = new Ess_M2ePro_Model_Magento_Product_Image($imageUrl);
                    $image->setStoreId($this->getMagentoProduct()->getStoreId());
                    $this->addWatermarkIfNeed($image);

                    $newTag = str_replace($imageUrl, $image->getUrl(), $newTag);
                }

                $description = str_replace($tags[$i], $newTag, $description);
            }
        }
    }

    //########################################
}