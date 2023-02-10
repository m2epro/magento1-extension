<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Template_Description getParentObject()
 * @method Ess_M2ePro_Model_Resource_Ebay_Template_Description getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Description extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const TITLE_MODE_PRODUCT = 0;
    const TITLE_MODE_CUSTOM  = 1;

    const SUBTITLE_MODE_NONE   = 0;
    const SUBTITLE_MODE_CUSTOM = 1;

    const DESCRIPTION_MODE_PRODUCT = 0;
    const DESCRIPTION_MODE_SHORT   = 1;
    const DESCRIPTION_MODE_CUSTOM  = 2;

    const CONDITION_MODE_EBAY      = 0;
    const CONDITION_MODE_ATTRIBUTE = 1;
    const CONDITION_MODE_NONE      = 2;

    const CONDITION_EBAY_NEW                        = 1000;
    const CONDITION_EBAY_NEW_OTHER                  = 1500;
    const CONDITION_EBAY_NEW_WITH_DEFECT            = 1750;
    const CONDITION_EBAY_CERTIFIED_REFURBISHED      = 2000;
    const CONDITION_EBAY_EXCELLENT_REFURBISHED      = 2010;
    const CONDITION_EBAY_VERY_GOOD_REFURBISHED      = 2020;
    const CONDITION_EBAY_GOOD_REFURBISHED           = 2030;
    const CONDITION_EBAY_SELLER_REFURBISHED         = 2500;
    const CONDITION_EBAY_LIKE_NEW                   = 2750;
    const CONDITION_EBAY_USED                       = 3000;
    const CONDITION_EBAY_VERY_GOOD                  = 4000;
    const CONDITION_EBAY_GOOD                       = 5000;
    const CONDITION_EBAY_ACCEPTABLE                 = 6000;
    const CONDITION_EBAY_NOT_WORKING                = 7000;

    const CONDITION_NOTE_MODE_NONE   = 0;
    const CONDITION_NOTE_MODE_CUSTOM = 1;

    const EDITOR_TYPE_SIMPLE  = 0;
    const EDITOR_TYPE_TINYMCE = 1;

    const CUT_LONG_TITLE_DISABLED = 0;
    const CUT_LONG_TITLE_ENABLED  = 1;

    const PRODUCT_DETAILS_MODE_NONE           = 0;
    const PRODUCT_DETAILS_MODE_DOES_NOT_APPLY = 1;
    const PRODUCT_DETAILS_MODE_ATTRIBUTE      = 2;

    const GALLERY_TYPE_EMPTY    = 4;
    const GALLERY_TYPE_NO       = 0;
    const GALLERY_TYPE_PICTURE  = 1;
    const GALLERY_TYPE_PLUS     = 2;
    const GALLERY_TYPE_FEATURED = 3;

    const IMAGE_MAIN_MODE_NONE      = 0;
    const IMAGE_MAIN_MODE_PRODUCT   = 1;
    const IMAGE_MAIN_MODE_ATTRIBUTE = 2;

    const GALLERY_IMAGES_MODE_NONE      = 0;
    const GALLERY_IMAGES_MODE_PRODUCT   = 1;
    const GALLERY_IMAGES_MODE_ATTRIBUTE = 2;

    const VARIATION_IMAGES_MODE_NONE      = 0;
    const VARIATION_IMAGES_MODE_PRODUCT   = 1;
    const VARIATION_IMAGES_MODE_ATTRIBUTE = 2;

    const USE_SUPERSIZE_IMAGES_NO  = 0;
    const USE_SUPERSIZE_IMAGES_YES = 1;

    const WATERMARK_MODE_NO  = 0;
    const WATERMARK_MODE_YES = 1;

    const WATERMARK_POSITION_TOP    = 0;
    const WATERMARK_POSITION_MIDDLE = 1;
    const WATERMARK_POSITION_BOTTOM = 2;

    const WATERMARK_SCALE_MODE_NONE     = 0;
    const WATERMARK_SCALE_MODE_IN_WIDTH = 1;
    const WATERMARK_SCALE_MODE_STRETCH  = 2;

    const WATERMARK_TRANSPARENT_MODE_NO  = 0;
    const WATERMARK_TRANSPARENT_MODE_YES = 1;

    const WATERMARK_CACHE_TIME     = 604800; // 7 days

    const INSTRUCTION_TYPE_MAGENTO_STATIC_BLOCK_IN_DESCRIPTION_CHANGED = 'magento_static_block_in_description_changed';

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Description_Source[]
     */
    protected $_descriptionSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Description');
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION;
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Ebay_Listing')
                ->getCollection()
                ->addFieldToFilter('template_description_id', $this->getId())
                ->getSize() ||
            (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                ->getCollection()
                ->addFieldToFilter(
                    'template_description_mode',
                    Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                )
                ->addFieldToFilter('template_description_id', $this->getId())
                ->getSize();
    }

    public function deleteInstance()
    {
        // Delete watermark if exists
        // ---------------------------------------
        $varDir = new Ess_M2ePro_Model_VariablesDir(
            array('child_folder' => 'ebay/template/description/watermarks')
        );

        $watermarkPath = $varDir->getPath() . $this->getId() . '.png';
        if (is_file($watermarkPath)) {
            @unlink($watermarkPath);
        }

        // ---------------------------------------

        $temp = parent::deleteInstance();
        $temp && $this->_descriptionSourceModels = array();

        return $temp;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_Description_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->_descriptionSourceModels[$productId])) {
            return $this->_descriptionSourceModels[$productId];
        }

        $this->_descriptionSourceModels[$productId] = Mage::getModel('M2ePro/Ebay_Template_Description_Source');
        $this->_descriptionSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->_descriptionSourceModels[$productId]->setDescriptionTemplate($this->getParentObject());

        return $this->_descriptionSourceModels[$productId];
    }

    //########################################

    /**
     * @return bool
     */
    public function isCustomTemplate()
    {
        return (bool)$this->getData('is_custom_template');
    }

    //########################################

    /**
     * @return int
     */
    public function getTitleMode()
    {
        return (int)$this->getData('title_mode');
    }

    /**
     * @return bool
     */
    public function isTitleModeProduct()
    {
        return $this->getTitleMode() == self::TITLE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isTitleModeCustom()
    {
        return $this->getTitleMode() == self::TITLE_MODE_CUSTOM;
    }

    /**
     * @return array
     */
    public function getTitleSource()
    {
        return array(
            'mode'     => $this->getTitleMode(),
            'template' => $this->getData('title_template')
        );
    }

    /**
     * @return array
     */
    public function getTitleAttributes()
    {
        $attributes = array();
        $src = $this->getTitleSource();

        if ($src['mode'] == self::TITLE_MODE_PRODUCT) {
            $attributes[] = 'name';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getSubTitleMode()
    {
        return (int)$this->getData('subtitle_mode');
    }

    /**
     * @return bool
     */
    public function isSubTitleModeProduct()
    {
        return $this->getSubTitleMode() == self::SUBTITLE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isSubTitleModeCustom()
    {
        return $this->getSubTitleMode() == self::SUBTITLE_MODE_CUSTOM;
    }

    /**
     * @return array
     */
    public function getSubTitleSource()
    {
        return array(
            'mode'     => $this->getSubTitleMode(),
            'template' => $this->getData('subtitle_template')
        );
    }

    /**
     * @return array
     */
    public function getSubTitleAttributes()
    {
        $attributes = array();
        $src = $this->getSubTitleSource();

        if ($src['mode'] == self::SUBTITLE_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getDescriptionMode()
    {
        return (int)$this->getData('description_mode');
    }

    /**
     * @return bool
     */
    public function isDescriptionModeProduct()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isDescriptionModeShort()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_SHORT;
    }

    /**
     * @return bool
     */
    public function isDescriptionModeCustom()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_CUSTOM;
    }

    /**
     * @return array
     */
    public function getDescriptionSource()
    {
        return array(
            'mode'     => $this->getDescriptionMode(),
            'template' => $this->getData('description_template')
        );
    }

    /**
     * @return array
     */
    public function getDescriptionAttributes()
    {
        $attributes = array();
        $src = $this->getDescriptionSource();

        if ($src['mode'] == self::DESCRIPTION_MODE_PRODUCT) {
            $attributes[] = 'description';
        } elseif ($src['mode'] == self::DESCRIPTION_MODE_SHORT) {
            $attributes[] = 'short_description';
        } else {
            preg_match_all('/#([a-zA-Z_0-9]+?)#|#(image|media_gallery)\[.*\]#+?/', $src['template'], $match);
            !empty($match[0]) && $attributes = array_filter(array_merge($match[1], $match[2]));
        }

        return $attributes;
    }

    //########################################

    /**
     * @return array
     */
    public function getConditionSource()
    {
        return array(
            'mode'      => (int)$this->getData('condition_mode'),
            'value'     => (int)$this->getData('condition_value'),
            'attribute' => $this->getData('condition_attribute')
        );
    }

    /**
     * @return array
     */
    public function getConditionAttributes()
    {
        $attributes = array();
        $src = $this->getConditionSource();

        if ($src['mode'] == self::CONDITION_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getConditionNoteSource()
    {
        return array(
            'mode'     => (int)$this->getData('condition_note_mode'),
            'template' => $this->getData('condition_note_template')
        );
    }

    /**
     * @return array
     */
    public function getConditionNoteAttributes()
    {
        $attributes = array();
        $src = $this->getConditionNoteSource();

        if ($src['mode'] == self::CONDITION_NOTE_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getProductDetails()
    {
        return $this->getSettings('product_details');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isProductDetailsIncludeEbayDetails()
    {
        $productDetails = $this->getProductDetails();

        return isset($productDetails['include_ebay_details']) ? (bool)$productDetails['include_ebay_details'] : true;
    }

    /**
     * @return bool
     */
    public function isProductDetailsIncludeImage()
    {
        $productDetails = $this->getProductDetails();

        return isset($productDetails['include_image']) ? (bool)$productDetails['include_image'] : true;
    }

    // ---------------------------------------

    /**
     * @param int $type
     * @return bool
     */
    public function isProductDetailsModeNone($type)
    {
        return $this->getProductDetailsMode($type) == self::PRODUCT_DETAILS_MODE_NONE;
    }

    /**
     * @param int $type
     * @return bool
     */
    public function isProductDetailsModeDoesNotApply($type)
    {
        return $this->getProductDetailsMode($type) == self::PRODUCT_DETAILS_MODE_DOES_NOT_APPLY;
    }

    /**
     * @param int $type
     * @return bool
     */
    public function isProductDetailsModeAttribute($type)
    {
        return $this->getProductDetailsMode($type) == self::PRODUCT_DETAILS_MODE_ATTRIBUTE;
    }

    public function getProductDetailsMode($type)
    {
        if (!in_array($type, array('isbn', 'epid', 'upc', 'ean', 'brand', 'mpn'))) {
            throw new InvalidArgumentException('Unknown Product details name');
        }

        $productDetails = $this->getProductDetails();

        if (!is_array($productDetails) || !isset($productDetails[$type]) ||
            !isset($productDetails[$type]['mode'])) {
            return null;
        }

        return $productDetails[$type]['mode'];
    }

    public function getProductDetailAttribute($type)
    {
        if (!in_array($type, array('isbn', 'epid', 'upc', 'ean', 'brand', 'mpn'))) {
            throw new InvalidArgumentException('Unknown Product details name');
        }

        $productDetails = $this->getProductDetails();

        if (!is_array($productDetails) || !isset($productDetails[$type]) ||
            $this->isProductDetailsModeNone($type) || !isset($productDetails[$type]['attribute'])) {
            return null;
        }

        return $productDetails[$type]['attribute'];
    }

    /**
     * @return array
     */
    public function getProductDetailAttributes()
    {
        $attributes = array();

        $temp = $this->getProductDetailAttribute('isbn');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('epid');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('upc');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('ean');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('brand');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('mpn');
        $temp && $attributes[] = $temp;

        return $attributes;
    }

    //########################################

    /**
     * @return bool
     */
    public function isCutLongTitles()
    {
        return (bool)$this->getData('cut_long_titles');
    }

    /**
     * @return array
     */
    public function getEnhancements()
    {
        return $this->getData('enhancement') ? explode(',', $this->getData('enhancement')) : array();
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getEditorType()
    {
        return (int)$this->getData('editor_type');
    }

    /**
     * @return bool
     */
    public function isEditorTypeSimple()
    {
        return $this->getEditorType() == self::EDITOR_TYPE_SIMPLE;
    }

    /**
     * @return bool
     */
    public function isEditorTypeTinyMce()
    {
        return $this->getEditorType() == self::EDITOR_TYPE_TINYMCE;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getGalleryType()
    {
        return (int)$this->getData('gallery_type');
    }

    /**
     * @return bool
     */
    public function isGalleryTypeEmpty()
    {
        return $this->getGalleryType() == self::GALLERY_TYPE_EMPTY;
    }

    /**
     * @return bool
     */
    public function isGalleryTypeNo()
    {
        return $this->getGalleryType() == self::GALLERY_TYPE_NO;
    }

    /**
     * @return bool
     */
    public function isGalleryTypePicture()
    {
        return $this->getGalleryType() == self::GALLERY_TYPE_PICTURE;
    }

    /**
     * @return bool
     */
    public function isGalleryTypeFeatured()
    {
        return $this->getGalleryType() == self::GALLERY_TYPE_FEATURED;
    }

    /**
     * @return bool
     */
    public function isGalleryTypePlus()
    {
        return $this->getGalleryType() == self::GALLERY_TYPE_PLUS;
    }

    //########################################

    /**
     * @return int
     */
    public function getImageMainMode()
    {
        return (int)$this->getData('image_main_mode');
    }

    /**
     * @return bool
     */
    public function isImageMainModeNone()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isImageMainModeProduct()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isImageMainModeAttribute()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getImageMainSource()
    {
        return array(
            'mode'      => $this->getImageMainMode(),
            'attribute' => $this->getData('image_main_attribute')
        );
    }

    /**
     * @return array
     */
    public function getImageMainAttributes()
    {
        $attributes = array();
        $src = $this->getImageMainSource();

        if ($src['mode'] == self::IMAGE_MAIN_MODE_PRODUCT) {
            $attributes[] = 'image';
        } elseif ($src['mode'] == self::IMAGE_MAIN_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getGalleryImagesMode()
    {
        return (int)$this->getData('gallery_images_mode');
    }

    /**
     * @return bool
     */
    public function isGalleryImagesModeNone()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isGalleryImagesModeProduct()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isGalleryImagesModeAttribute()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getGalleryImagesSource()
    {
        return array(
            'mode'      => $this->getGalleryImagesMode(),
            'attribute' => $this->getData('gallery_images_attribute'),
            'limit'     => $this->getData('gallery_images_limit')
        );
    }

    /**
     * @return array
     */
    public function getGalleryImagesAttributes()
    {
        $attributes = array();
        $src = $this->getGalleryImagesSource();

        if ($src['mode'] == self::GALLERY_IMAGES_MODE_PRODUCT) {
            $attributes[] = 'media_gallery';
        } elseif ($src['mode'] == self::GALLERY_IMAGES_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getVariationImagesMode()
    {
        return (int)$this->getData('variation_images_mode');
    }

    /**
     * @return bool
     */
    public function isVariationImagesModeNone()
    {
        return $this->getVariationImagesMode() == self::VARIATION_IMAGES_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isVariationImagesModeProduct()
    {
        return $this->getVariationImagesMode() == self::VARIATION_IMAGES_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isVariationImagesModeAttribute()
    {
        return $this->getVariationImagesMode() == self::VARIATION_IMAGES_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getVariationImagesSource()
    {
        return array(
            'mode'      => $this->getVariationImagesMode(),
            'attribute' => $this->getData('variation_images_attribute'),
            'limit'     => $this->getData('variation_images_limit')
        );
    }

    /**
     * @return array
     */
    public function getVariationImagesAttributes()
    {
        $attributes = array();
        $src = $this->getVariationImagesSource();

        if ($src['mode'] == self::VARIATION_IMAGES_MODE_PRODUCT) {
            $attributes[] = 'media_gallery';
        } elseif ($src['mode'] == self::VARIATION_IMAGES_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getDefaultImageUrl()
    {
        return $this->getData('default_image_url');
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getDecodedVariationConfigurableImages()
    {
        return Mage::helper('M2ePro')->jsonDecode($this->getData('variation_configurable_images'));
    }

    /**
     * @return bool
     */
    public function isVariationConfigurableImages()
    {
        $images = $this->getDecodedVariationConfigurableImages();

        return !empty($images);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isUseSupersizeImagesEnabled()
    {
        return (bool)$this->getData('use_supersize_images');
    }

    //########################################

    /**
     * @return bool
     */
    public function isWatermarkEnabled()
    {
        return (bool)$this->getData('watermark_mode');
    }

    public function getWatermarkImage()
    {
        return $this->getData('watermark_image');
    }

    public function getWatermarkHash()
    {
        $settingNamePath = array(
            'hashes',
            'current'
        );

        return $this->getSetting('watermark_settings', $settingNamePath);
    }

    public function getWatermarkPreviousHash()
    {
        $settingNamePath = array(
            'hashes',
            'previous'
        );

        return $this->getSetting('watermark_settings', $settingNamePath);
    }

    public function updateWatermarkHashes()
    {
        $settings = $this->getSettings('watermark_settings');

        if (isset($settings['hashes']['current'])) {
            $settings['hashes']['previous'] = $settings['hashes']['current'];
        } else {
            $settings['hashes']['previous'] = '';
        }

        $settings['hashes']['current'] = substr(sha1(microtime()), 0, 5);

        $this->setSettings('watermark_settings', $settings);

        return $this;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getWatermarkPosition()
    {
        return (int)$this->getSetting('watermark_settings', 'position');
    }

    /**
     * @return int
     */
    public function getWatermarkScaleMode()
    {
        return (int)$this->getSetting('watermark_settings', 'scale');
    }

    /**
     * @return int
     */
    public function getWatermarkTransparentMode()
    {
        return (int)$this->getSetting('watermark_settings', 'transparent');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isWatermarkPositionTop()
    {
        return $this->getWatermarkPosition() === self::WATERMARK_POSITION_TOP;
    }

    /**
     * @return bool
     */
    public function isWatermarkPositionMiddle()
    {
        return $this->getWatermarkPosition() === self::WATERMARK_POSITION_MIDDLE;
    }

    /**
     * @return bool
     */
    public function isWatermarkPositionBottom()
    {
        return $this->getWatermarkPosition() === self::WATERMARK_POSITION_BOTTOM;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isWatermarkScaleModeNone()
    {
        return $this->getWatermarkScaleMode() === self::WATERMARK_SCALE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isWatermarkScaleModeInWidth()
    {
        return $this->getWatermarkScaleMode() === self::WATERMARK_SCALE_MODE_IN_WIDTH;
    }

    /**
     * @return bool
     */
    public function isWatermarkScaleModeStretch()
    {
        return $this->getWatermarkScaleMode() === self::WATERMARK_SCALE_MODE_STRETCH;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isWatermarkTransparentEnabled()
    {
        return (bool)$this->getWatermarkTransparentMode();
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_description');

        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_description');

        return parent::delete();
    }

    //########################################
}
