<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Configuration extends Mage_Core_Helper_Abstract
{
    const CONFIG_GROUP = '/general/configuration/';

    //########################################

    public function getViewShowProductsThumbnailsMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'view_show_products_thumbnails_mode'
        );
    }

    public function getViewShowBlockNoticesMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'view_show_block_notices_mode'
        );
    }

    public function getProductForceQtyMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'product_force_qty_mode'
        );
    }

    public function isEnableProductForceQtyMode()
    {
        return $this->getProductForceQtyMode() == 1;
    }

    public function getProductForceQtyValue()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'product_force_qty_value'
        );
    }

    public function getMagentoAttributePriceTypeConvertingMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'magento_attribute_price_type_converting_mode'
        );
    }

    public function isEnableMagentoAttributePriceTypeConvertingMode()
    {
        return $this->getMagentoAttributePriceTypeConvertingMode() == 1;
    }

    public function getListingProductInspectorMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'listing_product_inspector_mode'
        );
    }

    public function isEnableListingProductInspectorMode()
    {
        return $this->getListingProductInspectorMode() == 1;
    }

    public function getGroupedProductMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'grouped_product_mode'
        );
    }

    public function isGroupedProductModeOptions()
    {
        return $this->getGroupedProductMode() == Ess_M2ePro_Model_Listing_Product::GROUPED_PRODUCT_MODE_OPTIONS;
    }

    public function isGroupedProductModeSet()
    {
        return $this->getGroupedProductMode() == Ess_M2ePro_Model_Listing_Product::GROUPED_PRODUCT_MODE_SET;
    }

    //########################################

    public function getSecureImageUrlInItemDescriptionMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'secure_image_url_in_item_description_mode'
        );
    }

    public function getViewProductsGridUseAlternativeMysqlSelectMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'view_products_grid_use_alternative_mysql_select_mode'
        );
    }

    public function getOtherPayPalUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'other_pay_pal_url'
        );
    }

    public function getProductIndexMode()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'product_index_mode'
        );
    }

    public function getQtyPercentageRoundingGreater()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'qty_percentage_rounding_greater'
        );
    }

    public function getCreateWithFirstProductOptionsWhenVariationUnavailable()
    {
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::CONFIG_GROUP, 'create_with_first_product_options_when_variation_unavailable'
        );
    }

    //########################################

    public function setConfigValues(array $values)
    {
        if (isset($values['view_show_products_thumbnails_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP,
                'view_show_products_thumbnails_mode',
                $values['view_show_products_thumbnails_mode']
            );
        }

        if (isset($values['view_show_block_notices_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP,
                'view_show_block_notices_mode',
                $values['view_show_block_notices_mode']
            );
        }

        if (isset($values['product_force_qty_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP,
                'product_force_qty_mode',
                $values['product_force_qty_mode']
            );
        }

        if (isset($values['product_force_qty_value'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP,
                'product_force_qty_value',
                $values['product_force_qty_value']
            );
        }

        if (isset($values['magento_attribute_price_type_converting_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP,
                'magento_attribute_price_type_converting_mode',
                $values['magento_attribute_price_type_converting_mode']
            );
        }

        if (isset($values['listing_product_inspector_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP,
                'listing_product_inspector_mode',
                $values['listing_product_inspector_mode']
            );
        }

        if (isset($values['grouped_product_mode'])) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                self::CONFIG_GROUP,
                'grouped_product_mode',
                $values['grouped_product_mode']
            );
        }
    }

    //########################################
}
