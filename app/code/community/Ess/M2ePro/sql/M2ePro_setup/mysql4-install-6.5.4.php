<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//########################################

// GENERAL SCRIPT

$tempMagentoConnectUrl = 'https://www.magentocommerce.com/magento-connect/';
$tempMagentoConnectUrl .= 'ebay-amazon-rakuten-magento-integration-order-import-and-stock-level-synchronization.html';

$servicingInterval = rand(43200, 86400);

$installer->run(<<<SQL

DROP TABLE IF EXISTS `m2epro_account`;
CREATE TABLE `m2epro_account` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `additional_data` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_cache_config`;
CREATE TABLE `m2epro_cache_config` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(255) DEFAULT NULL,
  `key` VARCHAR(255) NOT NULL,
  `value` VARCHAR(255) DEFAULT NULL,
  `notice` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `group` (`group`),
  INDEX `key` (`key`),
  INDEX `value` (`value`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_config`;
CREATE TABLE `m2epro_config` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(255) DEFAULT NULL,
  `key` VARCHAR(255) NOT NULL,
  `value` VARCHAR(255) DEFAULT NULL,
  `notice` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `group` (`group`),
  INDEX `key` (`key`),
  INDEX `value` (`value`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing`;
CREATE TABLE `m2epro_listing` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `store_id` INT(11) UNSIGNED NOT NULL,
  `products_total_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `products_active_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `products_inactive_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `items_active_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `source_products` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `additional_data` LONGTEXT DEFAULT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `auto_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `auto_global_adding_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `auto_global_adding_add_not_visible` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `auto_website_adding_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `auto_website_adding_add_not_visible` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `auto_website_deleting_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `store_id` (`store_id`),
  INDEX `title` (`title`),
  INDEX `auto_mode` (`auto_mode`),
  INDEX `auto_global_adding_mode` (`auto_global_adding_mode`),
  INDEX `auto_website_adding_mode` (`auto_website_adding_mode`),
  INDEX `auto_website_deleting_mode` (`auto_website_deleting_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing_auto_category`;
CREATE TABLE `m2epro_listing_auto_category` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_id` int(11) UNSIGNED NOT NULL,
    `category_id` int(11) UNSIGNED NOT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `category_id` (`category_id`),
    INDEX `group_id` (`group_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing_auto_category_group`;
CREATE TABLE `m2epro_listing_auto_category_group` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `listing_id` int(11) UNSIGNED NOT NULL,
    `title` varchar(255) NOT NULL,
    `adding_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
    `adding_add_not_visible` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
    `deleting_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
    `component_mode` varchar(10) DEFAULT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `listing_id` (`listing_id`),
    INDEX `title` (`title`),
    INDEX `component_mode` (`component_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing_log`;
CREATE TABLE `m2epro_listing_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_id` INT(11) UNSIGNED DEFAULT NULL,
  `product_id` INT(11) UNSIGNED DEFAULT NULL,
  `listing_product_id` INT(11) UNSIGNED DEFAULT NULL,
  `parent_listing_product_id` int(11) UNSIGNED DEFAULT NULL,
  `listing_title` VARCHAR(255) DEFAULT NULL,
  `product_title` VARCHAR(255) DEFAULT NULL,
  `action_id` INT(11) UNSIGNED DEFAULT NULL,
  `action` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `initiator` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `priority` TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  `description` TEXT DEFAULT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `action` (`action`),
  INDEX `action_id` (`action_id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `initiator` (`initiator`),
  INDEX `listing_id` (`listing_id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `parent_listing_product_id` (`parent_listing_product_id`),
  INDEX `listing_title` (`listing_title`),
  INDEX `priority` (`priority`),
  INDEX `product_id` (`product_id`),
  INDEX `product_title` (`product_title`),
  INDEX `type` (`type`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing_other`;
CREATE TABLE `m2epro_listing_other` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED DEFAULT NULL,
  `status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `status_changer` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `product_id` (`product_id`),
  INDEX `status` (`status`),
  INDEX `status_changer` (`status_changer`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing_other_log`;
CREATE TABLE `m2epro_listing_other_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_other_id` INT(11) UNSIGNED DEFAULT NULL,
  `identifier` VARCHAR(32) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `action_id` INT(11) UNSIGNED DEFAULT NULL,
  `action` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `initiator` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `priority` TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  `description` TEXT DEFAULT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `action` (`action`),
  INDEX `action_id` (`action_id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `initiator` (`initiator`),
  INDEX `identifier` (`identifier`),
  INDEX `listing_other_id` (`listing_other_id`),
  INDEX `priority` (`priority`),
  INDEX `title` (`title`),
  INDEX `type` (`type`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing_product`;
CREATE TABLE `m2epro_listing_product` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `status_changer` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `listing_id` (`listing_id`),
  INDEX `product_id` (`product_id`),
  INDEX `status` (`status`),
  INDEX `status_changer` (`status_changer`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing_product_variation`;
CREATE TABLE `m2epro_listing_product_variation` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `listing_product_id` (`listing_product_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing_product_variation_option`;
CREATE TABLE `m2epro_listing_product_variation_option` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_product_variation_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED DEFAULT NULL,
  `product_type` VARCHAR(255) NOT NULL,
  `attribute` VARCHAR(255) NOT NULL,
  `option` VARCHAR(255) NOT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `attribute` (`attribute`),
  INDEX `component_mode` (`component_mode`),
  INDEX `listing_product_variation_id` (`listing_product_variation_id`),
  INDEX `option` (`option`),
  INDEX `product_id` (`product_id`),
  INDEX `product_type` (`product_type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing_product_instruction`;
CREATE TABLE `m2epro_listing_product_instruction` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `component` VARCHAR(10) DEFAULT NULL,
  `type` VARCHAR(255) NOT NULL,
  `initiator` VARCHAR(255) NOT NULL,
  `priority` INT(11) UNSIGNED NOT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `skip_until` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `component` (`component`),
  INDEX `type` (`type`),
  INDEX `priority` (`priority`),
  INDEX `skip_until` (`skip_until`),
  INDEX `create_date` (`create_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_listing_product_scheduled_action`;
CREATE TABLE `m2epro_listing_product_scheduled_action` (
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

DROP TABLE IF EXISTS `m2epro_lock_item`;
CREATE TABLE `m2epro_lock_item` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nick` VARCHAR(255) NOT NULL,
  `parent_id` INT(11) UNSIGNED DEFAULT NULL,
  `data` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `nick` (`nick`),
  INDEX `parent_id` (`parent_id`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_lock_transactional`;
CREATE TABLE `m2epro_lock_transactional` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nick` VARCHAR(255) NOT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `nick` (`nick`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_magento_product_websites_update`;
CREATE TABLE `m2epro_magento_product_websites_update` (
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

DROP TABLE IF EXISTS `m2epro_marketplace`;
CREATE TABLE `m2epro_marketplace` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `native_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `sorder` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `group_title` VARCHAR(255) NOT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component_mode` (`component_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_order`;
CREATE TABLE `m2epro_order` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED DEFAULT NULL,
  `magento_order_id` INT(11) UNSIGNED DEFAULT NULL,
  `magento_order_creation_failure` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `magento_order_creation_fails_count` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `magento_order_creation_latest_attempt_date` DATETIME DEFAULT NULL,
  `store_id` INT(11) UNSIGNED DEFAULT NULL,
  `reservation_state` TINYINT(2) UNSIGNED DEFAULT 0,
  `reservation_start_date` DATETIME DEFAULT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `additional_data` TEXT NULL DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `magento_order_id` (`magento_order_id`),
  INDEX `magento_order_creation_failure` (`magento_order_creation_failure`),
  INDEX `magento_order_creation_fails_count` (`magento_order_creation_fails_count`),
  INDEX `magento_order_creation_latest_attempt_date` (`magento_order_creation_latest_attempt_date`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `reservation_state` (`reservation_state`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_order_change`;
CREATE TABLE `m2epro_order_change` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `component` VARCHAR(10) NOT NULL,
  `order_id` INT(11) UNSIGNED NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  `params` LONGTEXT NOT NULL,
  `creator_type` TINYINT(2) NOT NULL DEFAULT 0,
  `processing_attempt_count` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `processing_attempt_date` DATETIME DEFAULT NULL,
  `hash` VARCHAR(50) DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `action` (`action`),
  INDEX `creator_type` (`creator_type`),
  INDEX `hash` (`hash`),
  INDEX `order_id` (`order_id`),
  INDEX `processing_attempt_count` (`processing_attempt_count`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_order_item`;
CREATE TABLE `m2epro_order_item` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED DEFAULT NULL,
  `product_details` TEXT DEFAULT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `qty_reserved` INT(11) UNSIGNED DEFAULT 0,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `order_id` (`order_id`),
  INDEX `product_id` (`product_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_order_log`;
CREATE TABLE `m2epro_order_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 2,
  `initiator` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `initiator` (`initiator`),
  INDEX `order_id` (`order_id`),
  INDEX `type` (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_order_matching`;
CREATE TABLE `m2epro_order_matching` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `input_variation_options` TEXT DEFAULT NULL,
  `output_variation_options` TEXT DEFAULT NULL,
  `hash` VARCHAR(50) DEFAULT NULL,
  `component` VARCHAR(10) NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component` (`component`),
  INDEX `hash` (`hash`),
  INDEX `product_id` (`product_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_primary_config`;
CREATE TABLE `m2epro_primary_config` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(255) DEFAULT NULL,
  `key` VARCHAR(255) NOT NULL,
  `value` VARCHAR(255) DEFAULT NULL,
  `notice` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `group` (`group`),
  INDEX `key` (`key`),
  INDEX `value` (`value`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_processing`;
CREATE TABLE `m2epro_processing` (
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

DROP TABLE IF EXISTS `m2epro_processing_lock`;
CREATE TABLE `m2epro_processing_lock` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `model_name` VARCHAR(255) NOT NULL,
  `object_id` INT(11) UNSIGNED NOT NULL,
  `tag` VARCHAR(255) DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `model_name` (`model_name`),
  INDEX `object_id` (`object_id`),
  INDEX `tag` (`tag`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_request_pending_single`;
CREATE TABLE `m2epro_request_pending_single` (
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

DROP TABLE IF EXISTS `m2epro_request_pending_partial`;
CREATE TABLE `m2epro_request_pending_partial` (
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

DROP TABLE IF EXISTS `m2epro_request_pending_partial_data`;
CREATE TABLE `m2epro_request_pending_partial_data` (
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

DROP TABLE IF EXISTS `m2epro_connector_command_pending_processing_single`;
CREATE TABLE `m2epro_connector_command_pending_processing_single` (
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

DROP TABLE IF EXISTS `m2epro_connector_command_pending_processing_partial`;
CREATE TABLE `m2epro_connector_command_pending_processing_partial` (
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

DROP TABLE IF EXISTS `m2epro_stop_queue`;
CREATE TABLE `m2epro_stop_queue` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `component_mode` VARCHAR(255) NOT NULL,
  `is_processed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `additional_data` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `is_processed` (`is_processed`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_synchronization_log`;
CREATE TABLE `m2epro_synchronization_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `operation_history_id` INT(11) UNSIGNED DEFAULT NULL,
  `task` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `initiator` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `priority` TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  `description` TEXT DEFAULT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `initiator` (`initiator`),
  INDEX `priority` (`priority`),
  INDEX `task` (`task`),
  INDEX `operation_history_id` (`operation_history_id`),
  INDEX `type` (`type`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_system_log`;
CREATE TABLE `m2epro_system_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(255) DEFAULT NULL,
  `description` LONGTEXT DEFAULT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `type` (`type`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_operation_history`;
CREATE TABLE `m2epro_operation_history` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nick` VARCHAR(255) NOT NULL,
  `parent_id` INT(11) UNSIGNED DEFAULT NULL,
  `initiator` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `data` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `nick` (`nick`),
  INDEX `parent_id` (`parent_id`),
  INDEX `initiator` (`initiator`),
  INDEX `start_date` (`start_date`),
  INDEX `end_date` (`end_date`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_template_selling_format`;
CREATE TABLE `m2epro_template_selling_format` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_template_synchronization`;
CREATE TABLE `m2epro_template_synchronization` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `component_mode` (`component_mode`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_template_description`;
CREATE TABLE `m2epro_template_description` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `component_mode` varchar(10) DEFAULT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `component_mode` (`component_mode`),
    INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_wizard`;
CREATE TABLE `m2epro_wizard` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nick` VARCHAR(255) NOT NULL,
  `view` VARCHAR(255) NOT NULL,
  `status` INT(11) UNSIGNED NOT NULL,
  `step` VARCHAR(255) DEFAULT NULL,
  `type` TINYINT(2) UNSIGNED NOT NULL,
  `priority` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `nick` (`nick`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_migration_v6`;
CREATE TABLE `m2epro_migration_v6` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `component` VARCHAR(32) NOT NULL,
  `group` VARCHAR(255) NOT NULL,
  `data` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `group` (`group`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_registry`;
CREATE TABLE `m2epro_registry` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(255) NOT NULL,
  `value` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `key` (`key`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_archived_entity`;
CREATE TABLE `m2epro_archived_entity` (
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

SQL
);

$installer->run(<<<SQL

INSERT INTO `m2epro_primary_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/M2ePro/license/', 'key', NULL, 'License Key', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/', 'status', 1, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/', 'domain', NULL, 'Valid domain', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/', 'ip', NULL, 'Valid ip', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/info/', 'email', NULL, 'Associated Email', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/valid/', 'domain', NULL, '0 - Not valid\r\n1 - Valid', '2013-05-08 00:00:00','2013-05-08 00:00:00'),
  ('/M2ePro/license/valid/', 'ip', NULL, '0 - Not valid\r\n1 - Valid', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/server/', 'messages', '[]', 'Server messages', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/server/', 'application_key', 'b79a495170da3b081c9ebae6c255c7fbe1b139b5', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/server/', 'installation_key', '{$installer->generateRandomHash()}', 'Unique identifier of M2E instance',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/modules/', 'M2ePro', '0.0.0.r0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/server/', 'baseurl_1', 'https://s1.m2epro.com/', 'Support server base url',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  (NULL, 'is_disabled', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (NULL, 'environment', 'production', 'Available values:\r\nproduction\r\ndevelopment\r\ntesting',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/', 'runner', 'magento', NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
  ('/cron/', 'last_access', NULL, 'Time of last cron synchronization', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/', 'last_runner_change', NULL, 'Time of last change cron runner', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/', 'last_executed_slow_task', NULL, '', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/checker/task/repair_crashed_tables/', 'interval', '3600', 'in seconds', '2016-02-18 00:00:00',
   '2016-02-18 00:00:00'),
  ('/cron/service/', 'auth_key', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
  ('/cron/service/', 'disabled', '0', NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
  ('/cron/magento/', 'disabled', '0', NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
  ('/cron/service/', 'hostname_1', 'cron.m2epro.com', NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
  ('/cron/task/system/archive_old_orders/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/system/archive_old_orders/', 'interval', '3600', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/system/clear_old_logs/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/system/clear_old_logs/', 'interval', '86400', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/system/connector_command_pending/process_partial/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00','2013-05-08 00:00:00'),
  ('/cron/task/system/connector_command_pending/process_partial/', 'interval', '60', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/system/connector_command_pending/process_single/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/system/connector_command_pending/process_single/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/system/issues_resolver/remove_missed_processing_locks/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/system/issues_resolver/remove_missed_processing_locks/', 'interval', '60', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/system/processing/process_result/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/system/processing/process_result/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/system/request_pending/process_partial/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/system/request_pending/process_partial/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/system/request_pending/process_single/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/system/request_pending/process_single/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/system/servicing/synchronize/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/system/servicing/synchronize/', 'interval', '{$servicingInterval}', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/magento/product/detect_directly_added/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/magento/product/detect_directly_added/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/magento/product/detect_directly_deleted/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/magento/product/detect_directly_deleted/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/magento/global_notifications/', 'mode', '1', '0 - disable, \r\n1 - enable', '2018-11-06 00:00:00',
   '2018-11-06 00:00:00'),
  ('/cron/task/magento/global_notifications/', 'interval', '86400', 'in seconds', '2018-11-06 00:00:00',
   '2018-11-06 00:00:00'),
  ('/cron/task/listing/product/inspect_direct_changes/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/listing/product/inspect_direct_changes/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/listing/product/process_revise_total/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/listing/product/process_revise_total/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/listing/product/auto_actions/process_magento_product_websites_updates/', 'mode', '1',
   '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/listing/product/auto_actions/process_magento_product_websites_updates/', 'interval', '60', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/listing/product/stop_queue/process/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/listing/product/stop_queue/process/', 'interval', '3600', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/listing/product/stop_queue/remove_old/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/listing/product/stop_queue/remove_old/', 'interval', '86400', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/listing/product/inspector/', 'mode', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/clearing/listings/', 'days', '30', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/clearing/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/logs/clearing/other_listings/', 'days', '30', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/clearing/synchronizations/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/logs/clearing/synchronizations/', 'days', '30', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/clearing/orders/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/clearing/orders/', 'days', '90', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/clearing/ebay_pickup_store/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/logs/clearing/ebay_pickup_store/', 'days', '30', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/listings/', 'last_action_id', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/other_listings/', 'last_action_id', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/ebay_pickup_store/', 'last_action_id', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/', 'knowledge_base_url', 'https://support.m2epro.com/knowledgebase', NULL, '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/support/', 'documentation_url', 'https://docs.m2epro.com', NULL, '2015-07-03 00:00:00',
   '2015-07-03 00:00:00'),
  ('/support/', 'clients_portal_url', 'https://clients.m2epro.com/', NULL, '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/support/', 'main_website_url', 'https://m2epro.com/', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/', 'main_support_url', 'https://support.m2epro.com/', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/', 'magento_connect_url', '{$tempMagentoConnectUrl}', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/', 'contact_email', 'support@m2epro.com', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/', 'show_block_notices', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/', 'show_products_thumbnails', '1', 'Visibility thumbnails into grid', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/products_grid/', 'use_alternative_mysql_select', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/synchronization/revise_total/', 'show', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/amazon/autocomplete/', 'max_records_quantity', '100', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/ebay/', 'mode', 'simple', 'simple, advanced', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/ebay/notice/', 'disable_collapse', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/ebay/template/selling_format/', 'show_tax_category', '0', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/ebay/feedbacks/notification/', 'mode', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/ebay/feedbacks/notification/', 'last_check', NULL, 'Date last check new buyers feedbacks',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/debug/exceptions/', 'send_to_server', '1', '0 - disable,\r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/debug/exceptions/', 'filters_mode', '0', '0 - disable,\r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/debug/fatal_error/', 'send_to_server', '1', '0 - disable,\r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/debug/logging/', 'send_to_server', 1, '0 - disable,\r\n1 - enable', '2015-08-12 00:00:00', '2015-08-12 00:00:00'),
  ('/debug/maintenance/', 'mode', '0', '0 - disable,\r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/debug/maintenance/', 'restore_date', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/renderer/description/', 'convert_linebreaks', '1', '0 - No\r\n1 - Yes', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/other/paypal/', 'url', 'paypal.com/cgi-bin/webscr/', 'PayPal url', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/product/index/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/product/force_qty/', 'mode', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/product/force_qty/', 'value', '10', 'min qty value', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/qty/percentage/', 'rounding_greater', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/magento/attribute/', 'price_type_converting', '0',
   '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/order/magento/settings/', 'create_with_first_product_options_when_variation_unavailable', '1',
   '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO `m2epro_wizard` VALUES
  (1,  'installationEbay',             'ebay',    0, NULL, 1, 1),
  (2,  'installationAmazon',           'amazon',  0, NULL, 1, 2),
  (3,  'migrationNewAmazon',           'amazon',  3, NULL, 1, 3),
  (4,  'removedPlay',                  '*',       3, NULL, 0, 4),
  (5,  'ebayProductDetails',           'ebay',    3, NULL, 1, 5),
  (6,  'fullAmazonCategories',         'amazon',  3, NULL, 1, 6),
  (8,  'removedEbay3rdParty',          'ebay',    3, NULL, 0, 8),
  (9,  'removedBuy',                   '*',       3, NULL, 0, 9),
  (10, 'installationWalmart',          'walmart', 0, NULL, 1, 10);

SQL
);

//########################################

// EBAY SCRIPT
$installer->run(<<<SQL

DROP TABLE IF EXISTS `m2epro_ebay_account`;
CREATE TABLE `m2epro_ebay_account` (
  `account_id` INT(11) UNSIGNED NOT NULL,
  `mode` TINYINT(2) UNSIGNED NOT NULL,
  `server_hash` VARCHAR(255) NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `translation_hash` VARCHAR(255) DEFAULT NULL,
  `translation_info` TEXT DEFAULT NULL,
  `token_session` VARCHAR(255) NOT NULL,
  `token_expired_date` DATETIME NOT NULL,
  `sell_api_token_session` VARCHAR(255) DEFAULT NULL,
  `sell_api_token_expired_date` DATETIME DEFAULT NULL,
  `marketplaces_data` TEXT DEFAULT NULL,
  `defaults_last_synchronization` datetime DEFAULT NULL,
  `other_listings_synchronization` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `other_listings_mapping_mode` TINYINT(2) NOT NULL DEFAULT 0,
  `other_listings_mapping_settings` VARCHAR(255) DEFAULT NULL,
  `other_listings_last_synchronization` DATETIME DEFAULT NULL,
  `feedbacks_receive` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `feedbacks_auto_response` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `feedbacks_auto_response_only_positive` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `feedbacks_last_used_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `ebay_store_title` VARCHAR(255) NOT NULL,
  `ebay_store_url` TEXT NOT NULL,
  `ebay_store_subscription_level` VARCHAR(255) NOT NULL,
  `ebay_store_description` TEXT NOT NULL,
  `info` TEXT DEFAULT NULL,
  `user_preferences` TEXT DEFAULT NULL,
  `rate_tables` TEXT DEFAULT NULL,
  `ebay_shipping_discount_profiles` TEXT DEFAULT NULL,
  `job_token` VARCHAR(255) DEFAULT NULL,
  `orders_last_synchronization` DATETIME DEFAULT NULL,
  `magento_orders_settings` TEXT NOT NULL,
  `messages_receive` TINYINT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_account_store_category`;
CREATE TABLE `m2epro_ebay_account_store_category` (
  `account_id` INT(11) UNSIGNED NOT NULL,
  `category_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `parent_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `is_leaf` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `sorder` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`account_id`, `category_id`),
  INDEX `parent_id` (`parent_id`),
  INDEX `sorder` (`sorder`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_account_pickup_store`;
CREATE TABLE `m2epro_ebay_account_pickup_store` (
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

DROP TABLE IF EXISTS `m2epro_ebay_account_pickup_store_state`;
CREATE TABLE `m2epro_ebay_account_pickup_store_state` (
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

DROP TABLE IF EXISTS `m2epro_ebay_account_pickup_store_log`;
CREATE TABLE `m2epro_ebay_account_pickup_store_log` (
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

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_category`;
CREATE TABLE `m2epro_ebay_dictionary_category` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `parent_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `path` VARCHAR(500) DEFAULT NULL,
  `features` LONGTEXT DEFAULT NULL,
  `item_specifics` LONGTEXT DEFAULT NULL,
  `is_leaf` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `category_id` (`category_id`),
  INDEX `is_leaf` (`is_leaf`),
  INDEX `parent_category_id` (`parent_category_id`),
  INDEX `title` (`title`),
  INDEX `path` (`path`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_marketplace`;
CREATE TABLE `m2epro_ebay_dictionary_marketplace` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `client_details_last_update_date` DATETIME DEFAULT NULL,
  `server_details_last_update_date` DATETIME DEFAULT NULL,
  `dispatch` LONGTEXT NOT NULL,
  `packages` LONGTEXT NOT NULL,
  `return_policy` LONGTEXT NOT NULL,
  `listing_features` LONGTEXT NOT NULL,
  `payments` LONGTEXT NOT NULL,
  `shipping_locations` LONGTEXT NOT NULL,
  `shipping_locations_exclude` LONGTEXT NOT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `tax_categories` LONGTEXT NOT NULL,
  `charities` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_shipping`;
CREATE TABLE `m2epro_ebay_dictionary_shipping` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `ebay_id` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(255) NOT NULL,
  `is_flat` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_calculated` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_international` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `data` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `category` (`category`),
  INDEX `ebay_id` (`ebay_id`),
  INDEX `is_calculated` (`is_calculated`),
  INDEX `is_flat` (`is_flat`),
  INDEX `is_international` (`is_international`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_feedback`;
CREATE TABLE `m2epro_ebay_feedback` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `ebay_item_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `ebay_item_title` VARCHAR(255) NOT NULL,
  `ebay_transaction_id` VARCHAR(20) NOT NULL,
  `buyer_name` VARCHAR(200) NOT NULL,
  `buyer_feedback_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `buyer_feedback_text` VARCHAR(255) NOT NULL,
  `buyer_feedback_date` DATETIME NOT NULL,
  `buyer_feedback_type` VARCHAR(20) NOT NULL,
  `seller_feedback_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `seller_feedback_text` VARCHAR(255) NOT NULL,
  `seller_feedback_date` DATETIME NOT NULL,
  `seller_feedback_type` VARCHAR(20) NOT NULL,
  `last_response_attempt_date` DATETIME DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `buyer_feedback_id` (`buyer_feedback_id`),
  INDEX `ebay_item_id` (`ebay_item_id`),
  INDEX `ebay_transaction_id` (`ebay_transaction_id`),
  INDEX `seller_feedback_id` (`seller_feedback_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_feedback_template`;
CREATE TABLE `m2epro_ebay_feedback_template` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `body` TEXT NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_item`;
CREATE TABLE `m2epro_ebay_item` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `store_id` INT(11) UNSIGNED NOT NULL,
  `variations` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `item_id` (`item_id`),
  INDEX `account_id` (`account_id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `product_id` (`product_id`),
  INDEX `store_id` (`store_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing`;
CREATE TABLE `m2epro_ebay_listing` (
  `listing_id` INT(11) UNSIGNED NOT NULL,
  `products_sold_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `items_sold_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `auto_global_adding_template_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `auto_global_adding_template_other_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `auto_website_adding_template_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `auto_website_adding_template_other_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_payment_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_payment_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_payment_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_shipping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_shipping_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_shipping_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_return_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_return_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_return_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_description_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_description_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_description_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_selling_format_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_selling_format_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_selling_format_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_synchronization_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_synchronization_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_synchronization_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `product_add_ids` TEXT DEFAULT NULL,
  `parts_compatibility_mode` VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY (`listing_id`),
  INDEX `auto_global_adding_template_category_id` (`auto_global_adding_template_category_id`),
  INDEX `auto_global_adding_template_other_category_id` (`auto_global_adding_template_other_category_id`),
  INDEX `auto_website_adding_template_category_id` (`auto_website_adding_template_category_id`),
  INDEX `auto_website_adding_template_other_category_id` (`auto_website_adding_template_other_category_id`),
  INDEX `items_sold_count` (`items_sold_count`),
  INDEX `products_sold_count` (`products_sold_count`),
  INDEX `template_description_custom_id` (`template_description_custom_id`),
  INDEX `template_description_id` (`template_description_id`),
  INDEX `template_description_mode` (`template_description_mode`),
  INDEX `template_payment_custom_id` (`template_payment_custom_id`),
  INDEX `template_payment_id` (`template_payment_id`),
  INDEX `template_payment_mode` (`template_payment_mode`),
  INDEX `template_return_custom_id` (`template_return_custom_id`),
  INDEX `template_return_id` (`template_return_id`),
  INDEX `template_return_mode` (`template_return_mode`),
  INDEX `template_selling_format_custom_id` (`template_selling_format_custom_id`),
  INDEX `template_selling_format_id` (`template_selling_format_id`),
  INDEX `template_selling_format_mode` (`template_selling_format_mode`),
  INDEX `template_shipping_custom_id` (`template_shipping_custom_id`),
  INDEX `template_shipping_id` (`template_shipping_id`),
  INDEX `template_shipping_mode` (`template_shipping_mode`),
  INDEX `template_synchronization_custom_id` (`template_synchronization_custom_id`),
  INDEX `template_synchronization_id` (`template_synchronization_id`),
  INDEX `template_synchronization_mode` (`template_synchronization_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_auto_category_group`;
CREATE TABLE `m2epro_ebay_listing_auto_category_group` (
    `listing_auto_category_group_id` int(11) UNSIGNED NOT NULL,
    `adding_template_category_id` int(11) UNSIGNED DEFAULT NULL,
    `adding_template_other_category_id` int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`listing_auto_category_group_id`),
    INDEX `adding_template_category_id` (`adding_template_category_id`),
    INDEX `adding_template_other_category_id` (`adding_template_other_category_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_other`;
CREATE TABLE `m2epro_ebay_listing_other` (
  `listing_other_id` INT(11) UNSIGNED NOT NULL,
  `item_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `currency` VARCHAR(255) DEFAULT NULL,
  `online_duration` INT(11) UNSIGNED DEFAULT NULL,
  `online_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `online_qty` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `online_qty_sold` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `online_bids` INT(11) UNSIGNED DEFAULT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`listing_other_id`),
  INDEX `currency` (`currency`),
  INDEX `end_date` (`end_date`),
  INDEX `item_id` (`item_id`),
  INDEX `online_bids` (`online_bids`),
  INDEX `online_price` (`online_price`),
  INDEX `online_qty` (`online_qty`),
  INDEX `online_qty_sold` (`online_qty_sold`),
  INDEX `sku` (`sku`),
  INDEX `start_date` (`start_date`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_product`;
CREATE TABLE `m2epro_ebay_listing_product` (
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `template_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_other_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `ebay_item_id` INT(11) UNSIGNED DEFAULT NULL,
  `item_uuid` VARCHAR(32) DEFAULT NULL,
  `is_duplicate` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `online_is_variation` TINYINT(2) UNSIGNED DEFAULT NULL,
  `online_is_auction_type` TINYINT(2) UNSIGNED DEFAULT NULL,
  `online_sku` VARCHAR(255) DEFAULT NULL,
  `online_title` VARCHAR(255) DEFAULT NULL,
  `online_sub_title` VARCHAR(255) DEFAULT NULL,
  `online_description` LONGTEXT DEFAULT NULL,
  `online_images` LONGTEXT DEFAULT NULL,
  `online_duration` INT(11) UNSIGNED DEFAULT NULL,
  `online_current_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_start_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_reserve_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_buyitnow_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  `online_qty_sold` INT(11) UNSIGNED DEFAULT NULL,
  `online_bids` INT(11) UNSIGNED DEFAULT NULL,
  `online_main_category` VARCHAR(255) DEFAULT NULL,
  `online_categories_data` LONGTEXT DEFAULT NULL,
  `online_shipping_data` LONGTEXT DEFAULT NULL,
  `online_payment_data` LONGTEXT DEFAULT NULL,
  `online_return_data` LONGTEXT DEFAULT NULL,
  `online_other_data` LONGTEXT DEFAULT NULL,
  `translation_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `translation_service` VARCHAR(255) DEFAULT NULL,
  `translated_date` DATETIME DEFAULT NULL,
  `start_date` DATETIME DEFAULT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `template_payment_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_payment_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_payment_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_shipping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_shipping_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_shipping_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_return_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_return_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_return_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_description_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_description_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_description_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_selling_format_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_selling_format_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_selling_format_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_synchronization_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_synchronization_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_synchronization_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  INDEX `ebay_item_id` (`ebay_item_id`),
  INDEX `item_uuid` (`item_uuid`),
  INDEX `is_duplicate` (`is_duplicate`),
  INDEX `online_is_variation` (`online_is_variation`),
  INDEX `online_is_auction_type` (`online_is_auction_type`),
  INDEX `end_date` (`end_date`),
  INDEX `online_bids` (`online_bids`),
  INDEX `online_buyitnow_price` (`online_buyitnow_price`),
  INDEX `online_main_category` (`online_main_category`),
  INDEX `online_qty` (`online_qty`),
  INDEX `online_qty_sold` (`online_qty_sold`),
  INDEX `online_reserve_price` (`online_reserve_price`),
  INDEX `online_sku` (`online_sku`),
  INDEX `online_current_price` (`online_current_price`),
  INDEX `online_start_price` (`online_start_price`),
  INDEX `online_title` (`online_title`),
  INDEX `start_date` (`start_date`),
  INDEX `translation_status` (`translation_status`),
  INDEX `translation_service` (`translation_service`),
  INDEX `translated_date` (`translated_date`),
  INDEX `template_category_id` (`template_category_id`),
  INDEX `template_description_custom_id` (`template_description_custom_id`),
  INDEX `template_description_id` (`template_description_id`),
  INDEX `template_description_mode` (`template_description_mode`),
  INDEX `template_other_category_id` (`template_other_category_id`),
  INDEX `template_payment_custom_id` (`template_payment_custom_id`),
  INDEX `template_payment_id` (`template_payment_id`),
  INDEX `template_payment_mode` (`template_payment_mode`),
  INDEX `template_return_custom_id` (`template_return_custom_id`),
  INDEX `template_return_id` (`template_return_id`),
  INDEX `template_return_mode` (`template_return_mode`),
  INDEX `template_selling_format_custom_id` (`template_selling_format_custom_id`),
  INDEX `template_selling_format_id` (`template_selling_format_id`),
  INDEX `template_selling_format_mode` (`template_selling_format_mode`),
  INDEX `template_shipping_custom_id` (`template_shipping_custom_id`),
  INDEX `template_shipping_id` (`template_shipping_id`),
  INDEX `template_shipping_mode` (`template_shipping_mode`),
  INDEX `template_synchronization_custom_id` (`template_synchronization_custom_id`),
  INDEX `template_synchronization_id` (`template_synchronization_id`),
  INDEX `template_synchronization_mode` (`template_synchronization_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_product_pickup_store`;
CREATE TABLE `m2epro_ebay_listing_product_pickup_store` (
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

DROP TABLE IF EXISTS `m2epro_ebay_listing_product_variation`;
CREATE TABLE `m2epro_ebay_listing_product_variation` (
  `listing_product_variation_id` INT(11) UNSIGNED NOT NULL,
  `add` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `delete` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `online_sku` VARCHAR(255) DEFAULT NULL,
  `online_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  `online_qty_sold` INT(11) UNSIGNED DEFAULT NULL,
  `status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`listing_product_variation_id`),
  INDEX `add` (`add`),
  INDEX `delete` (`delete`),
  INDEX `online_sku` (`online_sku`),
  INDEX `online_price` (`online_price`),
  INDEX `online_qty` (`online_qty`),
  INDEX `online_qty_sold` (`online_qty_sold`),
  INDEX `status` (`status`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_product_variation_option`;
CREATE TABLE `m2epro_ebay_listing_product_variation_option` (
  `listing_product_variation_option_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_product_variation_option_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_product_action_processing`;
CREATE TABLE `m2epro_ebay_listing_product_action_processing` (
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

DROP TABLE IF EXISTS `m2epro_ebay_indexer_listing_product_parent`;
CREATE TABLE `m2epro_ebay_indexer_listing_product_parent` (
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

DROP TABLE IF EXISTS `m2epro_ebay_marketplace`;
CREATE TABLE `m2epro_ebay_marketplace` (
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `currency` VARCHAR(70) NOT NULL DEFAULT 'USD',
  `origin_country` VARCHAR(255) DEFAULT NULL,
  `language_code` VARCHAR(255) DEFAULT NULL,
  `translation_service_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_multivariation` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_freight_shipping` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_calculated_shipping` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_tax_table` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_vat` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_stp` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_stp_advanced` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_map` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_local_shipping_rate_table` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_international_shipping_rate_table` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_english_measurement_system` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_metric_measurement_system` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_cash_on_delivery` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_global_shipping_program` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_charity` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_click_and_collect` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_in_store_pickup` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_return_description` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_epid` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_ktype` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`marketplace_id`),
  INDEX `is_calculated_shipping` (`is_calculated_shipping`),
  INDEX `is_cash_on_delivery` (`is_cash_on_delivery`),
  INDEX `is_charity` (`is_charity`),
  INDEX `is_english_measurement_system` (`is_english_measurement_system`),
  INDEX `is_freight_shipping` (`is_freight_shipping`),
  INDEX `is_international_shipping_rate_table` (`is_international_shipping_rate_table`),
  INDEX `is_local_shipping_rate_table` (`is_local_shipping_rate_table`),
  INDEX `is_metric_measurement_system` (`is_metric_measurement_system`),
  INDEX `is_tax_table` (`is_tax_table`),
  INDEX `is_vat` (`is_vat`),
  INDEX `is_stp` (`is_stp`),
  INDEX `is_stp_advanced` (`is_stp_advanced`),
  INDEX `is_map` (`is_map`),
  INDEX `is_click_and_collect` (`is_click_and_collect`),
  INDEX `is_in_store_pickup` (`is_in_store_pickup`),
  INDEX `is_return_description` (`is_return_description`),
  INDEX `is_epid` (`is_epid`),
  INDEX `is_ktype` (`is_ktype`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_motor_epid`;
CREATE TABLE `m2epro_ebay_dictionary_motor_epid` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `epid` VARCHAR(255) NOT NULL,
  `product_type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `make` VARCHAR(255) NOT NULL,
  `model` VARCHAR(255) NOT NULL,
  `year` SMALLINT(4) UNSIGNED NOT NULL,
  `trim` VARCHAR(255) DEFAULT NULL,
  `engine` VARCHAR(255) DEFAULT NULL,
  `submodel` VARCHAR(255) DEFAULT NULL,
  `is_custom` TINYINT(2) UNSIGNED NOT NULL,
  `scope` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `epid` (`epid`),
  INDEX `engine` (`engine`),
  INDEX `make` (`make`),
  INDEX `model` (`model`),
  INDEX `product_type` (`product_type`),
  INDEX `submodel` (`submodel`),
  INDEX `trim` (`trim`),
  INDEX `year` (`year`),
  INDEX `is_custom` (`is_custom`),
  INDEX `scope` (`scope`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_motor_ktype`;
CREATE TABLE `m2epro_ebay_dictionary_motor_ktype` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ktype` int(11) UNSIGNED NOT NULL,
  `make` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `variant` varchar(255) DEFAULT NULL,
  `body_style` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `from_year` int(11) DEFAULT NULL,
  `to_year` int(11) DEFAULT NULL,
  `engine` varchar(255) DEFAULT NULL,
  `is_custom` TINYINT(2) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `body_style` (`body_style`),
  INDEX `engine` (`engine`),
  INDEX `from_year` (`from_year`),
  INDEX `ktype` (`ktype`),
  INDEX `make` (`make`),
  INDEX `model` (`model`),
  INDEX `to_year` (`to_year`),
  INDEX `type` (`type`),
  INDEX `variant` (`variant`),
  INDEX `is_custom` (`is_custom`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_motor_filter`;
CREATE TABLE `m2epro_ebay_motor_filter` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `type` TINYINT(2) UNSIGNED NOT NULL,
    `conditions` TEXT NOT NULL,
    `note` TEXT DEFAULT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX type (`type`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_motor_group`;
CREATE TABLE `m2epro_ebay_motor_group` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `mode` TINYINT(2) UNSIGNED NOT NULL,
    `type` TINYINT(2) UNSIGNED NOT NULL,
    `items_data` TEXT DEFAULT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX mode (`mode`),
    INDEX type (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_motor_filter_to_group`;
CREATE TABLE `m2epro_ebay_motor_filter_to_group` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `filter_id` INT(11) UNSIGNED NOT NULL,
    `group_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    INDEX filter_id (`filter_id`),
    INDEX group_id (`group_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_order`;
CREATE TABLE `m2epro_ebay_order` (
  `order_id` INT(11) UNSIGNED NOT NULL,
  `ebay_order_id` VARCHAR(255) NOT NULL,
  `selling_manager_id` INT(11) UNSIGNED DEFAULT NULL,
  `buyer_name` VARCHAR(255) NOT NULL,
  `buyer_email` VARCHAR(255) NOT NULL,
  `buyer_user_id` VARCHAR(255) NOT NULL,
  `buyer_message` VARCHAR(500) DEFAULT NULL,
  `buyer_tax_id` VARCHAR(64) DEFAULT NULL,
  `paid_amount` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `saved_amount` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `currency` VARCHAR(10) NOT NULL,
  `checkout_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `shipping_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `payment_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `shipping_details` TEXT DEFAULT NULL,
  `payment_details` TEXT DEFAULT NULL,
  `tax_details` TEXT DEFAULT NULL,
  `purchase_update_date` DATETIME DEFAULT NULL,
  `purchase_create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  INDEX `ebay_order_id` (`ebay_order_id`),
  INDEX `selling_manager_id` (`selling_manager_id`),
  INDEX `buyer_email` (`buyer_email`),
  INDEX `buyer_name` (`buyer_name`),
  INDEX `buyer_user_id` (`buyer_user_id`),
  INDEX `paid_amount` (`paid_amount`),
  INDEX `checkout_status` (`checkout_status`),
  INDEX `payment_status` (`payment_status`),
  INDEX `shipping_status` (`shipping_status`),
  INDEX `purchase_create_date` (`purchase_create_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_order_external_transaction`;
CREATE TABLE `m2epro_ebay_order_external_transaction` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL,
  `transaction_id` VARCHAR(255) NOT NULL,
  `fee` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `sum` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `is_refund` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `transaction_date` DATETIME NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id` (`order_id`),
  INDEX `transaction_id` (`transaction_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_order_item`;
CREATE TABLE `m2epro_ebay_order_item` (
  `order_item_id` INT(11) UNSIGNED NOT NULL,
  `transaction_id` VARCHAR(20) NOT NULL,
  `selling_manager_id` INT(11) UNSIGNED DEFAULT NULL,
  `item_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(64) DEFAULT NULL,
  `price` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `qty_purchased` INT(11) UNSIGNED NOT NULL,
  `tax_details` TEXT DEFAULT NULL,
  `final_fee` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `waste_recycling_fee` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `variation_details` TEXT DEFAULT NULL,
  `tracking_details` TEXT DEFAULT NULL,
  `unpaid_item_process_state` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_item_id`),
  INDEX `transaction_id` (`transaction_id`),
  INDEX `selling_manager_id` (`selling_manager_id`),
  INDEX `item_id` (`item_id`),
  INDEX `sku` (`sku`),
  INDEX `title` (`title`),
  INDEX `unpaid_item_process_state` (`unpaid_item_process_state`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_category`;
CREATE TABLE `m2epro_ebay_template_category` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `category_main_id` INT(11) UNSIGNED NOT NULL,
  `category_main_path` VARCHAR(255) DEFAULT NULL,
  `category_main_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 2,
  `category_main_attribute` VARCHAR(255) NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_category_specific`;
CREATE TABLE `m2epro_ebay_template_category_specific` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_category_id` INT(11) UNSIGNED NOT NULL,
  `mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `attribute_title` VARCHAR(255) NOT NULL,
  `value_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `value_ebay_recommended` LONGTEXT DEFAULT NULL,
  `value_custom_value` VARCHAR(255) DEFAULT NULL,
  `value_custom_attribute` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `template_category_id` (`template_category_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_description`;
CREATE TABLE `m2epro_ebay_template_description` (
  `template_description_id` INT(11) UNSIGNED NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `title_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `title_template` VARCHAR(255) NOT NULL,
  `subtitle_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `subtitle_template` VARCHAR(255) NOT NULL,
  `description_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `description_template` LONGTEXT NOT NULL,
  `condition_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `condition_value` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `condition_attribute` VARCHAR(255) NOT NULL,
  `condition_note_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `condition_note_template` TEXT NOT NULL,
  `product_details` TEXT DEFAULT NULL,
  `cut_long_titles` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `hit_counter` VARCHAR(255) NOT NULL,
  `editor_type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `enhancement` VARCHAR(255) NOT NULL,
  `gallery_type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 4,
  `image_main_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `image_main_attribute` VARCHAR(255) NOT NULL,
  `gallery_images_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `gallery_images_limit` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `gallery_images_attribute` VARCHAR(255) NOT NULL,
  `variation_images_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `variation_images_limit` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `variation_images_attribute` varchar(255) NOT NULL,
  `default_image_url` VARCHAR(255) DEFAULT NULL,
  `variation_configurable_images` TEXT DEFAULT NULL,
  `use_supersize_images` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `watermark_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `watermark_image` LONGBLOB DEFAULT NULL,
  `watermark_settings` TEXT DEFAULT NULL,
  PRIMARY KEY (`template_description_id`),
  INDEX `is_custom_template` (`is_custom_template`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_other_category`;
CREATE TABLE `m2epro_ebay_template_other_category` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` int(11) UNSIGNED NOT NULL,
  `account_id` int(11) UNSIGNED NOT NULL,
  `category_secondary_id` int(11) UNSIGNED NOT NULL,
  `category_secondary_path` varchar(255) DEFAULT NULL,
  `category_secondary_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 2,
  `category_secondary_attribute` varchar(255) NOT NULL,
  `store_category_main_id` decimal(20, 0) UNSIGNED NOT NULL,
  `store_category_main_path` varchar(255) DEFAULT NULL,
  `store_category_main_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `store_category_main_attribute` varchar(255) NOT NULL,
  `store_category_secondary_id` decimal(20, 0) UNSIGNED NOT NULL,
  `store_category_secondary_path` varchar(255) DEFAULT NULL,
  `store_category_secondary_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `store_category_secondary_attribute` varchar(255) NOT NULL,
  `update_date` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_payment`;
CREATE TABLE `m2epro_ebay_template_payment` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `pay_pal_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `pay_pal_email_address` VARCHAR(255) NOT NULL,
  `pay_pal_immediate_payment` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `is_custom_template` (`is_custom_template`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_payment_service`;
CREATE TABLE `m2epro_ebay_template_payment_service` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_payment_id` INT(11) UNSIGNED NOT NULL,
  `code_name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `template_payment_id` (`template_payment_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_return`;
CREATE TABLE `m2epro_ebay_template_return` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `accepted` VARCHAR(255) NOT NULL,
  `option` VARCHAR(255) NOT NULL,
  `within` VARCHAR(255) NOT NULL,
  `shipping_cost` VARCHAR(255) NOT NULL,
  `international_accepted` VARCHAR(255) NOT NULL,
  `international_option` VARCHAR(255) NOT NULL,
  `international_within` VARCHAR(255) NOT NULL,
  `international_shipping_cost` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `is_custom_template` (`is_custom_template`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_selling_format`;
CREATE TABLE `m2epro_ebay_template_selling_format` (
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `listing_type` TINYINT(2) UNSIGNED NOT NULL,
  `listing_type_attribute` VARCHAR(255) NOT NULL,
  `listing_is_private` TINYINT(2) UNSIGNED NOT NULL,
  `restricted_to_business` TINYINT(2) UNSIGNED DEFAULT 0,
  `duration_mode` TINYINT(4) UNSIGNED NOT NULL,
  `duration_attribute` VARCHAR(255) NOT NULL,
  `out_of_stock_control` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `qty_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_custom_value` INT(11) UNSIGNED NOT NULL,
  `qty_custom_attribute` VARCHAR(255) NOT NULL,
  `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100,
  `qty_modification_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_min_posted_value` INT(11) UNSIGNED DEFAULT NULL,
  `qty_max_posted_value` INT(11) UNSIGNED DEFAULT NULL,
  `lot_size_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `lot_size_custom_value` INT(11) UNSIGNED DEFAULT NULL,
  `lot_size_attribute` VARCHAR(255) DEFAULT NULL,
  `vat_percent` FLOAT UNSIGNED NOT NULL DEFAULT 0,
  `tax_table_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `tax_category_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `tax_category_value` VARCHAR(255) NOT NULL,
  `tax_category_attribute` VARCHAR(255) NOT NULL,
  `price_increase_vat_percent` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `fixed_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `fixed_price_coefficient` VARCHAR(255) NOT NULL,
  `fixed_price_custom_attribute` VARCHAR(255) NOT NULL,
  `start_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `start_price_coefficient` VARCHAR(255) NOT NULL,
  `start_price_custom_attribute` VARCHAR(255) NOT NULL,
  `reserve_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `reserve_price_coefficient` VARCHAR(255) NOT NULL,
  `reserve_price_custom_attribute` VARCHAR(255) NOT NULL,
  `buyitnow_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `buyitnow_price_coefficient` VARCHAR(255) NOT NULL,
  `buyitnow_price_custom_attribute` VARCHAR(255) NOT NULL,
  `price_discount_stp_mode` TINYINT(2) UNSIGNED NOT NULL,
  `price_discount_stp_attribute` VARCHAR(255) NOT NULL,
  `price_discount_stp_type` TINYINT(2) UNSIGNED NOT NULL,
  `price_discount_map_mode` TINYINT(2) UNSIGNED NOT NULL,
  `price_discount_map_attribute` VARCHAR(255) NOT NULL,
  `price_discount_map_exposure_type` TINYINT(2) UNSIGNED NOT NULL,
  `best_offer_mode` TINYINT(2) UNSIGNED NOT NULL,
  `best_offer_accept_mode` TINYINT(2) UNSIGNED NOT NULL,
  `best_offer_accept_value` VARCHAR(255) NOT NULL,
  `best_offer_accept_attribute` VARCHAR(255) NOT NULL,
  `best_offer_reject_mode` TINYINT(2) UNSIGNED NOT NULL,
  `best_offer_reject_value` VARCHAR(255) NOT NULL,
  `best_offer_reject_attribute` VARCHAR(255) NOT NULL,
  `charity` VARCHAR(255) DEFAULT NULL,
  `ignore_variations` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`template_selling_format_id`),
  INDEX `is_custom_template` (`is_custom_template`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_shipping`;
CREATE TABLE `m2epro_ebay_template_shipping` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `country_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `country_custom_value` VARCHAR(255) NOT NULL,
  `country_custom_attribute` VARCHAR(255) NOT NULL,
  `postal_code_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `postal_code_custom_value` VARCHAR(255) NOT NULL,
  `postal_code_custom_attribute` VARCHAR(255) NOT NULL,
  `address_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `address_custom_value` VARCHAR(255) NOT NULL,
  `address_custom_attribute` VARCHAR(255) NOT NULL,
  `dispatch_time_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `dispatch_time_value` INT(11) UNSIGNED NOT NULL DEFAULT 1,
  `dispatch_time_attribute` VARCHAR(255) DEFAULT NULL,
  `local_shipping_rate_table` TEXT DEFAULT NULL,
  `international_shipping_rate_table` TEXT DEFAULT NULL,
  `local_shipping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `local_shipping_discount_promotional_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `local_shipping_discount_combined_profile_id` TEXT DEFAULT NULL,
  `click_and_collect_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `cash_on_delivery_cost` VARCHAR(255) DEFAULT NULL,
  `international_shipping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `international_shipping_discount_promotional_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `international_shipping_discount_combined_profile_id` TEXT DEFAULT NULL,
  `excluded_locations` TEXT DEFAULT NULL,
  `cross_border_trade` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `global_shipping_program` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `is_custom_template` (`is_custom_template`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_shipping_calculated`;
CREATE TABLE `m2epro_ebay_template_shipping_calculated` (
  `template_shipping_id` INT(11) UNSIGNED NOT NULL,
  `measurement_system` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `package_size_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `package_size_value` VARCHAR(500) NOT NULL,
  `package_size_attribute` VARCHAR(255) NOT NULL,
  `dimension_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `dimension_width_value` VARCHAR(500) NOT NULL,
  `dimension_width_attribute` VARCHAR(255) NOT NULL,
  `dimension_length_value` VARCHAR(500) NOT NULL,
  `dimension_length_attribute` VARCHAR(255) NOT NULL,
  `dimension_depth_value` VARCHAR(500) NOT NULL,
  `dimension_depth_attribute` VARCHAR(255) NOT NULL,
  `weight_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `weight_minor` VARCHAR(500) NOT NULL,
  `weight_major` VARCHAR(500) NOT NULL,
  `weight_attribute` VARCHAR(255) NOT NULL,
  `local_handling_cost` VARCHAR(255) DEFAULT NULL,
  `international_handling_cost` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`template_shipping_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_shipping_service`;
CREATE TABLE `m2epro_ebay_template_shipping_service` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_shipping_id` INT(11) UNSIGNED NOT NULL,
  `shipping_type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `shipping_value` VARCHAR(255) NOT NULL,
  `cost_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `cost_value` VARCHAR(255) NOT NULL,
  `cost_additional_value` VARCHAR(255) NOT NULL,
  `cost_surcharge_value` VARCHAR(255) NOT NULL,
  `locations` TEXT NOT NULL,
  `priority` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `priority` (`priority`),
  INDEX `template_shipping_id` (`template_shipping_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_synchronization`;
CREATE TABLE `m2epro_ebay_template_synchronization` (
  `template_synchronization_id` INT(11) UNSIGNED NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `list_mode` TINYINT(2) UNSIGNED NOT NULL,
  `list_status_enabled` TINYINT(2) UNSIGNED NOT NULL,
  `list_is_in_stock` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `list_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `list_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `list_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  `revise_update_qty` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_qty_max_applied_value_mode` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_qty_max_applied_value` INT(11) UNSIGNED DEFAULT NULL,
  `revise_update_price` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_price_max_allowed_deviation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_price_max_allowed_deviation` INT(11) UNSIGNED DEFAULT NULL,
  `revise_update_title` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_sub_title` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_description` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_images` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_categories` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_shipping` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_payment` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_return` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_other` TINYINT(2) UNSIGNED NOT NULL,
  `relist_mode` TINYINT(2) UNSIGNED NOT NULL,
  `relist_filter_user_lock` TINYINT(2) UNSIGNED NOT NULL,
  `relist_status_enabled` TINYINT(2) UNSIGNED NOT NULL,
  `relist_is_in_stock` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `relist_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `relist_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `relist_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  `stop_mode` TINYINT(2) UNSIGNED NOT NULL,
  `stop_status_disabled` TINYINT(2) UNSIGNED NOT NULL,
  `stop_out_off_stock` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `stop_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `stop_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `stop_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`template_synchronization_id`),
  INDEX `is_custom_template` (`is_custom_template`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

$installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/component/ebay/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/ebay/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/ebay/update_accounts_preferences/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/update_accounts_preferences/', 'interval', '86400', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/template/remove_unused/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/template/remove_unused/', 'interval', '3600', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/channel/synchronize_changes/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/channel/synchronize_changes/', 'interval', '300', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/feedbacks/download_new/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/feedbacks/download_new/', 'interval', '10800', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/feedbacks/send_response/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/feedbacks/send_response/', 'interval', '10800', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/other/resolve_sku/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/other/resolve_sku/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/other/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/other/channel/synchronize_data/', 'interval', '86400', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/product/process_instructions/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/product/process_instructions/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/product/process_scheduled_actions/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/product/process_scheduled_actions/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/product/process_actions/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/product/process_actions/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/product/remove_potential_duplicates/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/ebay/listing/product/remove_potential_duplicates/', 'interval', '60', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/ebay/order/create_failed/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/order/create_failed/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/order/update/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/order/update/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/order/cancel/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/order/cancel/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/order/reserve_cancel/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/order/reserve_cancel/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/pickup_store/schedule_for_update/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/ebay/pickup_store/schedule_for_update/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/pickup_store/update_on_channel/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/ebay/pickup_store/update_on_channel/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/listing/product/inspector/ebay/', 'max_allowed_instructions_count', '2000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/listing/product/revise/total/ebay/', 'mode', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/listing/product/revise/total/ebay/', 'max_allowed_instructions_count', '2000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/list/', 'priority_coefficient', '25', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/list/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/relist/', 'priority_coefficient', '125', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/relist/', 'wait_increase_coefficient',  '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_qty/', 'priority_coefficient', '500', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_qty/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_price/', 'priority_coefficient', '250', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_price/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_title/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_title/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_subtitle/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_subtitle/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_description/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_description/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_images/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_images/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_categories/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_categories/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_payment/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_payment/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_shipping/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_shipping/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_return/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_return/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_other/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/revise_other/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/stop/', 'priority_coefficient', '1000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/action/stop/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/listing/product/scheduled_actions/', 'max_prepared_actions_count', '3000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/order/settings/marketplace_8/', 'use_first_street_line_as_company', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/connector/listing/', 'check_the_same_product_already_listed', '1', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/ebay/variation/', 'mpn_can_be_changed', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/ebay/template/category/', 'use_last_specifics', '0', '0 - false, \r\n1 - true',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/motors/', 'epids_motor_attribute', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/motors/', 'epids_uk_attribute', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/motors/', 'epids_de_attribute', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/motors/', 'ktypes_attribute', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/sell_on_another_marketplace/', 'tutorial_shown', '0', NULL, '2014-06-25 00:00:00', '2014-06-25 00:00:00'),
  ('/ebay/translation_services/gold/', 'avg_cost', '7.21', NULL, '2014-07-31 00:00:00', '2014-07-31 00:00:00'),
  ('/ebay/translation_services/silver/', 'avg_cost', '1.21', NULL, '2014-07-31 00:00:00', '2014-07-31 00:00:00'),
  ('/ebay/translation_services/platinum/', 'avg_cost', '17.51', NULL, '2014-07-31 00:00:00', '2014-07-31 00:00:00'),
  ('/ebay/description/', 'upload_images_mode', 2, NULL, '2015-08-21 00:00:00','2015-08-21 00:00:00'),
  ('/ebay/description/', 'should_be_ulrs_secure', 0, NULL, '2015-08-21 00:00:00','2015-08-21 00:00:00');

INSERT INTO `m2epro_marketplace` VALUES
  (1, 0, 'United States', 'US', 'ebay.com', 0, 1, 'America', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (2, 2, 'Canada', 'Canada', 'ebay.ca', 0, 8, 'America', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (3, 3, 'United Kingdom', 'UK', 'ebay.co.uk', 0, 2, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (4, 15, 'Australia', 'Australia', 'ebay.com.au', 0, 4, 'Australia Region', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (5, 16, 'Austria', 'Austria', 'ebay.at', 0, 5, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (6, 23, 'Belgium (French)', 'Belgium_French', 'befr.ebay.be', 0, 7, 'Europe', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (7, 71, 'France', 'France', 'ebay.fr', 0, 10, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (8, 77, 'Germany', 'Germany', 'ebay.de', 0, 3, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (9, 100, 'eBay Motors', 'eBayMotors', 'ebay.com/motors', 0, 23, 'Other', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (10, 101, 'Italy', 'Italy', 'ebay.it', 0, 14, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (11, 123, 'Belgium (Dutch)', 'Belgium_Dutch', 'benl.ebay.be', 0, 6, 'Europe', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (12, 146, 'Netherlands', 'Netherlands', 'ebay.nl', 0, 16, 'Europe', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (13, 186, 'Spain', 'Spain', 'ebay.es', 0, 19, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (14, 193, 'Switzerland', 'Switzerland', 'ebay.ch', 0, 22, 'Europe', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (15, 201, 'Hong Kong', 'HongKong', 'ebay.com.hk', 0, 11, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (16, 203, 'India', 'India', 'ebay.in', 0, 12, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (17, 205, 'Ireland', 'Ireland', 'ebay.ie', 0, 13, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (18, 207, 'Malaysia', 'Malaysia', 'ebay.com.my', 0, 15, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (19, 210, 'Canada (French)', 'CanadaFrench', 'cafr.ebay.ca', 0, 9, 'America', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (20, 211, 'Philippines', 'Philippines', 'ebay.ph', 0, 17, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (21, 212, 'Poland', 'Poland', 'ebay.pl', 0, 18, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (22, 216, 'Singapore', 'Singapore', 'ebay.com.sg', 0, 20, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00');

INSERT INTO `m2epro_ebay_marketplace` VALUES
    (1, 'USD', 'us', 'en_US', 0, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 0, 1, 1, 0, 1, 0, 0, 0),
    (2, 'CAD', 'ca', 'en_CA', 0, 1, 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (3, 'GBP', 'gb', 'en_GB', 3, 1, 1, 0, 0, 1, 1, 1, 0, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 1, 1),
    (4, 'AUD', 'au', 'en_AU', 0, 1, 1, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 1, 1, 0, 0, 1),
    (5, 'EUR', 'at', 'de_AT', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0),
    (6, 'EUR', 'be', 'nl_BE', 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (7, 'EUR', 'fr', 'fr_FR', 1, 1, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 1),
    (8, 'EUR', 'de', 'de_DE', 3, 1, 0, 0, 0, 1, 1, 1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 0, 1, 1, 1),
    (9, 'USD', 'us', 'en_US', 0, 1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 0, 0, 1, 1, 0, 0, 0, 1, 0),
    (10, 'EUR', 'it', 'it_IT', 1, 1, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1),
    (11, 'EUR', 'be', 'fr_BE', 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (12, 'EUR', 'nl', 'nl_NL', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (13, 'EUR', 'es', 'es_ES', 1, 1, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 1),
    (14, 'CHF', 'ch', 'fr_CH', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (15, 'HKD', 'hk', 'zh_HK', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (16, 'INR', 'in', 'hi_IN', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (17, 'EUR', 'ie', 'en_IE', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (18, 'MYR', 'my', 'ms_MY', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (19, 'CAD', 'ca', 'fr_CA', 0, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (20, 'PHP', 'ph', 'fil_PH', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (21, 'PLN', 'pl', 'pl_PL', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (22, 'SGD', 'sg', 'zh_SG', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0);

SQL
);

//########################################

// AMAZON SCRIPT
$installer->run(<<<SQL

DROP TABLE IF EXISTS `m2epro_amazon_account`;
CREATE TABLE `m2epro_amazon_account` (
  `account_id` INT(11) UNSIGNED NOT NULL,
  `server_hash` VARCHAR(255) NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `merchant_id` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) DEFAULT NULL,
  `related_store_id` INT(11) NOT NULL DEFAULT 0,
  `other_listings_synchronization` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `other_listings_mapping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `other_listings_mapping_settings` VARCHAR(255) DEFAULT NULL,
  `magento_orders_settings` TEXT NOT NULL,
  `is_vat_calculation_service_enabled` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_magento_invoice_creation_disabled` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `info` TEXT DEFAULT NULL,
  PRIMARY KEY (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_account_repricing`;
CREATE TABLE `m2epro_amazon_account_repricing` (
  `account_id` INT(11) UNSIGNED NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `token` VARCHAR(255) DEFAULT NULL,
  `total_products` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `regular_price_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `regular_price_attribute` VARCHAR(255) NOT NULL,
  `regular_price_coefficient` VARCHAR(255) NOT NULL,
  `regular_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `min_price_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `min_price_value` DECIMAL(14, 2) UNSIGNED DEFAULT NULL,
  `min_price_percent` INT(11) UNSIGNED DEFAULT NULL,
  `min_price_attribute` VARCHAR(255) NOT NULL,
  `min_price_coefficient` VARCHAR(255) NOT NULL,
  `min_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `max_price_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `max_price_value` DECIMAL(14, 2) UNSIGNED DEFAULT NULL,
  `max_price_percent` INT(11) UNSIGNED DEFAULT NULL,
  `max_price_attribute` VARCHAR(255) NOT NULL,
  `max_price_coefficient` VARCHAR(255) NOT NULL,
  `max_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `disable_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `disable_mode_attribute` VARCHAR(255) NOT NULL,
  `last_checked_listing_product_update_date` DATETIME DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_dictionary_category`;
CREATE TABLE `m2epro_amazon_dictionary_category` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `parent_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `browsenode_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `product_data_nicks` VARCHAR(500) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `path` VARCHAR(500) DEFAULT NULL,
  `keywords` TEXT DEFAULT NULL,
  `is_leaf` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `browsenode_id` (`browsenode_id`),
  INDEX `category_id` (`category_id`),
  INDEX `is_leaf` (`is_leaf`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `path` (`path`),
  INDEX `parent_category_id` (`parent_category_id`),
  INDEX `title` (`title`),
  INDEX `product_data_nicks` (`product_data_nicks`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_dictionary_category_product_data`;
CREATE TABLE `m2epro_amazon_dictionary_category_product_data` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `browsenode_id` INT(11) UNSIGNED NOT NULL,
  `product_data_nick` VARCHAR(255) NOT NULL,
  `is_applicable` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `required_attributes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `browsenode_id` (`browsenode_id`),
  INDEX `product_data_nick` (`product_data_nick`),
  INDEX `is_applicable` (`is_applicable`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_dictionary_marketplace`;
CREATE TABLE `m2epro_amazon_dictionary_marketplace` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `client_details_last_update_date` DATETIME DEFAULT NULL,
  `server_details_last_update_date` DATETIME DEFAULT NULL,
  `product_data` LONGTEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_dictionary_specific`;
CREATE TABLE `m2epro_amazon_dictionary_specific` (
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
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_item`;
CREATE TABLE `m2epro_amazon_item` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `store_id` INT(11) UNSIGNED NOT NULL,
  `variation_product_options` TEXT DEFAULT NULL,
  `variation_channel_options` TEXT DEFAULT NULL,
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

DROP TABLE IF EXISTS `m2epro_amazon_listing`;
CREATE TABLE `m2epro_amazon_listing` (
  `listing_id` INT(11) UNSIGNED NOT NULL,
  `auto_global_adding_description_template_id` int(11) UNSIGNED DEFAULT NULL,
  `auto_website_adding_description_template_id` int(11) UNSIGNED DEFAULT NULL,
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `template_synchronization_id` INT(11) UNSIGNED NOT NULL,
  `sku_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `sku_custom_attribute` VARCHAR(255) NOT NULL,
  `sku_modification_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `sku_modification_custom_value` VARCHAR(255) NOT NULL,
  `generate_sku_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `general_id_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `general_id_custom_attribute` VARCHAR(255) NOT NULL,
  `worldwide_id_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `worldwide_id_custom_attribute` VARCHAR(255) NOT NULL,
  `search_by_magento_title_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `condition_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `condition_value` VARCHAR(255) NOT NULL,
  `condition_custom_attribute` VARCHAR(255) NOT NULL,
  `condition_note_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `condition_note_value` VARCHAR(2000) NOT NULL,
  `image_main_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `image_main_attribute` varchar(255) NOT NULL,
  `gallery_images_mode` tinyint(2) UNSIGNED NOT NULL,
  `gallery_images_limit` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `gallery_images_attribute` varchar(255) NOT NULL,
  `gift_wrap_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `gift_wrap_attribute` VARCHAR(255) NOT NULL,
  `gift_message_mode` TINYINT(2) UNSIGNED NOT NULL,
  `gift_message_attribute` VARCHAR(255) NOT NULL,
  `handling_time_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `handling_time_value` INT(11) UNSIGNED NOT NULL DEFAULT 1,
  `handling_time_custom_attribute` VARCHAR(255) NOT NULL,
  `restock_date_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `restock_date_value` DATETIME NOT NULL,
  `restock_date_custom_attribute` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`listing_id`),
  INDEX `auto_global_adding_description_template_id` (`auto_global_adding_description_template_id`),
  INDEX `auto_website_adding_description_template_id` (`auto_website_adding_description_template_id`),
  INDEX `generate_sku_mode` (`generate_sku_mode`),
  INDEX `template_selling_format_id` (`template_selling_format_id`),
  INDEX `template_synchronization_id` (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_auto_category_group`;
CREATE TABLE `m2epro_amazon_listing_auto_category_group` (
    `listing_auto_category_group_id` int(11) UNSIGNED NOT NULL,
    `adding_description_template_id` int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`listing_auto_category_group_id`),
    INDEX `adding_description_template_id` (`adding_description_template_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_other`;
CREATE TABLE `m2epro_amazon_listing_other` (
  `listing_other_id` INT(11) UNSIGNED NOT NULL,
  `general_id` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `online_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  `is_afn_channel` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_isbn_general_id` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_repricing` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_repricing_disabled` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_repricing_inactive` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`listing_other_id`),
  INDEX `general_id` (`general_id`),
  INDEX `is_afn_channel` (`is_afn_channel`),
  INDEX `is_isbn_general_id` (`is_isbn_general_id`),
  INDEX `is_repricing` (`is_repricing`),
  INDEX `is_repricing_disabled` (`is_repricing_disabled`),
  INDEX `is_repricing_inactive` (`is_repricing_inactive`),
  INDEX `online_price` (`online_price`),
  INDEX `online_qty` (`online_qty`),
  INDEX `sku` (`sku`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product`;
CREATE TABLE `m2epro_amazon_listing_product` (
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `template_description_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_shipping_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_product_tax_code_id` INT(11) UNSIGNED DEFAULT NULL,
  `is_variation_product` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_variation_product_matched` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_variation_channel_matched` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_variation_parent` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `variation_parent_id` INT(11) UNSIGNED DEFAULT NULL,
  `variation_parent_need_processor` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `variation_child_statuses` TEXT DEFAULT NULL,
  `general_id` VARCHAR(255) DEFAULT NULL,
  `general_id_search_info` TEXT DEFAULT NULL,
  `search_settings_status` TINYINT(2) UNSIGNED DEFAULT NULL,
  `search_settings_data` LONGTEXT DEFAULT NULL,
  `sku` VARCHAR(255) DEFAULT NULL,
  `online_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_regular_sale_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_regular_sale_price_start_date` DATETIME DEFAULT NULL,
  `online_regular_sale_price_end_date` DATETIME DEFAULT NULL,
  `online_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_business_discounts` TEXT DEFAULT NULL,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  `online_handling_time` INT(11) UNSIGNED DEFAULT NULL,
  `online_restock_date` DATETIME DEFAULT NULL,
  `online_details_data` LONGTEXT DEFAULT NULL,
  `online_images_data` LONGTEXT DEFAULT NULL,
  `is_details_data_changed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_images_data_changed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_repricing` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_afn_channel` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_isbn_general_id` TINYINT(2) UNSIGNED DEFAULT NULL,
  `is_general_id_owner` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `variation_parent_afn_state` SMALLINT(4) UNSIGNED DEFAULT NULL,
  `variation_parent_repricing_state` SMALLINT(4) UNSIGNED DEFAULT NULL,
  `defected_messages` TEXT DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  INDEX `general_id` (`general_id`),
  INDEX `search_settings_status` (`search_settings_status`),
  INDEX `is_repricing` (`is_repricing`),
  INDEX `is_afn_channel` (`is_afn_channel`),
  INDEX `is_isbn_general_id` (`is_isbn_general_id`),
  INDEX `is_variation_product_matched` (`is_variation_product_matched`),
  INDEX `is_variation_channel_matched` (`is_variation_channel_matched`),
  INDEX `is_variation_product` (`is_variation_product`),
  INDEX `online_regular_price` (`online_regular_price`),
  INDEX `online_qty` (`online_qty`),
  INDEX `online_regular_sale_price` (`online_regular_sale_price`),
  INDEX `online_business_price` (`online_business_price`),
  INDEX `sku` (`sku`),
  INDEX `is_variation_parent` (`is_variation_parent`),
  INDEX `variation_parent_need_processor` (`variation_parent_need_processor`),
  INDEX `variation_parent_id` (`variation_parent_id`),
  INDEX `is_general_id_owner` (`is_general_id_owner`),
  INDEX `variation_parent_afn_state` (`variation_parent_afn_state`),
  INDEX `variation_parent_repricing_state` (`variation_parent_repricing_state`),
  INDEX `template_shipping_id` (`template_shipping_id`),
  INDEX `template_product_tax_code_id` (`template_product_tax_code_id`),
  INDEX `template_description_id` (`template_description_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product_repricing`;
CREATE TABLE `m2epro_amazon_listing_product_repricing` (
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `is_online_disabled` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_online_inactive` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `online_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_min_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_max_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `last_updated_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `last_updated_min_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `last_updated_max_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `last_updated_is_disabled` TINYINT(2) UNSIGNED DEFAULT NULL,
  `is_process_required` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `last_synchronization_date` DATETIME DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  INDEX `is_online_disabled` (`is_online_disabled`),
  INDEX `is_online_inactive` (`is_online_inactive`),
  INDEX `is_process_required` (`is_process_required`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product_variation`;
CREATE TABLE `m2epro_amazon_listing_product_variation` (
  `listing_product_variation_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_product_variation_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product_variation_option`;
CREATE TABLE `m2epro_amazon_listing_product_variation_option` (
  `listing_product_variation_option_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_product_variation_option_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product_action_processing`;
CREATE TABLE `m2epro_amazon_listing_product_action_processing` (
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

DROP TABLE IF EXISTS `m2epro_amazon_listing_product_action_processing_list_sku`;
CREATE TABLE `m2epro_amazon_listing_product_action_processing_list_sku` (
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

DROP TABLE IF EXISTS `m2epro_amazon_indexer_listing_product_parent`;
CREATE TABLE `m2epro_amazon_indexer_listing_product_parent` (
    `listing_product_id` INT(11) UNSIGNED NOT NULL,
    `listing_id` INT(11) UNSIGNED NOT NULL,
    `min_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `max_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `min_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `max_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `create_date` DATETIME NOT NULL,
    PRIMARY KEY (`listing_product_id`),
    INDEX `listing_id` (`listing_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_marketplace`;
CREATE TABLE `m2epro_amazon_marketplace` (
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `developer_key` VARCHAR(255) DEFAULT NULL,
  `default_currency` VARCHAR(255) NOT NULL,
  `is_new_asin_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `is_merchant_fulfillment_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_business_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_vat_calculation_service_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_product_tax_code_policy_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_automatic_token_retrieving_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`marketplace_id`),
  INDEX `is_new_asin_available` (`is_new_asin_available`),
  INDEX `is_merchant_fulfillment_available` (`is_merchant_fulfillment_available`),
  INDEX `is_business_available` (`is_business_available`),
  INDEX `is_vat_calculation_service_available` (`is_vat_calculation_service_available`),
  INDEX `is_product_tax_code_policy_available` (`is_product_tax_code_policy_available`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_order`;
CREATE TABLE `m2epro_amazon_order` (
  `order_id` INT(11) UNSIGNED NOT NULL,
  `amazon_order_id` VARCHAR(255) NOT NULL,
  `seller_order_id` varchar(255) DEFAULT NULL,
  `is_afn_channel` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_prime` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_business` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `buyer_name` VARCHAR(255) NOT NULL,
  `buyer_email` VARCHAR(255) DEFAULT NULL,
  `shipping_service` VARCHAR(255) DEFAULT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_price` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `shipping_dates` TEXT DEFAULT NULL,
  `paid_amount` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `tax_details` TEXT DEFAULT NULL,
  `discount_details` TEXT DEFAULT NULL,
  `qty_shipped` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `qty_unshipped` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `currency` VARCHAR(10) NOT NULL,
  `purchase_update_date` DATETIME DEFAULT NULL,
  `purchase_create_date` DATETIME DEFAULT NULL,
  `merchant_fulfillment_data` TEXT NULL DEFAULT NULL,
  `merchant_fulfillment_label` BLOB NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  INDEX `amazon_order_id` (`amazon_order_id`),
  INDEX `seller_order_id` (`seller_order_id`),
  INDEX `is_prime` (`is_prime`),
  INDEX `is_business` (`is_business`),
  INDEX `buyer_email` (`buyer_email`),
  INDEX `buyer_name` (`buyer_name`),
  INDEX `paid_amount` (`paid_amount`),
  INDEX `purchase_create_date` (`purchase_create_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_order_item`;
CREATE TABLE `m2epro_amazon_order_item` (
  `order_item_id` INT(11) UNSIGNED NOT NULL,
  `amazon_order_item_id` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(255) DEFAULT NULL,
  `general_id` VARCHAR(255) DEFAULT NULL,
  `is_isbn_general_id` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `price` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `gift_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `gift_message` VARCHAR(500) DEFAULT NULL,
  `gift_type` VARCHAR(255) DEFAULT NULL,
  `tax_details` TEXT DEFAULT NULL,
  `discount_details` TEXT DEFAULT NULL,
  `currency` VARCHAR(10) NOT NULL,
  `qty_purchased` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_item_id`),
  INDEX `general_id` (`general_id`),
  INDEX `sku` (`sku`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_order_action_processing`;
CREATE TABLE `m2epro_amazon_order_action_processing` (
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

DROP TABLE IF EXISTS `m2epro_amazon_template_shipping`;
CREATE TABLE `m2epro_amazon_template_shipping` (
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

DROP TABLE IF EXISTS `m2epro_amazon_template_product_tax_code`;
CREATE TABLE `m2epro_amazon_template_product_tax_code` (
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

DROP TABLE IF EXISTS `m2epro_amazon_template_description`;
CREATE TABLE `m2epro_amazon_template_description` (
  `template_description_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `is_new_asin_accepted` TINYINT(2) UNSIGNED DEFAULT 0,
  `product_data_nick` VARCHAR(255) DEFAULT NULL,
  `category_path` VARCHAR(255) DEFAULT NULL,
  `browsenode_id` DECIMAL(20, 0) UNSIGNED DEFAULT NULL,
  `registered_parameter` VARCHAR(25) DEFAULT NULL,
  `worldwide_id_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `worldwide_id_custom_attribute` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`template_description_id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `is_new_asin_accepted` (`is_new_asin_accepted`),
  INDEX `product_data_nick` (`product_data_nick`),
  INDEX `browsenode_id` (`browsenode_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_description_definition`;
CREATE TABLE `m2epro_amazon_template_description_definition` (
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
  `item_package_quantity_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_package_quantity_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_package_quantity_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `number_of_items_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `number_of_items_custom_value` VARCHAR(255) DEFAULT NULL,
  `number_of_items_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `msrp_rrp_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `msrp_rrp_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_dimensions_volume_length_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_width_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_height_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_length_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_width_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_height_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_unit_of_measure_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_dimensions_volume_unit_of_measure_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_unit_of_measure_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_weight_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_dimensions_weight_custom_value` DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  `item_dimensions_weight_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_weight_unit_of_measure_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_dimensions_weight_unit_of_measure_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_weight_unit_of_measure_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `package_dimensions_volume_length_custom_value` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_width_custom_value` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_height_custom_value` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_length_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_width_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_height_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_unit_of_measure_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `package_dimensions_volume_unit_of_measure_custom_value` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_unit_of_measure_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `shipping_weight_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `shipping_weight_custom_value` DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  `shipping_weight_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `shipping_weight_unit_of_measure_mode` TINYINT(2) UNSIGNED DEFAULT 1,
  `shipping_weight_unit_of_measure_custom_value` VARCHAR(255) DEFAULT NULL,
  `shipping_weight_unit_of_measure_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_weight_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `package_weight_custom_value` DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  `package_weight_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_weight_unit_of_measure_mode` TINYINT(2) UNSIGNED DEFAULT 1,
  `package_weight_unit_of_measure_custom_value` VARCHAR(255) DEFAULT NULL,
  `package_weight_unit_of_measure_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `target_audience_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `target_audience` TEXT NOT NULL,
  `search_terms_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `search_terms` TEXT NOT NULL,
  `bullet_points_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `bullet_points` TEXT NOT NULL,
  `description_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `description_template` LONGTEXT NOT NULL,
  `image_main_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `image_main_attribute` VARCHAR(255) NOT NULL,
  `image_variation_difference_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `image_variation_difference_attribute` VARCHAR(255) NOT NULL,
  `gallery_images_mode` TINYINT(2) UNSIGNED NOT NULL,
  `gallery_images_limit` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `gallery_images_attribute` VARCHAR(255) NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`template_description_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_description_specific`;
CREATE TABLE `m2epro_amazon_template_description_specific` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_description_id` INT(11) UNSIGNED NOT NULL,
  `xpath` VARCHAR(255) NOT NULL,
  `mode` VARCHAR(25) NOT NULL,
  `is_required` TINYINT(2) UNSIGNED DEFAULT 0,
  `recommended_value` VARCHAR(255) DEFAULT NULL,
  `custom_value` VARCHAR(255) DEFAULT NULL,
  `custom_attribute` VARCHAR(255) DEFAULT NULL,
  `type` VARCHAR(25) DEFAULT NULL,
  `attributes` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `template_description_id` (`template_description_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_selling_format`;
CREATE TABLE `m2epro_amazon_template_selling_format` (
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `qty_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_custom_value` INT(11) UNSIGNED NOT NULL,
  `qty_custom_attribute` VARCHAR(255) NOT NULL,
  `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100,
  `qty_modification_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_min_posted_value` INT(11) UNSIGNED DEFAULT NULL,
  `qty_max_posted_value` INT(11) UNSIGNED DEFAULT NULL,
  `is_regular_customer_allowed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `is_business_customer_allowed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `regular_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_price_custom_attribute` VARCHAR(255) NOT NULL,
  `regular_price_coefficient` VARCHAR(255) NOT NULL,
  `regular_map_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_map_price_custom_attribute` VARCHAR(255) NOT NULL,
  `regular_sale_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_sale_price_custom_attribute` VARCHAR(255) NOT NULL,
  `regular_sale_price_coefficient` VARCHAR(255) NOT NULL,
  `regular_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_sale_price_start_date_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_sale_price_start_date_value` DATETIME NOT NULL,
  `regular_sale_price_start_date_custom_attribute` VARCHAR(255) NOT NULL,
  `regular_sale_price_end_date_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_sale_price_end_date_value` DATETIME NOT NULL,
  `regular_sale_price_end_date_custom_attribute` VARCHAR(255) NOT NULL,
  `regular_price_vat_percent` FLOAT UNSIGNED DEFAULT NULL,
  `business_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `business_price_custom_attribute` VARCHAR(255) NOT NULL,
  `business_price_coefficient` VARCHAR(255) NOT NULL,
  `business_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `business_price_vat_percent` FLOAT UNSIGNED DEFAULT NULL,
  `business_discounts_mode` TINYINT(2) UNSIGNED NOT NULL,
  `business_discounts_tier_coefficient` VARCHAR(255) NOT NULL,
  `business_discounts_tier_customer_group_id` INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`template_selling_format_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_selling_format_business_discount`;
CREATE TABLE `m2epro_amazon_template_selling_format_business_discount` (
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

DROP TABLE IF EXISTS `m2epro_amazon_template_synchronization`;
CREATE TABLE `m2epro_amazon_template_synchronization` (
  `template_synchronization_id` INT(11) UNSIGNED NOT NULL,
  `list_mode` TINYINT(2) UNSIGNED NOT NULL,
  `list_status_enabled` TINYINT(2) UNSIGNED NOT NULL,
  `list_is_in_stock` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `list_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `list_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `list_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  `revise_update_qty` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_qty_max_applied_value_mode` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_qty_max_applied_value` INT(11) UNSIGNED DEFAULT NULL,
  `revise_update_price` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_price_max_allowed_deviation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_price_max_allowed_deviation` INT(11) UNSIGNED DEFAULT NULL,
  `relist_mode` TINYINT(2) UNSIGNED NOT NULL,
  `relist_filter_user_lock` TINYINT(2) UNSIGNED NOT NULL,
  `relist_status_enabled` TINYINT(2) UNSIGNED NOT NULL,
  `relist_is_in_stock` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `relist_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `relist_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `relist_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  `stop_mode` TINYINT(2) UNSIGNED NOT NULL,
  `stop_status_disabled` TINYINT(2) UNSIGNED NOT NULL,
  `stop_out_off_stock` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `stop_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `stop_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `stop_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

$installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/amazon/', 'application_name', 'M2ePro - Amazon Magento Integration', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/amazon/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/amazon/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/other/resolve_title/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/other/resolve_title/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/other/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/other/channel/synchronize_data/', 'interval', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/other/channel/synchronize_data/blocked/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/other/channel/synchronize_data/blocked/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/channel/synchronize_data/', 'interval', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/channel/synchronize_data/blocked/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/channel/synchronize_data/blocked/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/channel/synchronize_data/defected/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/channel/synchronize_data/defected/', 'interval', '259200', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/run_variation_parent_processors/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/run_variation_parent_processors/', 'interval', '60', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/process_instructions/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/process_instructions/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/process_actions/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/process_actions/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/process_actions_results/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/listing/product/process_actions_results/', 'interval', '60', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/receive/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/receive/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/receive/details/', 'mode', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/receive/details/', 'interval', '7200', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/create_failed/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/create_failed/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/update/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/update/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/update/seller_order_id/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/update/seller_order_id/', 'interval', '3600', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/refund/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/refund/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/cancel/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/cancel/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/reserve_cancel/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/reserve_cancel/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/action/process_update/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/action/process_update/', 'interval', '3600', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/action/process_refund/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/action/process_refund/', 'interval', '18000', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/action/process_cancel/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/action/process_cancel/', 'interval', '18000', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/action/process_results/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/order/action/process_results/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/repricing/inspect_products/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/repricing/inspect_products/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/repricing/update_settings/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/repricing/update_settings/', 'interval', '180', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
 ('/cron/task/amazon/repricing/synchronize/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/amazon/repricing/synchronize/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/listing/product/inspector/amazon/', 'max_allowed_instructions_count', '2000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/listing/product/revise/total/amazon/', 'mode', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/listing/product/revise/total/amazon/', 'max_allowed_instructions_count', '2000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/scheduled_data/', 'limit', '20000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/processing/prepare/', 'max_listings_products_count', '2000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/list/', 'priority_coefficient', '25', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/list/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/list/', 'min_allowed_wait_interval', '3600', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/relist/', 'priority_coefficient', '125', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/relist/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/relist/', 'min_allowed_wait_interval', '1800', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_qty/', 'priority_coefficient', '500', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_qty/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_qty/', 'min_allowed_wait_interval', '900', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_price/', 'priority_coefficient', '250', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_price/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_price/', 'min_allowed_wait_interval', '1800', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_details/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_details/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_details/', 'min_allowed_wait_interval', '7200', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_images/', 'priority_coefficient', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_images/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/revise_images/', 'min_allowed_wait_interval', '7200', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/stop/', 'priority_coefficient', '1000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/stop/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/stop/', 'min_allowed_wait_interval', '600', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/delete/', 'priority_coefficient', '1000', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/delete/', 'wait_increase_coefficient', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/listing/product/action/delete/', 'min_allowed_wait_interval', '600', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/order/settings/marketplace_25/', 'use_first_street_line_as_company', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
   ('/amazon/repricing/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
   ('/amazon/repricing/', 'base_url', 'https://repricer.m2epro.com/connector/m2epro/', 'Repricing Tool base url',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
   ('/amazon/business/', 'mode', '0', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO `m2epro_marketplace` VALUES
  (24, 4, 'Canada', 'CA', 'amazon.ca', 0, 4, 'America', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (25, 3, 'Germany', 'DE', 'amazon.de', 0, 3, 'Europe', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (26, 5, 'France', 'FR', 'amazon.fr', 0, 7, 'Europe', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (27, 6, 'Japan', 'JP', 'amazon.co.jp', 0, 6, 'Asia / Pacific', 'amazon', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (28, 2, 'United Kingdom', 'UK', 'amazon.co.uk', 0, 2, 'Europe', 'amazon', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (29, 1, 'United States', 'US', 'amazon.com', 0, 1, 'America', 'amazon', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (30, 7, 'Spain', 'ES', 'amazon.es', 0, 8, 'Europe', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (31, 8, 'Italy', 'IT', 'amazon.it', 0, 5, 'Europe', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (32, 9, 'China', 'CN', 'amazon.cn', 0, 9, 'Asia / Pacific', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (34, 9, 'Mexico', 'MX', 'amazon.com.mx', 0, 10, 'America', 'amazon', '2017-09-27 00:00:00', '2017-09-27 00:00:00'),
  (35, 10, 'Australia', 'AU', 'amazon.com.au', 0, 11, 'Australia Region', 'amazon',
   '2017-09-27 00:00:00', '2017-09-27 00:00:00'),
  (36, 0, 'India', 'IN', 'amazon.in', 0, 12, 'Asia / Pacific', 'amazon', '2017-09-27 00:00:00', '2017-09-27 00:00:00');

INSERT INTO `m2epro_amazon_marketplace` VALUES
  (24, '8636-1433-4377', 'CAD',1,0,0,0,0,1),
  (25, '7078-7205-1944', 'EUR',1,1,1,1,1,1),
  (26, '7078-7205-1944', 'EUR',1,0,1,1,1,1),
  (27, NULL, '',0,0,0,0,0,0),
  (28, '7078-7205-1944', 'GBP',1,1,1,1,1,1),
  (29, '8636-1433-4377', 'USD',1,1,1,0,0,1),
  (30, '7078-7205-1944', 'EUR',1,0,1,1,1,1),
  (31, '7078-7205-1944', 'EUR',1,0,1,1,1,1),
  (32, NULL, '',0,0,0,0,0,0),
  (34, '8636-1433-4377', 'MXN',1,0,0,0,0,1),
  (35, '2770-5005-3793', 'AUD',1,0,0,0,0,1),
  (36, NULL, '',0,0,0,0,0,0);

SQL
);

//########################################

// WALMART SCRIPT
$installer->run(<<<SQL

DROP TABLE IF EXISTS `m2epro_walmart_account`;
CREATE TABLE `m2epro_walmart_account` (
  `account_id` INT(11) UNSIGNED NOT NULL,
  `server_hash` VARCHAR(255) NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `consumer_id` VARCHAR(255) NOT NULL,
  `private_key` TEXT NOT NULL,
  `related_store_id` INT(11) NOT NULL DEFAULT 0,
  `other_listings_synchronization` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `other_listings_mapping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `other_listings_mapping_settings` TEXT DEFAULT NULL,
  `magento_orders_settings` TEXT NOT NULL,
  `orders_last_synchronization` DATETIME DEFAULT NULL,
  `info` TEXT DEFAULT NULL,
  PRIMARY KEY (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_dictionary_category`;
CREATE TABLE `m2epro_walmart_dictionary_category` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `parent_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `browsenode_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `product_data_nicks` VARCHAR(500) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `path` VARCHAR(500) DEFAULT NULL,
  `keywords` TEXT DEFAULT NULL,
  `is_leaf` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `browsenode_id` (`browsenode_id`),
  INDEX `category_id` (`category_id`),
  INDEX `is_leaf` (`is_leaf`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `path` (`path`),
  INDEX `parent_category_id` (`parent_category_id`),
  INDEX `title` (`title`),
  INDEX `product_data_nicks` (`product_data_nicks`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_dictionary_marketplace`;
CREATE TABLE `m2epro_walmart_dictionary_marketplace` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `client_details_last_update_date` DATETIME DEFAULT NULL,
  `server_details_last_update_date` DATETIME DEFAULT NULL,
  `product_data` LONGTEXT DEFAULT NULL,
  `tax_codes` LONGTEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_dictionary_specific`;
CREATE TABLE `m2epro_walmart_dictionary_specific` (
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
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_item`;
CREATE TABLE `m2epro_walmart_item` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `store_id` INT(11) UNSIGNED NOT NULL,
  `variation_product_options` TEXT DEFAULT NULL,
  `variation_channel_options` TEXT DEFAULT NULL,
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

DROP TABLE IF EXISTS `m2epro_walmart_listing`;
CREATE TABLE `m2epro_walmart_listing` (
  `listing_id` INT(11) UNSIGNED NOT NULL,
  `auto_global_adding_category_template_id` int(11) UNSIGNED DEFAULT NULL,
  `auto_website_adding_category_template_id` int(11) UNSIGNED DEFAULT NULL,
  `template_description_id` INT(11) UNSIGNED NOT NULL,
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `template_synchronization_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_id`),
  INDEX `auto_global_adding_description_template_id` (`auto_global_adding_category_template_id`),
  INDEX `auto_website_adding_description_template_id` (`auto_website_adding_category_template_id`),
  INDEX `template_selling_format_id` (`template_selling_format_id`),
  INDEX `template_description_id` (`template_description_id`),
  INDEX `template_synchronization_id` (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_listing_auto_category_group`;
CREATE TABLE `m2epro_walmart_listing_auto_category_group` (
    `listing_auto_category_group_id` int(11) UNSIGNED NOT NULL,
    `adding_category_template_id` int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`listing_auto_category_group_id`),
    INDEX `adding_category_template_id` (`adding_category_template_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_listing_other`;
CREATE TABLE `m2epro_walmart_listing_other` (
  `listing_other_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `gtin` VARCHAR(255) DEFAULT NULL,
  `upc` VARCHAR(255) DEFAULT NULL,
  `ean` VARCHAR(255) DEFAULT NULL,
  `wpid` VARCHAR(255) DEFAULT NULL,
  `item_id` VARCHAR(255) DEFAULT NULL,
  `channel_url` VARCHAR(255) DEFAULT NULL,
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

DROP TABLE IF EXISTS `m2epro_walmart_listing_product`;
CREATE TABLE `m2epro_walmart_listing_product` (
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
  `channel_url` VARCHAR(255) DEFAULT NULL,
  `publish_status` VARCHAR(255) DEFAULT NULL,
  `lifecycle_status` VARCHAR(255) DEFAULT NULL,
  `status_change_reasons` TEXT DEFAULT NULL,
  `online_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `is_online_price_invalid` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `online_promotions` TEXT DEFAULT NULL,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  `online_lag_time` INT(11) UNSIGNED DEFAULT NULL,
  `online_details_data` LONGTEXT DEFAULT NULL,
  `is_details_data_changed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
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

DROP TABLE IF EXISTS `m2epro_walmart_listing_product_variation`;
CREATE TABLE `m2epro_walmart_listing_product_variation` (
  `listing_product_variation_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_product_variation_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_listing_product_variation_option`;
CREATE TABLE `m2epro_walmart_listing_product_variation_option` (
  `listing_product_variation_option_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_product_variation_option_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_listing_product_action_processing`;
CREATE TABLE `m2epro_walmart_listing_product_action_processing` (
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

DROP TABLE IF EXISTS `m2epro_walmart_listing_product_action_processing_list`;
CREATE TABLE `m2epro_walmart_listing_product_action_processing_list` (
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

DROP TABLE IF EXISTS `m2epro_walmart_indexer_listing_product_parent`;
CREATE TABLE `m2epro_walmart_indexer_listing_product_parent` (
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

DROP TABLE IF EXISTS `m2epro_walmart_marketplace`;
CREATE TABLE `m2epro_walmart_marketplace` (
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `developer_key` VARCHAR(255) DEFAULT NULL,
  `default_currency` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_order`;
CREATE TABLE `m2epro_walmart_order` (
  `order_id` INT(11) UNSIGNED NOT NULL,
  `walmart_order_id` VARCHAR(255) NOT NULL,
  `status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `buyer_name` VARCHAR(255) NOT NULL,
  `buyer_email` VARCHAR(255) DEFAULT NULL,
  `shipping_service` VARCHAR(255) DEFAULT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_price` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `paid_amount` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `tax_details` TEXT DEFAULT NULL,
  `currency` VARCHAR(10) NOT NULL,
  `is_tried_to_acknowledge` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `purchase_update_date` DATETIME DEFAULT NULL,
  `purchase_create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  INDEX `walmart_order_id` (`walmart_order_id`),
  INDEX `buyer_email` (`buyer_email`),
  INDEX `buyer_name` (`buyer_name`),
  INDEX `paid_amount` (`paid_amount`),
  INDEX `is_tried_to_acknowledge` (`is_tried_to_acknowledge`),
  INDEX `purchase_create_date` (`purchase_create_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_order_item`;
CREATE TABLE `m2epro_walmart_order_item` (
  `order_item_id` INT(11) UNSIGNED NOT NULL,
  `walmart_order_item_id` VARCHAR(255) NOT NULL,
  `merged_walmart_order_item_ids` VARCHAR(500) DEFAULT NULL,
  `status` VARCHAR(30) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `qty` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_item_id`),
  INDEX `sku` (`sku`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_order_action_processing`;
CREATE TABLE `m2epro_walmart_order_action_processing` (
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

DROP TABLE IF EXISTS `m2epro_walmart_template_category`;
CREATE TABLE `m2epro_walmart_template_category` (
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

DROP TABLE IF EXISTS `m2epro_walmart_template_category_specific`;
CREATE TABLE `m2epro_walmart_template_category_specific` (
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

DROP TABLE IF EXISTS `m2epro_walmart_template_description`;
CREATE TABLE `m2epro_walmart_template_description` (
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
  `keywords_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `keywords_custom_value` VARCHAR(255) DEFAULT NULL,
  `keywords_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `attributes_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `attributes` TEXT NOT NULL,
  PRIMARY KEY (`template_description_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_template_selling_format`;
CREATE TABLE `m2epro_walmart_template_selling_format` (
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
  `map_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `map_price_custom_attribute` VARCHAR(255) NOT NULL,
  `price_coefficient` VARCHAR(255) NOT NULL,
  `price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `price_vat_percent` FLOAT UNSIGNED DEFAULT NULL,
  `promotions_mode` TINYINT(2) NOT NULL DEFAULT 0,
  `lag_time_mode` TINYINT(2) UNSIGNED NOT NULL,
  `lag_time_value` INT(11) UNSIGNED NOT NULL,
  `lag_time_custom_attribute` VARCHAR(255) NOT NULL,
  `product_tax_code_mode` TINYINT(2) UNSIGNED NOT NULL,
  `product_tax_code_custom_value` VARCHAR(255) NOT NULL,
  `product_tax_code_custom_attribute` VARCHAR(255) NOT NULL,
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

DROP TABLE IF EXISTS `m2epro_walmart_template_selling_format_promotion`;
CREATE TABLE `m2epro_walmart_template_selling_format_promotion` (
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

DROP TABLE IF EXISTS `m2epro_walmart_template_selling_format_shipping_override_service`;
CREATE TABLE `m2epro_walmart_template_selling_format_shipping_override_service` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `method` VARCHAR(255) NOT NULL,
  `region` VARCHAR(255) NOT NULL,
  `cost_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `cost_value` VARCHAR(255) NOT NULL,
  `cost_attribute` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `template_shipping_override_id` (`template_selling_format_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_walmart_template_synchronization`;
CREATE TABLE `m2epro_walmart_template_synchronization` (
  `template_synchronization_id` INT(11) UNSIGNED NOT NULL,
  `list_mode` TINYINT(2) UNSIGNED NOT NULL,
  `list_status_enabled` TINYINT(2) UNSIGNED NOT NULL,
  `list_is_in_stock` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `list_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `list_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `list_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  `revise_update_qty` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_qty_max_applied_value_mode` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_qty_max_applied_value` INT(11) UNSIGNED DEFAULT NULL,
  `revise_update_price` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_price_max_allowed_deviation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_price_max_allowed_deviation` INT(11) UNSIGNED DEFAULT NULL,
  `revise_update_promotions` TINYINT(2) UNSIGNED NOT NULL,
  `relist_mode` TINYINT(2) UNSIGNED NOT NULL,
  `relist_filter_user_lock` TINYINT(2) UNSIGNED NOT NULL,
  `relist_status_enabled` TINYINT(2) UNSIGNED NOT NULL,
  `relist_is_in_stock` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `relist_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `relist_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `relist_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  `stop_mode` TINYINT(2) UNSIGNED NOT NULL,
  `stop_status_disabled` TINYINT(2) UNSIGNED NOT NULL,
  `stop_out_off_stock` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `stop_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `stop_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `stop_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

$installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/walmart/', 'application_name', 'M2ePro - Walmart Magento Integration', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/walmart/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/walmart/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'sku_mode', '1', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'sku_custom_attribute', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'sku_modification_mode', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'sku_modification_custom_value', NULL, NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'generate_sku_mode', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'upc_mode', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'upc_custom_attribute', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'ean_mode', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'ean_custom_attribute', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'gtin_mode', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'gtin_custom_attribute', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'isbn_mode', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/configuration/', 'isbn_custom_attribute', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/other/resolve_title/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/other/resolve_title/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/other/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/other/channel/synchronize_data/', 'interval', '86400', 'in seconds',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/channel/synchronize_data/', 'interval', '86400', 'in seconds',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/channel/synchronize_data/blocked/', 'mode', '1', '0 - disable, \r\n1 - enable',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/channel/synchronize_data/blocked/', 'interval', '86400', 'in seconds',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/run_variation_parent_processors/', 'mode', '1', '0 - disable, \r\n1 - enable',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/run_variation_parent_processors/', 'interval', '60', 'in seconds',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/process_instructions/', 'mode', '1', '0 - disable, \r\n1 - enable',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/process_instructions/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/process_actions/', 'mode', '1', '0 - disable, \r\n1 - enable',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/process_actions/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/process_actions_results/', 'mode', '1', '0 - disable, \r\n1 - enable',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/process_actions_results/', 'interval', '60', 'in seconds',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/process_list_actions/', 'mode', '1', '0 - disable, \r\n1 - enable',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/walmart/listing/product/process_list_actions/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/order/receive/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/order/receive/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/order/acknowledge/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/order/acknowledge/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/order/shipping/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/order/shipping/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/order/cancel/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/order/cancel/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/order/refund/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/walmart/order/refund/', 'interval', '60', 'in seconds', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/listing/product/inspector/walmart/', 'max_allowed_instructions_count', '2000', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/listing/product/revise/total/walmart/', 'mode', '0', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/listing/product/revise/total/walmart/', 'max_allowed_instructions_count', '2000', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/scheduled_data/', 'limit', '20000', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/processing/prepare/', 'max_listings_products_count', '2000', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/list/', 'priority_coefficient', '25', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/list/', 'wait_increase_coefficient', '100', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/list/', 'min_allowed_wait_interval', '3600', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/relist/', 'priority_coefficient', '125', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/relist/', 'wait_increase_coefficient', '100', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/relist/', 'min_allowed_wait_interval', '1800', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_qty/', 'priority_coefficient', '500', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_qty/', 'wait_increase_coefficient', '100', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_qty/', 'min_allowed_wait_interval', '900', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_price/', 'priority_coefficient', '250', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_price/', 'wait_increase_coefficient', '100', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_price/', 'min_allowed_wait_interval', '1800', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_details/', 'priority_coefficient', '50', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_details/', 'wait_increase_coefficient', '100', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_details/', 'min_allowed_wait_interval', '7200', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_promotions/', 'priority_coefficient', '50', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_promotions/', 'wait_increase_coefficient', '100', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/revise_promotions/', 'min_allowed_wait_interval', '7200', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/stop/', 'priority_coefficient', '1000', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/stop/', 'wait_increase_coefficient', '100', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/stop/', 'min_allowed_wait_interval', '600', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/delete/', 'priority_coefficient', '1000', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/delete/', 'wait_increase_coefficient', '100', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/listing/product/action/delete/', 'min_allowed_wait_interval', '600', NULL,
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/walmart/order/settings/marketplace_25/', 'use_first_street_line_as_company', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO `m2epro_marketplace` VALUES
  (37, 1, 'United States', 'US', 'walmart.com', 0, 3, 'America', 'walmart',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (38, 2, 'Canada', 'CA', 'walmart.ca', 0, 4, 'America', 'walmart', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO `m2epro_walmart_marketplace` VALUES
  (37, '8636-1433-4377', 'USD'),
  (38, '7078-7205-1944', 'CAD');

SQL
);

//########################################

Mage::register('M2EPRO_IS_INSTALLATION',true);
$installer->endSetup();

//########################################