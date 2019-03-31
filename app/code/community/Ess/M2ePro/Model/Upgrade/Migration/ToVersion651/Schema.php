<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_Schema extends Ess_M2ePro_Model_Upgrade_Migration_Abstract
{
    //########################################

    public function schemaCreate()
    {
        if ($this->installer->getTablesObject()->isExists('magento_product_websites_update')) {
            return;
        }

        $this->installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `m2epro_processing` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `model` VARCHAR(255) NOT NULL,
  `params` LONGTEXT DEFAULT NULL,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `result_data` LONGTEXT DEFAULT NULL,
  `result_messages` LONGTEXT DEFAULT NULL,
  `is_completed` TINYINT(2) NOT NULL DEFAULT 0,
  `expiration_date` DATETIME NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `model` (`model`),
  INDEX `type` (`type`),
  INDEX `is_completed` (`is_completed`),
  INDEX `expiration_date` (`expiration_date`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_request_pending_single` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `component` VARCHAR(12) NOT NULL,
  `server_hash` VARCHAR(255) NOT NULL,
  `result_data` LONGTEXT DEFAULT NULL,
  `result_messages` LONGTEXT DEFAULT NULL,
  `expiration_date` DATETIME NOT NULL,
  `is_completed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component` (`component`),
  INDEX `server_hash` (`server_hash`),
  INDEX `is_completed` (`is_completed`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_request_pending_partial` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `component` VARCHAR(12) NOT NULL,
  `server_hash` VARCHAR(255) NOT NULL,
  `next_part` INT(11) UNSIGNED DEFAULT NULL,
  `result_messages` LONGTEXT DEFAULT NULL,
  `expiration_date` DATETIME NOT NULL,
  `is_completed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component` (`component`),
  INDEX `server_hash` (`server_hash`),
  INDEX `next_part` (`next_part`),
  INDEX `is_completed` (`is_completed`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_request_pending_partial_data` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_pending_partial_id` INT(11) UNSIGNED NOT NULL,
  `part_number` INT(11) UNSIGNED NOT NULL,
  `data` LONGTEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `part_number` (`part_number`),
  INDEX `request_pending_partial_id` (`request_pending_partial_id`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_connector_command_pending_processing_single` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `request_pending_single_id` INT(11) UNSIGNED NOT NULL NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `request_pending_single_id` (`request_pending_single_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_connector_command_pending_processing_partial` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `request_pending_partial_id` INT(11) UNSIGNED NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `request_pending_partial_id` (`request_pending_partial_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_ebay_listing_product_action_processing` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `listing_product_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `request_timeout` INT(11) UNSIGNED DEFAULT NULL,
  `request_data` LONGTEXT NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `type` (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_listing_product_action_processing` (
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

CREATE TABLE IF NOT EXISTS `m2epro_amazon_listing_product_action_processing_list_sku` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id__sku` (`account_id`, `sku`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_order_action_processing` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED DEFAULT NULL,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `request_pending_single_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `request_data` LONGTEXT NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id` (`order_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `request_pending_single_id` (`request_pending_single_id`),
  INDEX `type` (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_ebay_listing_product_action_processing` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `listing_product_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `request_timeout` INT(11) UNSIGNED DEFAULT NULL,
  `request_data` LONGTEXT NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `type` (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_listing_product_scheduled_action` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `component` VARCHAR(10) DEFAULT NULL,
  `action_type` VARCHAR(12) NOT NULL,
  `is_force` TINYINT(2) NOT NULL DEFAULT 0,
  `tag` VARCHAR(255) DEFAULT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `listing_product_id` (`listing_product_id`),
  INDEX `component` (`component`),
  INDEX `action_type` (`action_type`),
  INDEX `tag` (`tag`),
  INDEX `create_date` (`create_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_listing_product_instruction` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `component` VARCHAR(10) DEFAULT NULL,
  `type` VARCHAR(255) NOT NULL,
  `initiator` VARCHAR(255) NOT NULL,
  `priority` INT(11) UNSIGNED NOT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `component` (`component`),
  INDEX `type` (`type`),
  INDEX `priority` (`priority`),
  INDEX `create_date` (`create_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_ebay_account_pickup_store` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `location_id` VARCHAR(255) DEFAULT NULL,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `phone` VARCHAR(255) NOT NULL,
  `postal_code` VARCHAR(50) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `utc_offset` VARCHAR(50) NOT NULL ,
  `country` VARCHAR(255) NOT NULL,
  `region` VARCHAR(255) NOT NULL,
  `city` VARCHAR(255) NOT NULL,
  `address_1` VARCHAR(255) NOT NULL,
  `address_2` VARCHAR(255) NOT NULL,
  `latitude` FLOAT,
  `longitude` FLOAT,
  `business_hours` TEXT NOT NULL,
  `special_hours` TEXT NOT NULL,
  `pickup_instruction` TEXT NOT NULL,
  `qty_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_custom_value` INT(11) UNSIGNED NOT NULL,
  `qty_custom_attribute` VARCHAR(255) NOT NULL,
  `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100,
  `qty_modification_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_min_posted_value` int(11) UNSIGNED DEFAULT NULL,
  `qty_max_posted_value` int(11) UNSIGNED DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `name` (`name`),
  INDEX `location_id` (`location_id`),
  INDEX `account_id` (`account_id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_ebay_account_pickup_store_state` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_pickup_store_id` INT(11) UNSIGNED NOT NULL,
  `is_in_processing` TINYINT(2) UNSIGNED DEFAULT 0,
  `sku` VARCHAR(255) NOT NULL,
  `online_qty` INT(11) NOT NULL,
  `target_qty` INT(11) NOT NULL,
  `is_added` TINYINT(2) NOT NULL DEFAULT 0,
  `is_deleted` TINYINT(2) NOT NULL DEFAULT 0,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_pickup_store_id` (`account_pickup_store_id`),
  INDEX `is_in_processing` (`is_in_processing`),
  INDEX `sku` (`sku`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_ebay_listing_product_pickup_store` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_product_id` INT(11) UNSIGNED,
  `account_pickup_store_id` INT(11) UNSIGNED,
  `is_process_required` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `account_pickup_store_id` (`account_pickup_store_id`),
  INDEX `is_process_required` (`is_process_required`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_ebay_account_pickup_store_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_pickup_store_state_id` INT(11) UNSIGNED DEFAULT NULL,
  `location_id` VARCHAR(255) NOT NULL,
  `location_title` VARCHAR(255) DEFAULT NULL,
  `action_id` INT(11) UNSIGNED DEFAULT NULL,
  `action` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `priority` TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  `description` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_pickup_store_state_id` (`account_pickup_store_state_id`),
  INDEX `location_id` (`location_id`),
  INDEX `location_title` (`location_title`),
  INDEX `action` (`action`),
  INDEX `action_id` (`action_id`),
  INDEX `priority` (`priority`),
  INDEX `type` (`type`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_ebay_indexer_listing_product_parent` (
    `listing_product_id` INT(11) UNSIGNED NOT NULL,
    `listing_id` INT(11) UNSIGNED NOT NULL,
    `component_mode` VARCHAR(10) DEFAULT NULL,
    `min_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `max_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `create_date` DATETIME NOT NULL,
    PRIMARY KEY (`listing_product_id`),
    INDEX `listing_id` (`listing_id`),
    INDEX `component_mode` (`component_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_indexer_listing_product_parent` (
    `listing_product_id` INT(11) UNSIGNED NOT NULL,
    `listing_id` INT(11) UNSIGNED NOT NULL,
    `component_mode` VARCHAR(10) DEFAULT NULL,
    `min_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `max_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `min_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `max_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `create_date` DATETIME NOT NULL,
    PRIMARY KEY (`listing_product_id`),
    INDEX `listing_id` (`listing_id`),
    INDEX `component_mode` (`component_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_template_shipping` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `template_name_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
    `template_name_value` VARCHAR(255) NOT NULL,
    `template_name_attribute` VARCHAR(255) NOT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_template_product_tax_code` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `product_tax_code_mode` TINYINT(2) NOT NULL,
    `product_tax_code_value` VARCHAR(255) DEFAULT NULL,
    `product_tax_code_attribute` VARCHAR(255) DEFAULT NULL,
    `update_date` DATETIME DEFAULT NULL,
    `create_date` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_template_selling_format_business_discount` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
    `qty` INT(11) UNSIGNED NOT NULL,
    `mode` TINYINT(2) UNSIGNED NOT NULL,
    `attribute` VARCHAR(255) DEFAULT NULL,
    `coefficient` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `template_selling_format_id` (`template_selling_format_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_lock_transactional` (
   `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
   `nick` VARCHAR(255) NOT NULL,
   `create_date` DATETIME DEFAULT NULL,
   PRIMARY KEY (`id`),
   INDEX `nick` (`nick`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_archived_entity` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `origin_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `data` LONGTEXT NOT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `origin_id__name` (`origin_id`, `name`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_magento_product_websites_update` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `action` TINYINT(2) UNSIGNED NOT NULL,
  `website_id` SMALLINT(5) UNSIGNED NOT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `product_id` (`product_id`),
  INDEX `action` (`action`),
  INDEX `website_id` (`website_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );
    }

    public function schemaDelete()
    {
        if (!$this->installer->getTablesObject()->isExists('synchronization_config')) {
            return;
        }

        $this->installer->run(<<<SQL

DROP TABLE IF EXISTS `m2epro_buy_dictionary_category`;
DROP TABLE IF EXISTS `m2epro_buy_template_new_product`;
DROP TABLE IF EXISTS `m2epro_buy_template_new_product_core`;
DROP TABLE IF EXISTS `m2epro_buy_template_new_product_attribute`;
DROP TABLE IF EXISTS `m2epro_buy_account`;
DROP TABLE IF EXISTS `m2epro_buy_item`;
DROP TABLE IF EXISTS `m2epro_buy_listing`;
DROP TABLE IF EXISTS `m2epro_buy_listing_auto_category_group`;
DROP TABLE IF EXISTS `m2epro_buy_listing_other`;
DROP TABLE IF EXISTS `m2epro_buy_listing_product`;
DROP TABLE IF EXISTS `m2epro_buy_listing_product_variation`;
DROP TABLE IF EXISTS `m2epro_buy_listing_product_variation_option`;
DROP TABLE IF EXISTS `m2epro_buy_marketplace`;
DROP TABLE IF EXISTS `m2epro_buy_order`;
DROP TABLE IF EXISTS `m2epro_buy_order_item`;
DROP TABLE IF EXISTS `m2epro_buy_template_selling_format`;
DROP TABLE IF EXISTS `m2epro_buy_template_synchronization`;

DROP TABLE IF EXISTS `m2epro_amazon_dictionary_shipping_override`;
DROP TABLE IF EXISTS `m2epro_amazon_template_shipping_override`;
DROP TABLE IF EXISTS `m2epro_amazon_template_shipping_override_service`;
DROP TABLE IF EXISTS `m2epro_amazon_processed_inventory`;

TRUNCATE TABLE `m2epro_synchronization_log`;
TRUNCATE TABLE `m2epro_operation_history`;

DELETE FROM `m2epro_listing_other_log` WHERE `action` IN (2, 3, 9, 10, 11, 12, 13, 14, 15, 16, 17);

DELETE FROM `m2epro_account` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_marketplace` WHERE `component_mode` = 'buy';

DELETE FROM `m2epro_listing` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_listing_auto_category_group` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_listing_other` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_listing_product` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_listing_product_variation` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_listing_product_variation_option` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_listing_log` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_listing_other_log` WHERE `component_mode` = 'buy';

DELETE FROM `m2epro_template_selling_format` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_template_synchronization` WHERE `component_mode` = 'buy';

DELETE FROM `m2epro_order` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_order_item` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_order_log` WHERE `component_mode` = 'buy';

DELETE FROM `m2epro_stop_queue` WHERE `component_mode` = 'buy';
DELETE FROM `m2epro_synchronization_log` WHERE `component_mode` = 'buy';

DROP TABLE IF EXISTS m2epro_synchronization_config;
SQL
        );
    }

    //########################################
}