<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_Description_Definition extends Ess_M2ePro_Model_Component_Abstract
{
    const TITLE_MODE_CUSTOM  = 1;
    const TITLE_MODE_PRODUCT = 2;

    const BRAND_MODE_NONE             = 0;
    const BRAND_MODE_CUSTOM_VALUE     = 1;
    const BRAND_MODE_CUSTOM_ATTRIBUTE = 2;

    const DESCRIPTION_MODE_NONE     = 0;
    const DESCRIPTION_MODE_PRODUCT  = 1;
    const DESCRIPTION_MODE_SHORT    = 2;
    const DESCRIPTION_MODE_CUSTOM   = 3;

    const TARGET_AUDIENCE_MODE_NONE   = 0;
    const TARGET_AUDIENCE_MODE_CUSTOM = 1;

    const BULLET_POINTS_MODE_NONE   = 0;
    const BULLET_POINTS_MODE_CUSTOM = 1;

    const SEARCH_TERMS_MODE_NONE   = 0;
    const SEARCH_TERMS_MODE_CUSTOM = 1;

    const MANUFACTURER_MODE_NONE             = 0;
    const MANUFACTURER_MODE_CUSTOM_VALUE     = 1;
    const MANUFACTURER_MODE_CUSTOM_ATTRIBUTE = 2;

    const MANUFACTURER_PART_NUMBER_MODE_NONE             = 0;
    const MANUFACTURER_PART_NUMBER_MODE_CUSTOM_VALUE     = 1;
    const MANUFACTURER_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE = 2;

    const DIMENSION_VOLUME_MODE_NONE             = 0;
    const DIMENSION_VOLUME_MODE_CUSTOM_VALUE     = 1;
    const DIMENSION_VOLUME_MODE_CUSTOM_ATTRIBUTE = 2;

    const DIMENSION_VOLUME_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE     = 1;
    const DIMENSION_VOLUME_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE = 2;

    const WEIGHT_MODE_NONE             = 0;
    const WEIGHT_MODE_CUSTOM_VALUE     = 1;
    const WEIGHT_MODE_CUSTOM_ATTRIBUTE = 2;

    const WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE     = 1;
    const WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE = 2;

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
     * @var Ess_M2ePro_Model_Template_Description
     */
    private $descriptionTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source[]
     */
    private $descriptionDefinitionSourceModels = array();

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_Description_Definition');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->descriptionTemplateModel = NULL;
        $temp && $this->descriptionDefinitionSourceModels = array();
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Template_Description
     * @throws Exception
     */
    public function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplateModel)) {

            $this->descriptionTemplateModel = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
                'Template_Description', $this->getId(), NULL, array('template')
            );
        }

        return $this->descriptionTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Description $instance
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
        $this->descriptionTemplateModel = $instance;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description
     * @throws Exception
     */
    public function getAmazonDescriptionTemplate()
    {
        $this->getDescriptionTemplate()->getChildObject();
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->descriptionDefinitionSourceModels[$productId])) {
            return $this->descriptionDefinitionSourceModels[$productId];
        }

        $this->descriptionDefinitionSourceModels[$productId] = Mage::getModel(
            'M2ePro/Amazon_Template_Description_Definition_Source'
        );
        $this->descriptionDefinitionSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->descriptionDefinitionSourceModels[$productId]->setDescriptionDefinitionTemplate($this);

        return $this->descriptionDefinitionSourceModels[$productId];
    }

    // ########################################

    public function getTemplateDescriptionId()
    {
        return (int)$this->getData('template_description_id');
    }

    //-------------------------

    public function getTitleMode()
    {
        return (int)$this->getData('title_mode');
    }

    public function isTitleModeProduct()
    {
        return $this->getTitleMode() == self::TITLE_MODE_PRODUCT;
    }

    public function isTitleModeCustom()
    {
        return $this->getTitleMode() == self::TITLE_MODE_CUSTOM;
    }

    public function getTitleSource()
    {
        return array(
            'mode'     => $this->getTitleMode(),
            'template' => $this->getData('title_template')
        );
    }

    public function getTitleAttributes()
    {
        $attributes = array();
        $src = $this->getTitleSource();

        if ($src['mode'] == self::TITLE_MODE_PRODUCT) {
            $attributes[] = 'name';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getBrandMode()
    {
        return (int)$this->getData('brand_mode');
    }

    public function isBrandModeNone()
    {
        return $this->getBrandMode() == self::BRAND_MODE_NONE;
    }

    public function isBrandModeCustomValue()
    {
        return $this->getBrandMode() == self::BRAND_MODE_CUSTOM_VALUE;
    }

    public function isBrandModeCustomAttribute()
    {
        return $this->getBrandMode() == self::BRAND_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getBrandSource()
    {
        return array(
            'mode'             => $this->getBrandMode(),
            'custom_value'     => $this->getData('brand_custom_value'),
            'custom_attribute' => $this->getData('brand_custom_attribute')
        );
    }

    public function getBrandAttributes()
    {
        $attributes = array();
        $src = $this->getBrandSource();

        if ($src['mode'] == self::BRAND_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getDescriptionMode()
    {
        return (int)$this->getData('description_mode');
    }

    public function isDescriptionModeNone()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_NONE;
    }

    public function isDescriptionModeProduct()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_PRODUCT;
    }

    public function isDescriptionModeShort()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_SHORT;
    }

    public function isDescriptionModeCustom()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_CUSTOM;
    }

    public function getDescriptionSource()
    {
        return array(
            'mode'     => $this->getDescriptionMode(),
            'template' => $this->getData('description_template')
        );
    }

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
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getTargetAudienceMode()
    {
        return (int)$this->getData('target_audience_mode');
    }

    public function getTargetAudienceTemplate()
    {
        return !is_null($this->getData('target_audience')) ? json_decode($this->getData('target_audience'), true)
                                                           : array();
    }

    public function isTargetAudienceModeNone()
    {
        return $this->getTargetAudienceMode() == self::TARGET_AUDIENCE_MODE_NONE;
    }

    public function isTargetAudienceModeCustom()
    {
        return $this->getTargetAudienceMode() == self::TARGET_AUDIENCE_MODE_CUSTOM;
    }

    public function getTargetAudienceSource()
    {
        return array(
            'mode'     => $this->getTargetAudienceMode(),
            'template' => $this->getTargetAudienceTemplate()
        );
    }

    public function getTargetAudienceAttributes()
    {
        $src = $this->getTargetAudienceSource();

        if ($src['mode'] == self::TARGET_AUDIENCE_MODE_NONE) {
            return array();
        }

        $attributes = array();

        if ($src['mode'] == self::TARGET_AUDIENCE_MODE_CUSTOM) {
            $match = array();
            $audience = implode(PHP_EOL,$src['template']);
            preg_match_all('/#([a-zA-Z_]+?)#/', $audience, $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getBulletPointsMode()
    {
        return (int)$this->getData('bullet_points_mode');
    }

    public function getBulletPointsTemplate()
    {
        return is_null($this->getData('bullet_points')) ? array() : json_decode($this->getData('bullet_points'),true);
    }

    public function isBulletPointsModeNone()
    {
        return $this->getBulletPointsMode() == self::BULLET_POINTS_MODE_NONE;
    }

    public function isBulletPointsModeCustom()
    {
        return $this->getBulletPointsMode() == self::BULLET_POINTS_MODE_CUSTOM;
    }

    public function getBulletPointsSource()
    {
        return array(
            'mode'     => $this->getBulletPointsMode(),
            'template' => $this->getBulletPointsTemplate()
        );
    }

    public function getBulletPointsAttributes()
    {
        $src = $this->getBulletPointsSource();

        if ($src['mode'] == self::BULLET_POINTS_MODE_NONE) {
            return array();
        }

        $attributes = array();

        if ($src['mode'] == self::BULLET_POINTS_MODE_CUSTOM) {
            $match = array();
            $bullets = implode(PHP_EOL,$src['template']);
            preg_match_all('/#([a-zA-Z_]+?)#/', $bullets, $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getSearchTermsMode()
    {
        return (int)$this->getData('search_terms_mode');
    }

    public function getSearchTermsTemplate()
    {
        return is_null($this->getData('search_terms')) ? array() : json_decode($this->getData('search_terms'),true);
    }

    public function isSearchTermsModeNone()
    {
        return $this->getSearchTermsMode() == self::SEARCH_TERMS_MODE_NONE;
    }

    public function isSearchTermsModeCustom()
    {
        return $this->getSearchTermsMode() == self::SEARCH_TERMS_MODE_CUSTOM;
    }

    public function getSearchTermsSource()
    {
        return array(
            'mode'     => $this->getSearchTermsMode(),
            'template' => $this->getSearchTermsTemplate()
        );
    }

    public function getSearchTermsAttributes()
    {
        $src = $this->getSearchTermsSource();

        if ($src['mode'] == self::SEARCH_TERMS_MODE_NONE) {
            return array();
        }

        $attributes = array();

        if ($src['mode'] == self::SEARCH_TERMS_MODE_CUSTOM) {
            $match = array();
            $searchTerms = implode(PHP_EOL,$src['template']);
            preg_match_all('/#([a-zA-Z_]+?)#/', $searchTerms, $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getManufacturerMode()
    {
        return (int)$this->getData('manufacturer_mode');
    }

    public function isManufacturerModeNone()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_NONE;
    }

    public function isManufacturerModeCustomValue()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_CUSTOM_VALUE;
    }

    public function isManufacturerModeCustomAttribute()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getManufacturerSource()
    {
        return array(
            'mode'             => $this->getManufacturerMode(),
            'custom_value'     => $this->getData('manufacturer_custom_value'),
            'custom_attribute' => $this->getData('manufacturer_custom_attribute')
        );
    }

    public function getManufacturerAttributes()
    {
        $attributes = array();
        $src = $this->getManufacturerSource();

        if ($src['mode'] == self::MANUFACTURER_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getManufacturerPartNumberMode()
    {
        return (int)$this->getData('manufacturer_part_number_mode');
    }

    public function isManufacturerPartNumberModeNone()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_NONE;
    }

    public function isManufacturerPartNumberModeCustomValue()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_VALUE;
    }

    public function isManufacturerPartNumberModeCustomAttribute()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getManufacturerPartNumberSource()
    {
        return array(
            'mode'             => $this->getManufacturerPartNumberMode(),
            'custom_value'     => $this->getData('manufacturer_part_number_custom_value'),
            'custom_attribute' => $this->getData('manufacturer_part_number_custom_attribute')
        );
    }

    public function getManufacturerPartNumberAttributes()
    {
        $attributes = array();
        $src = $this->getManufacturerPartNumberSource();

        if ($src['mode'] == self::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getItemDimensionsVolumeMode()
    {
        return (int)$this->getData('item_dimensions_volume_mode');
    }

    public function isItemDimensionsVolumeModeNone()
    {
        return $this->getItemDimensionsVolumeMode() == self::DIMENSION_VOLUME_MODE_NONE;
    }

    public function isItemDimensionsVolumeModeCustomValue()
    {
        return $this->getItemDimensionsVolumeMode() == self::DIMENSION_VOLUME_MODE_CUSTOM_VALUE;
    }

    public function isItemDimensionsVolumeModeCustomAttribute()
    {
        return $this->getItemDimensionsVolumeMode() == self::DIMENSION_VOLUME_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getItemDimensionsVolumeSource()
    {
        return array(
            'mode' => $this->getItemDimensionsVolumeMode(),

            'length_custom_value' => $this->getData('item_dimensions_volume_length_custom_value'),
            'width_custom_value'  => $this->getData('item_dimensions_volume_width_custom_value'),
            'height_custom_value' => $this->getData('item_dimensions_volume_height_custom_value'),

            'length_custom_attribute' => $this->getData('item_dimensions_volume_length_custom_attribute'),
            'width_custom_attribute'  => $this->getData('item_dimensions_volume_width_custom_attribute'),
            'height_custom_attribute' => $this->getData('item_dimensions_volume_height_custom_attribute')
        );
    }

    public function getItemDimensionsVolumeAttributes()
    {
        $attributes = array();
        $src = $this->getItemDimensionsVolumeSource();

        if ($src['mode'] == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['length_custom_attribute'];
            $attributes[] = $src['width_custom_attribute'];
            $attributes[] = $src['height_custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getItemDimensionsVolumeUnitOfMeasureMode()
    {
        return (int)$this->getData('item_dimensions_volume_unit_of_measure_mode');
    }

    public function isItemDimensionsVolumeUnitOfMeasureModeCustomValue()
    {
        return $this->getItemDimensionsVolumeUnitOfMeasureMode() ==
               self::DIMENSION_VOLUME_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE;
    }

    public function isItemDimensionsVolumeUnitOfMeasureModeCustomAttribute()
    {
        return $this->getItemDimensionsVolumeUnitOfMeasureMode() ==
               self::DIMENSION_VOLUME_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getItemDimensionsVolumeUnitOfMeasureSource()
    {
        return array(
            'mode'             => $this->getItemDimensionsVolumeUnitOfMeasureMode(),
            'custom_value'     => $this->getData('item_dimensions_volume_unit_of_measure_custom_value'),
            'custom_attribute' => $this->getData('item_dimensions_volume_unit_of_measure_custom_attribute')
        );
    }

    public function getItemDimensionsVolumeUnitOfMeasureAttributes()
    {
        $attributes = array();
        $src = $this->getItemDimensionsVolumeUnitOfMeasureSource();

        if ($src['mode'] == self::DIMENSION_VOLUME_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getItemDimensionsWeightMode()
    {
        return (int)$this->getData('item_dimensions_weight_mode');
    }

    public function isItemDimensionsWeightModeNone()
    {
        return $this->getItemDimensionsWeightMode() == self::WEIGHT_MODE_NONE;
    }

    public function isItemDimensionsWeightModeCustomValue()
    {
        return $this->getItemDimensionsWeightMode() == self::WEIGHT_MODE_CUSTOM_VALUE;
    }

    public function isItemDimensionsWeightModeCustomAttribute()
    {
        return $this->getItemDimensionsWeightMode() == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getItemDimensionsWeightSource()
    {
        return array(
            'mode'             => $this->getItemDimensionsWeightMode(),
            'custom_value'     => $this->getData('item_dimensions_weight_custom_value'),
            'custom_attribute' => $this->getData('item_dimensions_weight_custom_attribute')
        );
    }

    public function getItemDimensionsWeightAttributes()
    {
        $attributes = array();
        $src = $this->getItemDimensionsWeightSource();

        if ($src['mode'] == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getItemDimensionsWeightUnitOfMeasureMode()
    {
        return (int)$this->getData('item_dimensions_weight_unit_of_measure_mode');
    }

    public function isItemDimensionsWeightUnitOfMeasureModeCustomValue()
    {
        return $this->getItemDimensionsWeightUnitOfMeasureMode() == self::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE;
    }

    public function isItemDimensionsWeightUnitOfMeasureModeCustomAttribute()
    {
        return $this->getItemDimensionsWeightUnitOfMeasureMode() == self::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getItemDimensionsWeightUnitOfMeasureSource()
    {
        return array(
            'mode'             => $this->getItemDimensionsWeightUnitOfMeasureMode(),
            'custom_value'     => $this->getData('item_dimensions_weight_unit_of_measure_custom_value'),
            'custom_attribute' => $this->getData('item_dimensions_weight_unit_of_measure_custom_attribute')
        );
    }

    public function getItemDimensionsWeightUnitOfMeasureAttributes()
    {
        $attributes = array();
        $src = $this->getItemDimensionsWeightUnitOfMeasureSource();

        if ($src['mode'] == self::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getPackageDimensionsVolumeMode()
    {
        return (int)$this->getData('package_dimensions_volume_mode');
    }

    public function isPackageDimensionsVolumeModeNone()
    {
        return $this->getPackageDimensionsVolumeMode() == self::DIMENSION_VOLUME_MODE_NONE;
    }

    public function isPackageDimensionsVolumeModeCustomValue()
    {
        return $this->getPackageDimensionsVolumeMode() == self::DIMENSION_VOLUME_MODE_CUSTOM_VALUE;
    }

    public function isPackageDimensionsVolumeModeCustomAttribute()
    {
        return $this->getPackageDimensionsVolumeMode() == self::DIMENSION_VOLUME_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getPackageDimensionsVolumeSource()
    {
        return array(
            'mode' => $this->getPackageDimensionsVolumeMode(),

            'length_custom_value' => $this->getData('package_dimensions_volume_length_custom_value'),
            'width_custom_value'  => $this->getData('package_dimensions_volume_width_custom_value'),
            'height_custom_value' => $this->getData('package_dimensions_volume_height_custom_value'),

            'length_custom_attribute' => $this->getData('package_dimensions_volume_length_custom_attribute'),
            'width_custom_attribute'  => $this->getData('package_dimensions_volume_width_custom_attribute'),
            'height_custom_attribute' => $this->getData('package_dimensions_volume_height_custom_attribute')
        );
    }

    public function getPackageDimensionsVolumeAttributes()
    {
        $attributes = array();
        $src = $this->getPackageDimensionsVolumeSource();

        if ($src['mode'] == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['length_custom_attribute'];
            $attributes[] = $src['width_custom_attribute'];
            $attributes[] = $src['height_custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getPackageDimensionsVolumeUnitOfMeasureMode()
    {
        return (int)$this->getData('package_dimensions_volume_unit_of_measure_mode');
    }

    public function isPackageDimensionsVolumeUnitOfMeasureModeCustomValue()
    {
        return $this->getPackageDimensionsVolumeUnitOfMeasureMode() ==
               self::DIMENSION_VOLUME_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE;
    }

    public function isPackageDimensionsVolumeUnitOfMeasureModeCustomAttribute()
    {
        return $this->getPackageDimensionsVolumeUnitOfMeasureMode() ==
               self::DIMENSION_VOLUME_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getPackageDimensionsVolumeUnitOfMeasureSource()
    {
        return array(
            'mode'             => $this->getPackageDimensionsVolumeUnitOfMeasureMode(),
            'custom_value'     => $this->getData('package_dimensions_volume_unit_of_measure_custom_value'),
            'custom_attribute' => $this->getData('package_dimensions_volume_unit_of_measure_custom_attribute')
        );
    }

    public function getPackageDimensionsVolumeUnitOfMeasureAttributes()
    {
        $attributes = array();
        $src = $this->getPackageDimensionsVolumeUnitOfMeasureSource();

        if ($src['mode'] == self::DIMENSION_VOLUME_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getPackageWeightMode()
    {
        return (int)$this->getData('package_weight_mode');
    }

    public function isPackageWeightModeNone()
    {
        return $this->getPackageWeightMode() == self::WEIGHT_MODE_NONE;
    }

    public function isPackageWeightModeCustomValue()
    {
        return $this->getPackageWeightMode() == self::WEIGHT_MODE_CUSTOM_VALUE;
    }

    public function isPackageWeightModeCustomAttribute()
    {
        return $this->getPackageWeightMode() == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getPackageWeightSource()
    {
        return array(
            'mode'             => $this->getPackageWeightMode(),
            'custom_value'     => $this->getData('package_weight_custom_value'),
            'custom_attribute' => $this->getData('package_weight_custom_attribute')
        );
    }

    public function getPackageWeightAttributes()
    {
        $attributes = array();
        $src = $this->getPackageWeightSource();

        if ($src['mode'] == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getPackageWeightUnitOfMeasureMode()
    {
        return (int)$this->getData('package_weight_unit_of_measure_mode');
    }

    public function isPackageWeightUnitOfMeasureModeCustomValue()
    {
        return $this->getPackageWeightUnitOfMeasureMode() == self::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE;
    }

    public function isPackageWeightUnitOfMeasureModeCustomAttribute()
    {
        return $this->getPackageWeightUnitOfMeasureMode() == self::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getPackageWeightUnitOfMeasureSource()
    {
        return array(
            'mode'             => $this->getPackageWeightUnitOfMeasureMode(),
            'custom_value'     => $this->getData('package_weight_unit_of_measure_custom_value'),
            'custom_attribute' => $this->getData('package_weight_unit_of_measure_custom_attribute')
        );
    }

    public function getPackageWeightUnitOfMeasureAttributes()
    {
        $attributes = array();
        $src = $this->getPackageWeightUnitOfMeasureSource();

        if ($src['mode'] == self::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getShippingWeightMode()
    {
        return (int)$this->getData('shipping_weight_mode');
    }

    public function isShippingWeightModeNone()
    {
        return $this->getShippingWeightMode() == self::WEIGHT_MODE_NONE;
    }

    public function isShippingWeightModeCustomValue()
    {
        return $this->getShippingWeightMode() == self::WEIGHT_MODE_CUSTOM_VALUE;
    }

    public function isShippingWeightModeCustomAttribute()
    {
        return $this->getShippingWeightMode() == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingWeightSource()
    {
        return array(
            'mode'             => $this->getShippingWeightMode(),
            'custom_value'     => $this->getData('shipping_weight_custom_value'),
            'custom_attribute' => $this->getData('shipping_weight_custom_attribute')
        );
    }

    public function getShippingWeightAttributes()
    {
        $attributes = array();
        $src = $this->getShippingWeightSource();

        if ($src['mode'] == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getShippingWeightUnitOfMeasureMode()
    {
        return (int)$this->getData('shipping_weight_unit_of_measure_mode');
    }

    public function isShippingWeightUnitOfMeasureModeCustomValue()
    {
        return $this->getShippingWeightUnitOfMeasureMode() == self::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE;
    }

    public function isShippingWeightUnitOfMeasureModeCustomAttribute()
    {
        return $this->getShippingWeightUnitOfMeasureMode() == self::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingWeightUnitOfMeasureSource()
    {
        return array(
            'mode'             => $this->getShippingWeightUnitOfMeasureMode(),
            'custom_value'     => $this->getData('shipping_weight_unit_of_measure_custom_value'),
            'custom_attribute' => $this->getData('shipping_weight_unit_of_measure_custom_attribute')
        );
    }

    public function getShippingWeightUnitOfMeasureAttributes()
    {
        $attributes = array();
        $src = $this->getShippingWeightUnitOfMeasureSource();

        if ($src['mode'] == self::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['custom_attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getImageMainMode()
    {
        return (int)$this->getData('image_main_mode');
    }

    public function isImageMainModeNone()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_NONE;
    }

    public function isImageMainModeProduct()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_PRODUCT;
    }

    public function isImageMainModeAttribute()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_ATTRIBUTE;
    }

    public function getImageMainSource()
    {
        return array(
            'mode'     => $this->getImageMainMode(),
            'attribute' => $this->getData('image_main_attribute')
        );
    }

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

    //-------------------------

    public function getImageVariationDifferenceMode()
    {
        return (int)$this->getData('image_variation_difference_mode');
    }

    public function isImageVariationDifferenceModeNone()
    {
        return $this->getImageVariationDifferenceMode() == self::IMAGE_VARIATION_DIFFERENCE_MODE_NONE;
    }

    public function isImageVariationDifferenceModeProduct()
    {
        return $this->getImageVariationDifferenceMode() == self::IMAGE_VARIATION_DIFFERENCE_MODE_PRODUCT;
    }

    public function isImageVariationDifferenceModeAttribute()
    {
        return $this->getImageVariationDifferenceMode() == self::IMAGE_VARIATION_DIFFERENCE_MODE_ATTRIBUTE;
    }

    public function getImageVariationDifferenceSource()
    {
        return array(
            'mode'     => $this->getImageVariationDifferenceMode(),
            'attribute' => $this->getData('image_variation_difference_attribute')
        );
    }

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

    //-------------------------

    public function getGalleryImagesMode()
    {
        return (int)$this->getData('gallery_images_mode');
    }

    public function isGalleryImagesModeNone()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_NONE;
    }

    public function isGalleryImagesModeProduct()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_PRODUCT;
    }

    public function isGalleryImagesModeAttribute()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_ATTRIBUTE;
    }

    public function getGalleryImagesSource()
    {
        return array(
            'mode'      => $this->getGalleryImagesMode(),
            'attribute' => $this->getData('gallery_images_attribute'),
            'limit'     => $this->getData('gallery_images_limit')
        );
    }

    public function getGalleryImagesAttributes()
    {
        $attributes = array();
        $src = $this->getGalleryImagesSource();

        if ($src['mode'] == self::GALLERY_IMAGES_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return $this->getUsedAttributes();
    }

    public function getUsedAttributes()
    {
        return array_unique(array_merge(
            $this->getUsedDetailsAttributes(),
            $this->getUsedImagesAttributes()
        ));
    }

    public function getUsedDetailsAttributes()
    {
        return array_unique(array_merge(

            $this->getTitleAttributes(),
            $this->getBrandAttributes(),
            $this->getDescriptionAttributes(),

            $this->getBulletPointsAttributes(),
            $this->getSearchTermsAttributes(),
            $this->getTargetAudienceAttributes(),

            $this->getManufacturerAttributes(),
            $this->getManufacturerPartNumberAttributes(),

            $this->getItemDimensionsVolumeAttributes(),
            $this->getItemDimensionsVolumeUnitOfMeasureAttributes(),
            $this->getItemDimensionsWeightAttributes(),
            $this->getItemDimensionsWeightUnitOfMeasureAttributes(),

            $this->getPackageDimensionsVolumeAttributes(),
            $this->getPackageDimensionsVolumeUnitOfMeasureAttributes(),

            $this->getPackageWeightAttributes(),
            $this->getPackageWeightUnitOfMeasureAttributes(),

            $this->getShippingWeightAttributes(),
            $this->getShippingWeightUnitOfMeasureAttributes()
        ));
    }

    public function getUsedImagesAttributes()
    {
        return array_unique(array_merge(
            $this->getImageMainAttributes(),
            $this->getImageVariationDifferenceAttributes(),
            $this->getGalleryImagesAttributes()
        ));
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_description_definition');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_description_definition');
        return parent::delete();
    }

    // ########################################
}