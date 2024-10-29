<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Template_Description getParentObject()
 * @method Ess_M2ePro_Model_Resource_Walmart_Template_Description getResource()
 */
class Ess_M2ePro_Model_Walmart_Template_Description extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    const TITLE_MODE_CUSTOM  = 1;
    const TITLE_MODE_PRODUCT = 2;

    const BRAND_MODE_CUSTOM_VALUE     = 1;
    const BRAND_MODE_CUSTOM_ATTRIBUTE = 2;

    const COUNT_PER_PACK_MODE_NONE             = 0;
    const COUNT_PER_PACK_MODE_CUSTOM_VALUE     = 1;
    const COUNT_PER_PACK_MODE_CUSTOM_ATTRIBUTE = 2;

    const MULTIPACK_QUANTITY_MODE_NONE             = 0;
    const MULTIPACK_QUANTITY_MODE_CUSTOM_VALUE     = 1;
    const MULTIPACK_QUANTITY_MODE_CUSTOM_ATTRIBUTE = 2;

    const MODEL_NUMBER_MODE_NONE             = 0;
    const MODEL_NUMBER_MODE_CUSTOM_VALUE     = 1;
    const MODEL_NUMBER_MODE_CUSTOM_ATTRIBUTE = 2;

    const TOTAL_COUNT_MODE_NONE             = 0;
    const TOTAL_COUNT_MODE_CUSTOM_VALUE     = 1;
    const TOTAL_COUNT_MODE_CUSTOM_ATTRIBUTE = 2;

    const MSRP_RRP_MODE_NONE       = 0;
    const MSRP_RRP_MODE_ATTRIBUTE  = 1;

    const DESCRIPTION_MODE_PRODUCT  = 1;
    const DESCRIPTION_MODE_SHORT    = 2;
    const DESCRIPTION_MODE_CUSTOM   = 3;

    const KEY_FEATURES_MODE_NONE   = 0;
    const KEY_FEATURES_MODE_CUSTOM = 1;

    const OTHER_FEATURES_MODE_NONE   = 0;
    const OTHER_FEATURES_MODE_CUSTOM = 1;

    const ATTRIBUTES_MODE_NONE   = 0;
    const ATTRIBUTES_MODE_CUSTOM = 1;

    const MANUFACTURER_MODE_NONE             = 0;
    const MANUFACTURER_MODE_CUSTOM_VALUE     = 1;
    const MANUFACTURER_MODE_CUSTOM_ATTRIBUTE = 2;

    const MANUFACTURER_PART_NUMBER_MODE_NONE             = 0;
    const MANUFACTURER_PART_NUMBER_MODE_CUSTOM_VALUE     = 1;
    const MANUFACTURER_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE = 2;

    const IMAGE_MAIN_MODE_NONE       = 0;
    const IMAGE_MAIN_MODE_PRODUCT    = 1;
    const IMAGE_MAIN_MODE_ATTRIBUTE  = 2;

    const IMAGE_VARIATION_DIFFERENCE_MODE_NONE      = 0;
    const IMAGE_VARIATION_DIFFERENCE_MODE_PRODUCT   = 1;
    const IMAGE_VARIATION_DIFFERENCE_MODE_ATTRIBUTE = 2;

    const GALLERY_IMAGES_MODE_NONE      = 0;
    const GALLERY_IMAGES_MODE_PRODUCT   = 1;
    const GALLERY_IMAGES_MODE_ATTRIBUTE = 2;

    /**
     * @var Ess_M2ePro_Model_Walmart_Template_Description_Source[]
     */
    protected $_descriptionSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Template_Description');
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

        return (bool)Mage::getModel('M2ePro/Walmart_Listing')->getCollection()
                        ->addFieldToFilter('template_description_id', $this->getId())
                        ->getSize();
    }

    // ---------------------------------------

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->_descriptionSourceModels = array();

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Walmart_Template_Description_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->_descriptionSourceModels[$productId])) {
            return $this->_descriptionSourceModels[$productId];
        }

        $this->_descriptionSourceModels[$productId] = Mage::getModel('M2ePro/Walmart_Template_Description_Source');
        $this->_descriptionSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->_descriptionSourceModels[$productId]->setDescriptionTemplate($this->getParentObject());

        return $this->_descriptionSourceModels[$productId];
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
    public function getBrandMode()
    {
        return (int)$this->getData('brand_mode');
    }

    /**
     * @return bool
     */
    public function isBrandModeCustomValue()
    {
        return $this->getBrandMode() == self::BRAND_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isBrandModeCustomAttribute()
    {
        return $this->getBrandMode() == self::BRAND_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getBrandSource()
    {
        return array(
            'mode'             => $this->getBrandMode(),
            'custom_value'     => $this->getData('brand_custom_value'),
            'custom_attribute' => $this->getData('brand_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getBrandAttributes()
    {
        $attributes = array();
        $src = $this->getBrandSource();

        if ($src['mode'] == self::BRAND_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getCountPerPackMode()
    {
        return (int)$this->getData('count_per_pack_mode');
    }

    public function getCountPerPackCustomValue()
    {
        return $this->getData('count_per_pack_custom_value');
    }

    public function getCountPerPackCustomAttribute()
    {
        return $this->getData('count_per_pack_custom_attribute');
    }

    /**
     * @return bool
     */
    public function isCountPerPackModeNone()
    {
        return $this->getCountPerPackMode() == self::COUNT_PER_PACK_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isCountPerPackModeCustomValue()
    {
        return $this->getCountPerPackMode() == self::COUNT_PER_PACK_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isCountPerPackModeCustomAttribute()
    {
        return $this->getCountPerPackMode() == self::COUNT_PER_PACK_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getCountPerPackSource()
    {
        return array(
            'mode'      => $this->getCountPerPackMode(),
            'value'     => $this->getCountPerPackCustomValue(),
            'attribute' => $this->getCountPerPackCustomAttribute()
        );
    }

    /**
     * @return array
     */
    public function getCountPerPackAttributes()
    {
        $attributes = array();
        $src = $this->getCountPerPackSource();

        if ($src['mode'] == self::COUNT_PER_PACK_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getMultipackQuantityMode()
    {
        return (int)$this->getData('multipack_quantity_mode');
    }

    /**
     * @return bool
     */
    public function isMultipackQuantityModeNone()
    {
        return $this->getMultipackQuantityMode() == self::MULTIPACK_QUANTITY_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isMultipackQuantityModeCustomValue()
    {
        return $this->getMultipackQuantityMode() == self::MULTIPACK_QUANTITY_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isMultipackQuantityModeCustomAttribute()
    {
        return $this->getMultipackQuantityMode() == self::MULTIPACK_QUANTITY_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getMultipackQuantitySource()
    {
        return array(
            'mode'      => $this->getMultipackQuantityMode(),
            'value'     => $this->getData('multipack_quantity_custom_value'),
            'attribute' => $this->getData('multipack_quantity_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getMultipackQuantityAttributes()
    {
        $attributes = array();
        $src = $this->getMultipackQuantitySource();

        if ($src['mode'] == self::MULTIPACK_QUANTITY_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getTotalCountMode()
    {
        return (int)$this->getData('total_count_mode');
    }

    /**
     * @return bool
     */
    public function isTotalCountModeNone()
    {
        return $this->getTotalCountMode() == self::TOTAL_COUNT_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isTotalCountModeCustomValue()
    {
        return $this->getTotalCountMode() == self::TOTAL_COUNT_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isTotalCountModeCustomAttribute()
    {
        return $this->getTotalCountMode() == self::TOTAL_COUNT_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getTotalCountSource()
    {
        return array(
            'mode'             => $this->getTotalCountMode(),
            'custom_value'     => $this->getData('total_count_custom_value'),
            'custom_attribute' => $this->getData('total_count_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getTotalCountAttributes()
    {
        $attributes = array();
        $src = $this->getTotalCountSource();

        if ($src['mode'] == self::TOTAL_COUNT_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getModelNumberMode()
    {
        return (int)$this->getData('model_number_mode');
    }

    /**
     * @return bool
     */
    public function isModelNumberModeNone()
    {
        return $this->getModelNumberMode() == self::MODEL_NUMBER_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isModelNumberModeCustomValue()
    {
        return $this->getModelNumberMode() == self::MODEL_NUMBER_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isModelNumberModeCustomAttribute()
    {
        return $this->getModelNumberMode() == self::MODEL_NUMBER_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getModelNumberSource()
    {
        return array(
            'mode'             => $this->getModelNumberMode(),
            'custom_value'     => $this->getData('model_number_custom_value'),
            'custom_attribute' => $this->getData('model_number_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getModelNumberAttributes()
    {
        $attributes = array();
        $src = $this->getModelNumberSource();

        if ($src['mode'] == self::MODEL_NUMBER_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
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
    public function getKeyFeaturesMode()
    {
        return (int)$this->getData('key_features_mode');
    }

    /**
     * @return array
     */
    public function getKeyFeaturesTemplate()
    {
        return $this->getData('key_features') !== null
            ? Mage::helper('M2ePro')->jsonDecode($this->getData('key_features')) : array();
    }

    /**
     * @return bool
     */
    public function isKeyFeaturesModeNone()
    {
        return $this->getKeyFeaturesMode() == self::KEY_FEATURES_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isKeyFeaturesModeCustom()
    {
        return $this->getKeyFeaturesMode() == self::KEY_FEATURES_MODE_CUSTOM;
    }

    /**
     * @return array
     */
    public function getKeyFeaturesSource()
    {
        return array(
            'mode'     => $this->getKeyFeaturesMode(),
            'template' => $this->getKeyFeaturesTemplate()
        );
    }

    /**
     * @return array
     */
    public function getKeyFeaturesAttributes()
    {
        $src = $this->getKeyFeaturesSource();

        if ($src['mode'] == self::KEY_FEATURES_MODE_NONE) {
            return array();
        }

        $attributes = array();

        if ($src['mode'] == self::KEY_FEATURES_MODE_CUSTOM) {
            $match = array();
            $audience = implode(PHP_EOL, $src['template']);
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $audience, $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherFeaturesMode()
    {
        return (int)$this->getData('other_features_mode');
    }

    /**
     * @return array
     */
    public function getOtherFeaturesTemplate()
    {
        return $this->getData('other_features') === null
            ? array() : Mage::helper('M2ePro')->jsonDecode($this->getData('other_features'));
    }

    /**
     * @return bool
     */
    public function isOtherFeaturesModeNone()
    {
        return $this->getOtherFeaturesMode() == self::OTHER_FEATURES_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherFeaturesModeCustom()
    {
        return $this->getOtherFeaturesMode() == self::OTHER_FEATURES_MODE_CUSTOM;
    }

    /**
     * @return array
     */
    public function getOtherFeaturesSource()
    {
        return array(
            'mode'     => $this->getOtherFeaturesMode(),
            'template' => $this->getOtherFeaturesTemplate()
        );
    }

    /**
     * @return array
     */
    public function getOtherFeaturesAttributes()
    {
        $src = $this->getOtherFeaturesSource();

        if ($src['mode'] == self::OTHER_FEATURES_MODE_NONE) {
            return array();
        }

        $attributes = array();

        if ($src['mode'] == self::OTHER_FEATURES_MODE_CUSTOM) {
            $match = array();
            $bullets = implode(PHP_EOL, $src['template']);
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $bullets, $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getManufacturerMode()
    {
        return (int)$this->getData('manufacturer_mode');
    }

    /**
     * @return bool
     */
    public function isManufacturerModeNone()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isManufacturerModeCustomValue()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isManufacturerModeCustomAttribute()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getManufacturerSource()
    {
        return array(
            'mode'             => $this->getManufacturerMode(),
            'custom_value'     => $this->getData('manufacturer_custom_value'),
            'custom_attribute' => $this->getData('manufacturer_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getManufacturerAttributes()
    {
        $attributes = array();
        $src = $this->getManufacturerSource();

        if ($src['mode'] == self::MANUFACTURER_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getManufacturerPartNumberMode()
    {
        return (int)$this->getData('manufacturer_part_number_mode');
    }

    /**
     * @return bool
     */
    public function isManufacturerPartNumberModeNone()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isManufacturerPartNumberModeCustomValue()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isManufacturerPartNumberModeCustomAttribute()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getManufacturerPartNumberSource()
    {
        return array(
            'mode'             => $this->getManufacturerPartNumberMode(),
            'custom_value'     => $this->getData('manufacturer_part_number_custom_value'),
            'custom_attribute' => $this->getData('manufacturer_part_number_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getManufacturerPartNumberAttributes()
    {
        $attributes = array();
        $src = $this->getManufacturerPartNumberSource();

        if ($src['mode'] == self::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getMsrpRrpMode()
    {
        return (int)$this->getData('msrp_rrp_mode');
    }

    /**
     * @return bool
     */
    public function isMsrpRrpModeNone()
    {
        return $this->getMsrpRrpMode() == self::MSRP_RRP_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isMsrpRrpModeCustomAttribute()
    {
        return $this->getMsrpRrpMode() == self::MSRP_RRP_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getMsrpRrpSource()
    {
        return array(
            'mode'             => $this->getMsrpRrpMode(),
            'custom_attribute' => $this->getData('msrp_rrp_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getMsrpRrpAttributes()
    {
        $attributes = array();
        $src = $this->getMsrpRrpSource();

        if ($src['mode'] == self::MSRP_RRP_MODE_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

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
            'mode'     => $this->getImageMainMode(),
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
        } else if ($src['mode'] == self::IMAGE_MAIN_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getImageVariationDifferenceMode()
    {
        return (int)$this->getData('image_variation_difference_mode');
    }

    /**
     * @return bool
     */
    public function isImageVariationDifferenceModeNone()
    {
        return $this->getImageVariationDifferenceMode() == self::IMAGE_VARIATION_DIFFERENCE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isImageVariationDifferenceModeProduct()
    {
        return $this->getImageVariationDifferenceMode() == self::IMAGE_VARIATION_DIFFERENCE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isImageVariationDifferenceModeAttribute()
    {
        return $this->getImageVariationDifferenceMode() == self::IMAGE_VARIATION_DIFFERENCE_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getImageVariationDifferenceSource()
    {
        return array(
            'mode'     => $this->getImageVariationDifferenceMode(),
            'attribute' => $this->getData('image_variation_difference_attribute')
        );
    }

    /**
     * @return array
     */
    public function getImageVariationDifferenceAttributes()
    {
        $attributes = array();
        $src = $this->getImageVariationDifferenceSource();

        if ($src['mode'] == self::IMAGE_VARIATION_DIFFERENCE_MODE_PRODUCT) {
            $attributes[] = 'image';
        } else if ($src['mode'] == self::IMAGE_VARIATION_DIFFERENCE_MODE_ATTRIBUTE) {
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

        if ($src['mode'] == self::GALLERY_IMAGES_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return array
     */
    public function getUsedDetailsAttributes()
    {
        return array_unique(
            array_merge(
                $this->getTitleAttributes(),
                $this->getBrandAttributes(),
                $this->getMultipackQuantityAttributes(),
                $this->getCountPerPackAttributes(),
                $this->getModelNumberAttributes(),
                $this->getTotalCountAttributes(),
                $this->getDescriptionAttributes(),
                $this->getOtherFeaturesAttributes(),
                $this->getKeyFeaturesAttributes(),
                $this->getManufacturerAttributes(),
                $this->getManufacturerPartNumberAttributes(),
                $this->getMsrpRrpAttributes()
            )
        );
    }

    /**
     * @return array
     */
    public function getUsedImagesAttributes()
    {
        return array_unique(
            array_merge(
                $this->getImageMainAttributes(),
                $this->getImageVariationDifferenceAttributes(),
                $this->getGalleryImagesAttributes()
            )
        );
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('walmart_template_description');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('walmart_template_description');
        return parent::delete();
    }

    //########################################
}
