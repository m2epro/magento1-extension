<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Walmart_Configuration extends Mage_Core_Helper_Abstract
{
    const SKU_MODE_DEFAULT          = 1;
    const SKU_MODE_CUSTOM_ATTRIBUTE = 2;
    const SKU_MODE_PRODUCT_ID       = 3;

    const SKU_MODIFICATION_MODE_NONE     = 0;
    const SKU_MODIFICATION_MODE_PREFIX   = 1;
    const SKU_MODIFICATION_MODE_POSTFIX  = 2;
    const SKU_MODIFICATION_MODE_TEMPLATE = 3;

    const PRODUCT_ID_OVERRIDE_MODE_NONE              = 0;
    const PRODUCT_ID_OVERRIDE_MODE_ALL               = 1;
    const PRODUCT_ID_OVERRIDE_MODE_SPECIFIC_PRODUCTS = 2;
    const PRODUCT_ID_OVERRIDE_CUSTOM_CODE = 'CUSTOM';

    const PRODUCT_ID_MODE_NOT_SET          = 0;
    const PRODUCT_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    const OPTION_IMAGES_URL_MODE_ORIGINAL = 0;
    const OPTION_IMAGES_URL_MODE_HTTP     = 1;
    const OPTION_IMAGES_URL_MODE_HTTPS    = 2;

    const CONFIG_GROUP = '/walmart/configuration/';

    //########################################

    public function getSkuMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(self::CONFIG_GROUP, 'sku_mode');
    }

    public function isSkuModeDefault()
    {
        return $this->getSkuMode() == self::SKU_MODE_DEFAULT;
    }

    public function isSkuModeCustomAttribute()
    {
        return $this->getSkuMode() == self::SKU_MODE_CUSTOM_ATTRIBUTE;
    }

    public function isSkuModeProductId()
    {
        return $this->getSkuMode() == self::SKU_MODE_PRODUCT_ID;
    }

    //----------------------------------------

    public function getSkuCustomAttribute()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(self::CONFIG_GROUP, 'sku_custom_attribute');
    }

    //----------------------------------------

    public function getSkuModificationMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'sku_modification_mode'
        );
    }

    public function isSkuModificationModeNone()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_NONE;
    }

    public function isSkuModificationModePrefix()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_PREFIX;
    }

    public function isSkuModificationModePostfix()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_POSTFIX;
    }

    public function isSkuModificationModeTemplate()
    {
        return $this->getSkuModificationMode() == self::SKU_MODIFICATION_MODE_TEMPLATE;
    }

    //----------------------------------------

    public function getSkuModificationCustomValue()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'sku_modification_custom_value'
        );
    }

    //----------------------------------------

    public function getGenerateSkuMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(self::CONFIG_GROUP, 'generate_sku_mode');
    }

    public function isGenerateSkuModeYes()
    {
        return $this->getGenerateSkuMode() == 1;
    }

    //########################################

    public function getProductIdOverrideMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP,
            'product_id_override_mode'
        );
    }

    public function isProductIdOverrideModeNode()
    {
        return $this->getProductIdOverrideMode() == self::PRODUCT_ID_OVERRIDE_MODE_NONE;
    }

    public function isProductIdOverrideModeAll()
    {
        return $this->getProductIdOverrideMode() == self::PRODUCT_ID_OVERRIDE_MODE_ALL;
    }

    public function isProductIdOverrideModeSpecificProducts()
    {
        return $this->getProductIdOverrideMode() == self::PRODUCT_ID_OVERRIDE_MODE_SPECIFIC_PRODUCTS;
    }

    //########################################

    public function getProductIdMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(self::CONFIG_GROUP, 'product_id_mode');
    }

    public function getProductIdCustomAttribute()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'product_id_custom_attribute'
        );
    }

    public function isProductIdModeNotSet()
    {
        return $this->getProductIdMode() == self::PRODUCT_ID_MODE_NOT_SET;
    }

    //----------------------------------------

    public function isProductIdModeCustomAttribute()
    {
        return $this->getProductIdMode() == self::PRODUCT_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    //########################################

    public function getOptionImagesURLMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'option_images_url_mode'
        );
    }

    public function isOptionImagesURLOriginalMode()
    {
        return $this->getOptionImagesURLMode() == self::OPTION_IMAGES_URL_MODE_ORIGINAL;
    }

    public function isOptionImagesURLHTTPSMode()
    {
        return $this->getOptionImagesURLMode() == self::OPTION_IMAGES_URL_MODE_HTTPS;
    }

    public function isOptionImagesURLHTTPMode()
    {
        return $this->getOptionImagesURLMode() == self::OPTION_IMAGES_URL_MODE_HTTP;
    }

    //########################################

    public function setConfigValues(array $values)
    {
        if (isset($values['sku_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'sku_mode', $values['sku_mode']
            );
        }

        if (isset($values['sku_custom_attribute'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'sku_custom_attribute', $values['sku_custom_attribute']
            );
        }

        if (isset($values['sku_modification_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'sku_modification_mode', $values['sku_modification_mode']
            );
        }

        if (isset($values['sku_modification_custom_value'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'sku_modification_custom_value', $values['sku_modification_custom_value']
            );
        }

        if (isset($values['generate_sku_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'generate_sku_mode', $values['generate_sku_mode']
            );
        }

        if (isset($values['product_id_override_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'product_id_override_mode',
                $values['product_id_override_mode']
            );
        }

        if (isset($values['product_id_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'product_id_mode', $values['product_id_mode']
            );
        }

        if (isset($values['product_id_custom_attribute'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'product_id_custom_attribute', $values['product_id_custom_attribute']
            );
        }

        if (isset($values['option_images_url_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP, 'option_images_url_mode', $values['option_images_url_mode']
            );
        }
    }

    //########################################
}
