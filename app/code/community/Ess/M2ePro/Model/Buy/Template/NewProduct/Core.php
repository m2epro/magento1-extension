<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Template_NewProduct_Core extends Ess_M2ePro_Model_Component_Abstract
{
    const GTIN_MODE_NONE = 0;
    const GTIN_MODE_CUSTOM_ATTRIBUTE = 2;

    const ISBN_MODE_NONE = 0;
    const ISBN_MODE_CUSTOM_ATTRIBUTE = 2;

    const MFG_PART_NUMBER_MODE_CUSTOM_VALUE = 1;
    const MFG_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE = 2;

    const PRODUCT_SET_ID_MODE_NONE = 0;
    const PRODUCT_SET_ID_MODE_CUSTOM_VALUE = 1;
    const PRODUCT_SET_ID_MODE_CUSTOM_ATTRIBUTE = 2;

    const TITLE_MODE_PRODUCT_NAME = 1;
    const TITLE_MODE_CUSTOM_TEMPLATE  = 2; //t

    const DESCRIPTION_MODE_PRODUCT_SHORT  = 1;
    const DESCRIPTION_MODE_PRODUCT_FULL = 2;
    const DESCRIPTION_MODE_CUSTOM_TEMPLATE = 3; //t

    const IMAGE_MAIN_MODE_PRODUCT_BASE = 1;
    const IMAGE_MAIN_MODE_CUSTOM_ATTRIBUTE = 2;

    const ADDITIONAL_IMAGES_MODE_NONE = 0;
    const ADDITIONAL_IMAGES_MODE_PRODUCT = 1;
    const ADDITIONAL_IMAGES_MODE_CUSTOM_ATTRIBUTE = 2;

    const KEYWORDS_MODE_NONE = 0;
    const KEYWORDS_MODE_CUSTOM_VALUE = 1;
    const KEYWORDS_MODE_CUSTOM_ATTRIBUTE = 2;

    const FEATURES_MODE_NONE = 0;
    const FEATURES_MODE_CUSTOM_TEMPLATE = 1;
    const FEATURES_COUNT = 10;

    const WEIGHT_MODE_CUSTOM_VALUE = 1;
    const WEIGHT_MODE_CUSTOM_ATTRIBUTE = 2;

    /**
     * @var Ess_M2ePro_Model_Buy_Template_NewProduct
     */
    private $newProductTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Buy_Template_NewProduct_Core_Source[]
     */
    private $newProductCoreSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Template_NewProduct_Core');
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $temp = parent::deleteInstance();
        $temp && $this->newProductTemplateModel = NULL;
        $temp && $this->newProductCoreSourceModels = array();
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Template_NewProduct
     */
    public function getNewProductTemplate()
    {
        if (is_null($this->newProductTemplateModel)) {

            $this->newProductTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Buy_Template_NewProduct', $this->getTemplateNewProductId(), NULL, array('template')
            );
        }

        return $this->newProductTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Buy_Template_NewProduct $instance
     */
    public function setNewProductTemplate(Ess_M2ePro_Model_Buy_Template_NewProduct $instance)
    {
        $this->newProductTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Buy_Template_NewProduct_Core_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->newProductCoreSourceModels[$productId])) {
            return $this->newProductCoreSourceModels[$productId];
        }

        $this->newProductCoreSourceModels[$productId] = Mage::getModel('M2ePro/Buy_Template_NewProduct_Core_Source');
        $this->newProductCoreSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->newProductCoreSourceModels[$productId]->setNewProductCoreTemplate($this);

        return $this->newProductCoreSourceModels[$productId];
    }

    //########################################

    /**
     * @return int
     */
    public function getTemplateNewProductId()
    {
        return (int)$this->getData('template_new_product_id');
    }

    /**
     * @return mixed
     */
    public function getSellerSkuCustomAttribute()
    {
        return $this->getData('seller_sku_custom_attribute');
    }

    /**
     * @return array
     */
    public function getSellerSkuSource()
    {
        return array(
            'custom_attribute' => $this->getSellerSkuCustomAttribute(),
        );
    }

    //########################################

    /**
     * @return int
     */
    public function getGtinMode()
    {
        return (int)$this->getData('gtin_mode');
    }

    /**
     * @return bool
     */
    public function isGtinNone()
    {
        return $this->getGtinMode() == self::GTIN_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isGtinCustomAttribute()
    {
        return $this->getGtinMode() == self::GTIN_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getGtinCustomAttribute()
    {
        return $this->getData('gtin_custom_attribute');
    }

    /**
     * @return array
     */
    public function getGtinSource()
    {
        return array(
            'mode' => $this->getGtinMode(),
            'custom_attribute' => $this->getGtinCustomAttribute(),
        );
    }

    //########################################

    /**
     * @return int
     */
    public function getIsbnMode()
    {
        return (int)$this->getData('isbn_mode');
    }

    public function getIsbnCustomAttribute()
    {
        return $this->getData('isbn_custom_attribute');
    }

    /**
     * @return bool
     */
    public function isIsbnNone()
    {
        return $this->getIsbnMode() == self::ISBN_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isIsbnCustomAttribute()
    {
        return $this->getIsbnMode() == self::ISBN_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getIsbnSource()
    {
        return array(
            'mode'     => $this->getIsbnMode(),
            'custom_attribute' => $this->getIsbnCustomAttribute(),
        );
    }

    //########################################

    /**
     * @return array
     */
    public function getMfgSource()
    {
        return array(
            'template' => $this->getData('mfg_name_template')
        );
    }

    /**
     * @return array
     */
    public function getMfgAttributes()
    {
        $attributes = array();
        $src = $this->getMfgSource();

        $match = array();
        preg_match_all('/#([a-zA-Z_0-9]+?)#/', $src['template'], $match);
        $match && $attributes = $match[1];

        return $attributes;
    }

    //########################################

    /**
     * @return int
     */
    public function getMfgPartNumberMode()
    {
        return (int)$this->getData('mfg_part_number_mode');
    }

    public function getMfgPartNumberCustomValue()
    {
        return $this->getData('mfg_part_number_custom_value');
    }

    public function getMfgPartNumberCustomAttribute()
    {
        return $this->getData('mfg_part_number_custom_attribute');
    }

    /**
     * @return bool
     */
    public function isMfgPartNumberCustomValue()
    {
        return $this->getMfgPartNumberMode() == self::MFG_PART_NUMBER_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isMfgPartNumberCustomAttribute()
    {
        return $this->getMfgPartNumberMode() == self::MFG_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getMfgPartNumberSource()
    {
        return array(
            'mode'     => $this->getMfgPartNumberMode(),
            'custom_value' => $this->getMfgPartNumberCustomValue(),
            'custom_attribute' => $this->getMfgPartNumberCustomAttribute(),
        );
    }

    //########################################

    /**
     * @return int
     */
    public function getProductSetIdMode()
    {
        return (int)$this->getData('product_set_id_mode');
    }

    public function getProductSetIdCustomValue()
    {
        return $this->getData('product_set_id_custom_value');
    }

    public function getProductSetIdCustomAttribute()
    {
        return $this->getData('product_set_id_custom_attribute');
    }

    /**
     * @return bool
     */
    public function isProductSetIdNone()
    {
        return $this->getProductSetIdMode() == self::PRODUCT_SET_ID_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isProductSetIdCustomValue()
    {
        return $this->getProductSetIdMode() == self::PRODUCT_SET_ID_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isProductSetIdCustomAttribute()
    {
        return $this->getProductSetIdMode() == self::PRODUCT_SET_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getProductSetIdSource()
    {
        return array(
            'mode'     => $this->getProductSetIdMode(),
            'custom_value' => $this->getProductSetIdCustomValue(),
            'custom_attribute' => $this->getProductSetIdCustomAttribute(),
        );
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
        return $this->getTitleMode() == self::TITLE_MODE_PRODUCT_NAME;
    }

    /**
     * @return bool
     */
    public function isTitleCustomTemplate()
    {
        return $this->getTitleMode() == self::TITLE_MODE_CUSTOM_TEMPLATE;
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

        if ($src['mode'] == self::TITLE_MODE_PRODUCT_NAME) {
            $attributes[] = 'name';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //########################################

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
    public function isDescriptionProductFull()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_PRODUCT_FULL;
    }

    /**
     * @return bool
     */
    public function isDescriptionProductShort()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_PRODUCT_SHORT;
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

        if ($src['mode'] == self::DESCRIPTION_MODE_PRODUCT_FULL) {
            $attributes[] = 'description';
        } elseif ($src['mode'] == self::DESCRIPTION_MODE_PRODUCT_SHORT) {
            $attributes[] = 'short_description';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return int
     */
    public function getMainImageMode()
    {
        return (int)$this->getData('main_image_mode');
    }

    /**
     * @return bool
     */
    public function isMainImageBroductBase()
    {
        return $this->getMainImageMode() == self::IMAGE_MAIN_MODE_PRODUCT_BASE;
    }

    /**
     * @return bool
     */
    public function isMainImageAttribute()
    {
        return $this->getMainImageMode() == self::IMAGE_MAIN_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getMainImageSource()
    {
        return array(
            'mode'     => $this->getMainImageMode(),
            'attribute' => $this->getData('main_image_attribute')
        );
    }

    /**
     * @return array
     */
    public function getMainImageAttributes()
    {
        $attributes = array();
        $src = $this->getMainImageSource();

        if ($src['mode'] == self::IMAGE_MAIN_MODE_PRODUCT_BASE) {
            $attributes[] = 'image';
        } else if ($src['mode'] == self::IMAGE_MAIN_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return int
     */
    public function getAdditionalImageMode()
    {
        return (int)$this->getData('additional_images_mode');
    }

    /**
     * @return bool
     */
    public function isAdditionalImageNone()
    {
        return $this->getAdditionalImageMode() == self::ADDITIONAL_IMAGES_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isAdditionalImageProduct()
    {
        return $this->getAdditionalImageMode() == self::ADDITIONAL_IMAGES_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isAdditionalImageCustomAttribute()
    {
        return $this->getAdditionalImageMode() == self::ADDITIONAL_IMAGES_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getAdditionalImageSource()
    {
        return array(
            'mode'     => $this->getAdditionalImageMode(),
            'attribute' => $this->getData('additional_images_attribute'),
            'limit' => $this->getData('additional_images_limit')
        );
    }

    /**
     * @return array
     */
    public function getAdditionalImageAttributes()
    {
        $attributes = array();
        $src = $this->getAdditionalImageSource();

        if ($src['mode'] == self::ADDITIONAL_IMAGES_MODE_CUSTOM_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return int
     */
    public function getKeywordsMode()
    {
        return (int)$this->getData('keywords_mode');
    }

    /**
     * @return bool
     */
    public function isKeywordsNone()
    {
        return $this->getKeywordsMode() == self::KEYWORDS_MODE_NONE;
    }

    public function getKeywordsCustomAttribute()
    {
        return $this->getData('keywords_custom_attribute');
    }

    public function getKeywordsCustomValue()
    {
        return $this->getData('keywords_custom_value');
    }

    /**
     * @return bool
     */
    public function isKeywordsCustomAttribute()
    {
        return $this->getKeywordsMode() == self::KEYWORDS_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return bool
     */
    public function isKeywordsCustomValue()
    {
        return $this->getKeywordsMode() == self::KEYWORDS_MODE_CUSTOM_VALUE;
    }

    /**
     * @return array
     */
    public function getKeywordsSource()
    {
        return array(
            'mode'     => $this->getKeywordsMode(),
            'custom_value' => $this->getKeywordsCustomValue(),
            'custom_attribute' => $this->getKeywordsCustomAttribute()
        );
    }

    /**
     * @return array
     */
    public function getKeywordsAttributes()
    {
        $src = $this->getKeywordsSource();

        if ($src['mode'] == self::KEYWORDS_MODE_NONE) {
            return array();
        }

        $attributes = array();

        if ($src['mode'] == self::KEYWORDS_MODE_CUSTOM_ATTRIBUTE) {
            $match = array();
            $keywords = implode(PHP_EOL,$src['template']);
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $keywords, $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return int
     */
    public function getFeaturesMode()
    {
        return (int)$this->getData('features_mode');
    }

    /**
     * @return bool
     */
    public function isFeaturesNone()
    {
        return $this->getFeaturesMode() == self::FEATURES_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isFeaturesCustomTemplate()
    {
        return $this->getFeaturesMode() == self::FEATURES_MODE_CUSTOM_TEMPLATE;
    }

    /**
     * @return array
     */
    public function getFeaturesSource()
    {
        return array(
            'mode'     => $this->getFeaturesMode(),
            'template' => $this->getArrayFeatures()
        );
    }

    public function getArrayFeatures()
    {
        $value = $this->getData('features_template');
        return is_null($value) ? array() : json_decode($value, true);
    }

    /**
     * @return array
     */
    public function getFeaturesAttributes()
    {
        $src = $this->getFeaturesSource();

        if ($src['mode'] == self::FEATURES_MODE_NONE) {
            return array();
        }

        $attributes = array();

        if ($src['mode'] == self::FEATURES_MODE_CUSTOM_TEMPLATE) {
            $match = array();
            $features = implode(PHP_EOL,$src['template']);
            preg_match_all('/#([a-zA-Z_0-9]+?)#/', $features, $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //########################################

    /**
     * @return int
     */
    public function getWeightMode()
    {
        return (int)$this->getData('weight_mode');
    }

    public function getWeightCustomValue()
    {
        return $this->getData('weight_custom_value');
    }

    public function getWeightCustomAttribute()
    {
        return $this->getData('weight_custom_attribute');
    }

    /**
     * @return bool
     */
    public function isWeightCustomValue()
    {
        return $this->getWeightMode() == self::WEIGHT_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isWeightCustomAttribute()
    {
        return $this->getWeightMode() == self::WEIGHT_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getWeightSource()
    {
        return array(
            'mode'     => $this->getWeightMode(),
            'custom_value' => $this->getWeightCustomValue(),
            'custom_attribute' => $this->getWeightCustomAttribute(),
        );
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_newproduct_core');
        return parent::save();
    }

    //########################################
}