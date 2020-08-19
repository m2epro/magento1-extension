<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m06_GeneralConfig extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $config = $this->_installer->getMainConfigModifier();

        $config->getEntity('/view/ebay/notice/', 'disable_collapse')->delete();

        $config->getEntity('/listing/product/inspector/', 'mode')
            ->updateGroup('/general/configuration/')
            ->updateKey('listing_product_inspector_mode');

        $config->getEntity('/view/', 'show_block_notices')
            ->updateGroup('/general/configuration/')
            ->updateKey('view_show_block_notices_mode');

        $config->getEntity('/view/', 'show_products_thumbnails')
            ->updateGroup('/general/configuration/')
            ->updateKey('view_show_products_thumbnails_mode');

        $config->getEntity('/view/products_grid/', 'use_alternative_mysql_select')
            ->updateGroup('/general/configuration/')
            ->updateKey('view_products_grid_use_alternative_mysql_select_mode');

        $config->getEntity('/renderer/description/', 'convert_linebreaks')
            ->updateGroup('/general/configuration/')
            ->updateKey('renderer_description_convert_linebreaks_mode');

        $config->getEntity('/other/paypal/', 'url')
            ->updateGroup('/general/configuration/')
            ->updateKey('other_pay_pal_url');

        $config->getEntity('/product/index/', 'mode')
            ->updateGroup('/general/configuration/')
            ->updateKey('product_index_mode');

        $config->getEntity('/product/force_qty/', 'mode')
            ->updateGroup('/general/configuration/')
            ->updateKey('product_force_qty_mode');

        $config->getEntity('/product/force_qty/', 'value')
            ->updateGroup('/general/configuration/')
            ->updateKey('product_force_qty_value');

        $config->getEntity('/qty/percentage/', 'rounding_greater')
            ->updateGroup('/general/configuration/')
            ->updateKey('qty_percentage_rounding_greater');

        $config->getEntity('/magento/attribute/', 'price_type_converting')
            ->updateGroup('/general/configuration/')
            ->updateKey('magento_attribute_price_type_converting_mode');

        $config->getEntity('/order/magento/settings/', 'create_with_first_product_options_when_variation_unavailable')
            ->updateGroup('/general/configuration/');
    }

    //########################################
}
