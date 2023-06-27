<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Install_Walmart extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->run(
            <<<SQL

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_account')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_account')}` (
  `account_id` INT(11) UNSIGNED NOT NULL,
  `server_hash` VARCHAR(255) NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `consumer_id` VARCHAR(255) DEFAULT NULL,
  `private_key` TEXT DEFAULT NULL,
  `client_id` VARCHAR(255) DEFAULT NULL,
  `client_secret` TEXT DEFAULT NULL,
  `related_store_id` INT(11) NOT NULL DEFAULT 0,
  `other_listings_synchronization` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `other_listings_mapping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `other_listings_mapping_settings` TEXT DEFAULT NULL,
  `magento_orders_settings` TEXT NOT NULL,
  `other_carriers` TEXT DEFAULT NULL,
  `create_magento_invoice` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `create_magento_shipment` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `orders_last_synchronization` DATETIME DEFAULT NULL,
  `inventory_last_synchronization` DATETIME DEFAULT NULL,
  `info` TEXT DEFAULT NULL,
  PRIMARY KEY (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_dictionary_category')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_dictionary_category')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `parent_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `browsenode_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `product_data_nicks` TEXT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `path` TEXT DEFAULT NULL,
  `keywords` TEXT DEFAULT NULL,
  `is_leaf` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `browsenode_id` (`browsenode_id`),
  INDEX `category_id` (`category_id`),
  INDEX `is_leaf` (`is_leaf`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `path` (`path`(255)),
  INDEX `parent_category_id` (`parent_category_id`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_dictionary_marketplace')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_dictionary_marketplace')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `client_details_last_update_date` DATETIME DEFAULT NULL,
  `server_details_last_update_date` DATETIME DEFAULT NULL,
  `product_data` LONGTEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_dictionary_specific')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_dictionary_specific')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `specific_id` INT(11) UNSIGNED NOT NULL,
  `parent_specific_id` INT(11) UNSIGNED DEFAULT NULL,
  `product_data_nick` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `xml_tag` VARCHAR(255) NOT NULL,
  `xpath` VARCHAR(255) NOT NULL,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `values` TEXT DEFAULT NULL,
  `recommended_values` TEXT DEFAULT NULL,
  `params` TEXT DEFAULT NULL,
  `data_definition` TEXT DEFAULT NULL,
  `min_occurs` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1,
  `max_occurs` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `max_occurs` (`max_occurs`),
  INDEX `min_occurs` (`min_occurs`),
  INDEX `parent_specific_id` (`parent_specific_id`),
  INDEX `title` (`title`),
  INDEX `type` (`type`),
  INDEX `specific_id` (`specific_id`),
  INDEX `xml_tag` (`xml_tag`),
  INDEX `xpath` (`xpath`),
  INDEX `product_data_nick` (`product_data_nick`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_item')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_item')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `store_id` INT(11) UNSIGNED NOT NULL,
  `variation_product_options` TEXT DEFAULT NULL,
  `variation_channel_options` TEXT DEFAULT NULL,
  `additional_data` TEXT NULL DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `product_id` (`product_id`),
  INDEX `sku` (`sku`),
  INDEX `store_id` (`store_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_listing')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_listing')}` (
  `listing_id` INT(11) UNSIGNED NOT NULL,
  `auto_global_adding_category_template_id` int(11) UNSIGNED DEFAULT NULL,
  `auto_website_adding_category_template_id` int(11) UNSIGNED DEFAULT NULL,
  `template_description_id` INT(11) UNSIGNED NOT NULL,
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `template_synchronization_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_id`),
  INDEX `auto_global_adding_category_template_id` (`auto_global_adding_category_template_id`),
  INDEX `auto_website_adding_category_template_id` (`auto_website_adding_category_template_id`),
  INDEX `template_selling_format_id` (`template_selling_format_id`),
  INDEX `template_description_id` (`template_description_id`),
  INDEX `template_synchronization_id` (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_listing_auto_category_group')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_listing_auto_category_group')}` (
    `listing_auto_category_group_id` int(11) UNSIGNED NOT NULL,
    `adding_category_template_id` int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`listing_auto_category_group_id`),
    INDEX `adding_category_template_id` (`adding_category_template_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_listing_other')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_listing_other')}` (
  `listing_other_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `gtin` VARCHAR(255) DEFAULT NULL,
  `upc` VARCHAR(255) DEFAULT NULL,
  `ean` VARCHAR(255) DEFAULT NULL,
  `wpid` VARCHAR(255) DEFAULT NULL,
  `item_id` VARCHAR(255) DEFAULT NULL,
  `publish_status` VARCHAR(255) DEFAULT NULL,
  `lifecycle_status` VARCHAR(255) DEFAULT NULL,
  `status_change_reasons` TEXT DEFAULT NULL,
  `is_online_price_invalid` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `title` VARCHAR(255) DEFAULT NULL,
  `online_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`listing_other_id`),
  INDEX `online_price` (`online_price`),
  INDEX `online_qty` (`online_qty`),
  INDEX `sku` (`sku`),
  INDEX `gtin` (`gtin`),
  INDEX `upc` (`upc`),
  INDEX `ean` (`ean`),
  INDEX `wpid` (`wpid`),
  INDEX `item_id` (`item_id`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_listing_product')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_listing_product')}` (
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `template_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `is_variation_product` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_variation_product_matched` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_variation_channel_matched` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_variation_parent` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `variation_parent_id` INT(11) UNSIGNED DEFAULT NULL,
  `variation_parent_need_processor` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `variation_child_statuses` TEXT DEFAULT NULL,
  `sku` VARCHAR(255) DEFAULT NULL,
  `gtin` VARCHAR(255) DEFAULT NULL,
  `upc` VARCHAR(255) DEFAULT NULL,
  `ean` VARCHAR(255) DEFAULT NULL,
  `isbn` VARCHAR(255) DEFAULT NULL,
  `wpid` VARCHAR(255) DEFAULT NULL,
  `item_id` VARCHAR(255) DEFAULT NULL,
  `publish_status` VARCHAR(255) DEFAULT NULL,
  `lifecycle_status` VARCHAR(255) DEFAULT NULL,
  `status_change_reasons` TEXT DEFAULT NULL,
  `is_stopped_manually` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `online_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `is_online_price_invalid` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `online_promotions` VARCHAR(40) DEFAULT NULL,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  `online_lag_time` INT(11) UNSIGNED DEFAULT NULL,
  `online_details_data` VARCHAR(40) DEFAULT NULL,
  `online_start_date` DATETIME DEFAULT NULL,
  `online_end_date` DATETIME DEFAULT NULL,
  `is_missed_on_channel` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `list_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  INDEX `is_variation_product_matched` (`is_variation_product_matched`),
  INDEX `is_variation_channel_matched` (`is_variation_channel_matched`),
  INDEX `is_variation_product` (`is_variation_product`),
  INDEX `online_price` (`online_price`),
  INDEX `online_qty` (`online_qty`),
  INDEX `sku` (`sku`),
  INDEX `gtin` (`gtin`),
  INDEX `upc` (`upc`),
  INDEX `ean` (`ean`),
  INDEX `isbn` (`isbn`),
  INDEX `wpid` (`wpid`),
  INDEX `item_id` (`item_id`),
  INDEX `online_start_date` (`online_start_date`),
  INDEX `online_end_date` (`online_end_date`),
  INDEX `is_variation_parent` (`is_variation_parent`),
  INDEX `variation_parent_need_processor` (`variation_parent_need_processor`),
  INDEX `variation_parent_id` (`variation_parent_id`),
  INDEX `template_category_id` (`template_category_id`),
  INDEX `list_date` (`list_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_listing_product_variation')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_listing_product_variation')}` (
  `listing_product_variation_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_product_variation_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_listing_product_variation_option')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_listing_product_variation_option')}` (
  `listing_product_variation_option_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_product_variation_option_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_listing_product_action_processing')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_listing_product_action_processing')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `request_pending_single_id` INT(11) UNSIGNED DEFAULT NULL,
  `listing_product_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `is_prepared` TINYINT(2) NOT NULL DEFAULT 0,
  `group_hash` VARCHAR(255) DEFAULT NULL,
  `request_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `request_pending_single_id` (`request_pending_single_id`),
  INDEX `type` (`type`),
  INDEX `is_prepared` (`is_prepared`),
  INDEX `group_hash` (`group_hash`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_listing_product_action_processing_list')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_listing_product_action_processing_list')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `stage` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `relist_request_pending_single_id` INT(11) UNSIGNED DEFAULT NULL,
  `relist_request_data` LONGTEXT DEFAULT NULL,
  `relist_configurator_data` LONGTEXT DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id__sku` (`account_id`, `sku`),
  INDEX `stage` (`stage`),
  INDEX `listing_product_id` (`listing_product_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_listing_product_indexer_variation_parent')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_listing_product_indexer_variation_parent')}` (
    `listing_product_id` INT(11) UNSIGNED NOT NULL,
    `listing_id` INT(11) UNSIGNED NOT NULL,
    `min_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `max_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `create_date` DATETIME NOT NULL,
    PRIMARY KEY (`listing_product_id`),
    INDEX `listing_id` (`listing_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_marketplace')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_marketplace')}` (
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `developer_key` VARCHAR(255) DEFAULT NULL,
  `default_currency` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_order')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_order')}` (
  `order_id` INT(11) UNSIGNED NOT NULL,
  `walmart_order_id` VARCHAR(255) NOT NULL,
  `customer_order_id` VARCHAR(255) NOT NULL DEFAULT '',
  `status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `buyer_name` VARCHAR(255) NOT NULL,
  `buyer_email` VARCHAR(255) DEFAULT NULL,
  `shipping_service` VARCHAR(255) DEFAULT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_price` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `shipping_date_to` DATETIME DEFAULT NULL,
  `paid_amount` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `tax_details` TEXT DEFAULT NULL,
  `currency` VARCHAR(10) NOT NULL,
  `is_tried_to_acknowledge` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `purchase_update_date` DATETIME DEFAULT NULL,
  `purchase_create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  INDEX `walmart_order_id` (`walmart_order_id`),
  INDEX `customer_order_id` (`customer_order_id`),
  INDEX `buyer_email` (`buyer_email`),
  INDEX `buyer_name` (`buyer_name`),
  INDEX `shipping_date_to` (`shipping_date_to`),
  INDEX `paid_amount` (`paid_amount`),
  INDEX `is_tried_to_acknowledge` (`is_tried_to_acknowledge`),
  INDEX `purchase_create_date` (`purchase_create_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_order_item')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_order_item')}` (
  `order_item_id` INT(11) UNSIGNED NOT NULL,
  `walmart_order_item_id` VARCHAR(255) NOT NULL,
  `merged_walmart_order_item_ids` TEXT DEFAULT NULL,
  `status` VARCHAR(30) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `qty_purchased` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `tracking_details` TEXT DEFAULT NULL,
  `buyer_cancellation_requested` SMALLINT(4) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_item_id`),
  INDEX `sku` (`sku`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_template_category')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_template_category')}` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `marketplace_id` INT(11) UNSIGNED NOT NULL,
    `product_data_nick` VARCHAR(255) DEFAULT NULL,
    `category_path` VARCHAR(255) DEFAULT NULL,
    `browsenode_id` DECIMAL(20, 0) UNSIGNED DEFAULT NULL,
    `update_date` DATETIME DEFAULT NULL,
    `create_date` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_template_category_specific')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_template_category_specific')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_category_id` INT(11) UNSIGNED NOT NULL,
  `xpath` VARCHAR(255) NOT NULL,
  `mode` VARCHAR(25) NOT NULL,
  `is_required` TINYINT(2) UNSIGNED DEFAULT 0,
  `custom_value` VARCHAR(255) DEFAULT NULL,
  `custom_attribute` VARCHAR(255) DEFAULT NULL,
  `type` VARCHAR(25) DEFAULT NULL,
  `attributes` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `template_category_id` (`template_category_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_template_description')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_template_description')}` (
  `template_description_id` INT(11) UNSIGNED NOT NULL,
  `title_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `title_template` VARCHAR(255) NOT NULL,
  `brand_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `brand_custom_value` VARCHAR(255) DEFAULT NULL,
  `brand_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `manufacturer_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `manufacturer_custom_value` VARCHAR(255) DEFAULT NULL,
  `manufacturer_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `manufacturer_part_number_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `manufacturer_part_number_custom_value` VARCHAR(255) NOT NULL,
  `manufacturer_part_number_custom_attribute` VARCHAR(255) NOT NULL,
  `model_number_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `model_number_custom_value` VARCHAR(255) NOT NULL,
  `model_number_custom_attribute` VARCHAR(255) NOT NULL,
  `msrp_rrp_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `msrp_rrp_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `image_main_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `image_main_attribute` VARCHAR(255) NOT NULL,
  `image_variation_difference_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `image_variation_difference_attribute` VARCHAR(255) NOT NULL,
  `gallery_images_mode` TINYINT(2) UNSIGNED NOT NULL,
  `gallery_images_limit` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `gallery_images_attribute` VARCHAR(255) NOT NULL,
  `description_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `description_template` LONGTEXT NOT NULL,
  `multipack_quantity_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `multipack_quantity_custom_value` VARCHAR(255) DEFAULT NULL,
  `multipack_quantity_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `count_per_pack_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `count_per_pack_custom_value` VARCHAR(255) DEFAULT NULL,
  `count_per_pack_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `total_count_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `total_count_custom_value` VARCHAR(255) DEFAULT NULL,
  `total_count_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `key_features_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `key_features` TEXT NOT NULL,
  `other_features_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `other_features` TEXT NOT NULL,
  `attributes_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `attributes` TEXT NOT NULL,
  PRIMARY KEY (`template_description_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_template_selling_format')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_template_selling_format')}` (
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `qty_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_custom_value` INT(11) UNSIGNED NOT NULL,
  `qty_custom_attribute` VARCHAR(255) NOT NULL,
  `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100,
  `qty_modification_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_min_posted_value` INT(11) UNSIGNED DEFAULT NULL,
  `qty_max_posted_value` INT(11) UNSIGNED DEFAULT NULL,
  `price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `price_custom_attribute` VARCHAR(255) NOT NULL,
  `price_coefficient` VARCHAR(255) NOT NULL,
  `price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `price_vat_percent` DECIMAL(10,2) UNSIGNED DEFAULT NULL,
  `promotions_mode` TINYINT(2) NOT NULL DEFAULT 0,
  `lag_time_mode` TINYINT(2) UNSIGNED NOT NULL,
  `lag_time_value` INT(11) UNSIGNED NOT NULL,
  `lag_time_custom_attribute` VARCHAR(255) NOT NULL,
  `item_weight_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_weight_custom_value` DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  `item_weight_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `must_ship_alone_mode` TINYINT(2) UNSIGNED NOT NULL,
  `must_ship_alone_value` TINYINT(2) UNSIGNED NOT NULL,
  `must_ship_alone_custom_attribute` VARCHAR(255) NOT NULL,
  `ships_in_original_packaging_mode` TINYINT(2) UNSIGNED NOT NULL,
  `ships_in_original_packaging_value` TINYINT(2) UNSIGNED NOT NULL,
  `ships_in_original_packaging_custom_attribute` VARCHAR(255) NOT NULL,
  `shipping_override_rule_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `sale_time_start_date_mode` TINYINT(2) UNSIGNED NOT NULL,
  `sale_time_start_date_value` DATETIME NOT NULL,
  `sale_time_start_date_custom_attribute` VARCHAR(255) NOT NULL,
  `sale_time_end_date_mode` TINYINT(2) UNSIGNED NOT NULL,
  `sale_time_end_date_value` DATETIME NOT NULL,
  `sale_time_end_date_custom_attribute` VARCHAR(255) NOT NULL,
  `attributes_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `attributes` TEXT NOT NULL,
  PRIMARY KEY (`template_selling_format_id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_template_selling_format_promotion')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_template_selling_format_promotion')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `start_date_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `start_date_attribute` VARCHAR(255) DEFAULT NULL,
  `start_date_value` DATETIME DEFAULT NULL,
  `end_date_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `end_date_attribute` VARCHAR(255) DEFAULT NULL,
  `end_date_value` DATETIME DEFAULT NULL,
  `price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `price_attribute` VARCHAR(255) NOT NULL,
  `price_coefficient` VARCHAR(255) NOT NULL,
  `comparison_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `comparison_price_attribute` VARCHAR(255) NOT NULL,
  `comparison_price_coefficient` VARCHAR(255) NOT NULL,
  `type` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `template_selling_format_id` (`template_selling_format_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_template_selling_format_shipping_override')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_template_selling_format_shipping_override')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `method` VARCHAR(255) NOT NULL,
  `is_shipping_allowed` VARCHAR(255) NOT NULL,
  `region` VARCHAR(255) NOT NULL,
  `cost_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `cost_value` VARCHAR(255) NOT NULL,
  `cost_attribute` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `template_selling_format_id` (`template_selling_format_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_template_synchronization')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_walmart_template_synchronization')}` (
  `template_synchronization_id` INT(11) UNSIGNED NOT NULL,
  `list_mode` TINYINT(2) UNSIGNED NOT NULL,
  `list_status_enabled` TINYINT(2) UNSIGNED NOT NULL,
  `list_is_in_stock` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `list_advanced_rules_mode` TINYINT(2) UNSIGNED NOT NULL,
  `list_advanced_rules_filters` TEXT DEFAULT NULL,
  `revise_update_qty` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_qty_max_applied_value_mode` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_qty_max_applied_value` INT(11) UNSIGNED DEFAULT NULL,
  `revise_update_price` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_promotions` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_details` TINYINT(2) UNSIGNED NOT NULL,
  `relist_mode` TINYINT(2) UNSIGNED NOT NULL,
  `relist_filter_user_lock` TINYINT(2) UNSIGNED NOT NULL,
  `relist_status_enabled` TINYINT(2) UNSIGNED NOT NULL,
  `relist_is_in_stock` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `relist_advanced_rules_mode` TINYINT(2) UNSIGNED NOT NULL,
  `relist_advanced_rules_filters` TEXT DEFAULT NULL,
  `stop_mode` TINYINT(2) UNSIGNED NOT NULL,
  `stop_status_disabled` TINYINT(2) UNSIGNED NOT NULL,
  `stop_out_off_stock` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `stop_advanced_rules_mode` TINYINT(2) UNSIGNED NOT NULL,
  `stop_advanced_rules_filters` TEXT DEFAULT NULL,
  PRIMARY KEY (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );

        $this->_installer->run(
            <<<SQL

INSERT INTO `{$this->_installer->getTable('m2epro_config')}` (`group`,`key`,`value`,`update_date`,`create_date`) VALUES
  ('/walmart/', 'application_name', 'M2ePro - Walmart Magento Integration', NOW(), NOW()),
  ('/component/walmart/', 'mode', '1', NOW(), NOW()),
  ('/cron/task/walmart/listing/product/process_instructions/', 'mode', '1', NOW(), NOW()),
  ('/walmart/configuration/', 'sku_mode', '1', NOW(), NOW()),
  ('/walmart/configuration/', 'sku_custom_attribute', NULL, NOW(), NOW()),
  ('/walmart/configuration/', 'sku_modification_mode', '0', NOW(), NOW()),
  ('/walmart/configuration/', 'sku_modification_custom_value', NULL, NOW(), NOW()),
  ('/walmart/configuration/', 'generate_sku_mode', '0', NOW(), NOW()),
  ('/walmart/configuration/', 'product_id_override_mode', '0', NOW(), NOW()),   
  ('/walmart/configuration/', 'product_id_mode', '0', NOW(), NOW()),
  ('/walmart/configuration/', 'product_id_custom_attribute', NULL, NOW(), NOW()),
  ('/walmart/configuration/', 'option_images_url_mode', '0', NOW(), NOW()),  
  ('/listing/product/inspector/walmart/', 'max_allowed_instructions_count', '2000', NOW(), NOW()),
  ('/walmart/listing/product/action/scheduled_data/', 'limit', '20000', NOW(), NOW()),
  ('/walmart/listing/product/action/processing/prepare/', 'max_listings_products_count', '2000', NOW(), NOW()),
  ('/walmart/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000', NOW(), NOW()),
  ('/walmart/listing/product/action/list/', 'min_allowed_wait_interval', '3600', NOW(), NOW()),
  ('/walmart/listing/product/action/relist/', 'min_allowed_wait_interval', '1800', NOW(), NOW()),
  ('/walmart/listing/product/action/revise_qty/', 'min_allowed_wait_interval', '900', NOW(), NOW()),
  ('/walmart/listing/product/action/revise_price/', 'min_allowed_wait_interval', '1800', NOW(), NOW()),
  ('/walmart/listing/product/action/revise_details/', 'min_allowed_wait_interval', '7200', NOW(), NOW()),
  ('/walmart/listing/product/action/revise_promotions/', 'min_allowed_wait_interval', '7200', NOW(), NOW()),
  ('/walmart/listing/product/action/revise_lag_time/', 'min_allowed_wait_interval', '7200', NOW(), NOW()),
  ('/walmart/listing/product/action/stop/', 'min_allowed_wait_interval', '600', NOW(), NOW()),
  ('/walmart/listing/product/action/delete/', 'min_allowed_wait_interval', '600', NOW(), NOW()),
  ('/cron/task/walmart/listing/synchronize_inventory/', 'interval_per_account', '86400', NOW(), NOW()),
  ('/walmart/order/settings/marketplace_25/', 'use_first_street_line_as_company', '1', NOW(), NOW());

INSERT INTO `{$this->_installer->getTable('m2epro_marketplace')}` VALUES
  (37, 1, 'United States', 'US', 'walmart.com', 0, 3, 'America', 'walmart', NOW(), NOW()),
  (38, 2, 'Canada', 'CA', 'walmart.ca', 0, 4, 'America', 'walmart', NOW(), NOW());

INSERT INTO `{$this->_installer->getTable('m2epro_walmart_marketplace')}` VALUES
  (37, '8636-1433-4377', 'USD'),
  (38, '7078-7205-1944', 'CAD');

SQL
        );
    }

    //########################################
}
