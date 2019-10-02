<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Install_General extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->run(
            <<<SQL

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

DROP TABLE IF EXISTS `m2epro_order_note`;
CREATE TABLE `m2epro_order_note` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL,
  `note` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id` (`order_id`)
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

CREATE TABLE `m2epro_setup` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version_from` VARCHAR(32) DEFAULT NULL,
  `version_to` VARCHAR(32) DEFAULT NULL,
  `is_backuped` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_completed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `profiler_data` LONGTEXT DEFAULT  NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `version_from` (`version_from`),
  INDEX `version_to` (`version_to`),
  INDEX `is_backuped` (`is_backuped`),
  INDEX `is_completed` (`is_completed`)
)
ENGINE = INNODB
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

        $tempMagentoConnectUrl  = 'https://www.magentocommerce.com/magento-connect/';
        $tempMagentoConnectUrl .= 'ebay-amazon-rakuten-magento-integration-order-import-and-stock-level';
        $tempMagentoConnectUrl .= '-synchronization.html';

        $servicingInterval = rand(43200, 86400);
        $randomHash = $this->_installer->generateRandomHash();

        $this->_installer->run(
            <<<SQL

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
  ('/M2ePro/server/', 'installation_key', '{$randomHash}', 'Unique identifier of M2E instance',
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
    }

    //########################################
}
