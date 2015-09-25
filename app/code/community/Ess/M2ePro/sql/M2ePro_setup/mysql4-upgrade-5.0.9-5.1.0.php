<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

$installer->run(<<<SQL

DELETE FROM `m2epro_config`
WHERE `group` = '/amazon/synchronization/settings/defaults/update_listings_products/'
AND   `key` = 'existance_mode';

DELETE FROM `m2epro_config`
WHERE `group` = '/buy/synchronization/settings/defaults/update_listings_products/'
AND   `key` = 'existance_mode';

DELETE FROM `m2epro_config`
WHERE `group` = '/synchronization/memory/';

DELETE FROM `m2epro_config`
WHERE `group` = '/synchronization/lockFile/';

UPDATE `m2epro_ebay_marketplace`
SET `is_multivariation` = 1
WHERE `marketplace_id` = 13;

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_amazon_listing_product`
    DROP COLUMN `existance_check_status`;

    ALTER TABLE `m2epro_buy_listing_product`
    DROP COLUMN `existance_check_status`,
    ADD COLUMN `ignore_next_inventory_synch` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `shipping_expedited_rate`,
    ADD INDEX `ignore_next_inventory_synch` (`ignore_next_inventory_synch`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');

if ($connection->tableColumnExists($tempTable, 'existance_check_status') === true) {
    $connection->dropColumn($tempTable, 'existance_check_status');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_listing_product');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'existance_check_status') === true) {
    $connection->dropColumn($tempTable, 'existance_check_status');
}

if ($connection->tableColumnExists($tempTable, 'ignore_next_inventory_synch') === false) {
    $connection->addColumn($tempTable, 'ignore_next_inventory_synch',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `shipping_expedited_rate`');
}

if (!isset($tempTableIndexList[strtoupper('ignore_next_inventory_synch')])) {
    $connection->addKey($tempTable, 'ignore_next_inventory_synch', 'ignore_next_inventory_synch');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/cron/lockItem/'
                               AND   `key` = 'max_deactivate_time'")
                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/cron/lockItem/', 'max_deactivate_time', '86400', 'in seconds', '2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/product/index/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
);
}

//#############################################

// PLAY SCRIPT
$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS m2epro_play_account (
  account_id INT(11) UNSIGNED NOT NULL,
  server_hash VARCHAR(255) NOT NULL,
  login VARCHAR(255) NOT NULL,
  related_store_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_synchronization TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  other_listings_first_synchronization DATETIME DEFAULT NULL,
  other_listings_mapping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_mapping_settings VARCHAR(255) DEFAULT NULL,
  other_listings_move_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_move_settings VARCHAR(255) DEFAULT NULL,
  listings_update_links_settings VARCHAR(255) DEFAULT NULL,
  orders_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_last_synchronization DATETIME DEFAULT NULL,
  magento_orders_settings TEXT NOT NULL,
  info TEXT DEFAULT NULL,
  PRIMARY KEY (account_id),
  INDEX login (login),
  INDEX other_listings_mapping_mode (other_listings_mapping_mode),
  INDEX other_listings_move_mode (other_listings_move_mode),
  INDEX other_listings_synchronization (other_listings_synchronization),
  INDEX related_store_id (related_store_id),
  INDEX server_hash (server_hash)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_item (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) NOT NULL,
  product_id INT(11) UNSIGNED NOT NULL,
  store_id INT(11) UNSIGNED NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX marketplace_id (marketplace_id),
  INDEX product_id (product_id),
  INDEX sku (sku),
  INDEX store_id (store_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_listing (
  listing_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_listing_other (
  listing_other_id INT(11) UNSIGNED NOT NULL,
  general_id VARCHAR(20) NOT NULL,
  general_id_type VARCHAR(255) NOT NULL,
  play_listing_id INT(11) UNSIGNED NOT NULL,
  link_info VARCHAR(255) DEFAULT NULL,
  sku VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  dispatch_to VARCHAR(255) NOT NULL,
  dispatch_from VARCHAR(255) NOT NULL,
  online_price_gbr DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  online_price_euro DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  online_qty INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `condition` VARCHAR(255) NOT NULL,
  condition_note TEXT NOT NULL,
  start_date DATETIME NOT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_other_id),
  INDEX `condition` (`condition`),
  INDEX dispatch_from (dispatch_from),
  INDEX dispatch_to (dispatch_to),
  INDEX end_date (end_date),
  INDEX general_id (general_id),
  INDEX general_id_type (general_id_type),
  INDEX online_price_euro (online_price_euro),
  INDEX online_price_gbr (online_price_gbr),
  INDEX online_qty (online_qty),
  INDEX play_listing_id (play_listing_id),
  INDEX sku (sku),
  INDEX start_date (start_date),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_listing_product (
  listing_product_id INT(11) UNSIGNED NOT NULL,
  general_id VARCHAR(20) DEFAULT NULL,
  general_id_type VARCHAR(255) DEFAULT NULL,
  play_listing_id INT(11) UNSIGNED DEFAULT NULL,
  link_info VARCHAR(255) DEFAULT NULL,
  general_id_search_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_search_suggest_data TEXT DEFAULT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  dispatch_to VARCHAR(255) DEFAULT NULL,
  dispatch_from VARCHAR(255) DEFAULT NULL,
  online_price_gbr DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_price_euro DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_shipping_price_gbr DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_shipping_price_euro DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  `condition` VARCHAR(255) DEFAULT NULL,
  condition_note VARCHAR(255) DEFAULT NULL,
  ignore_next_inventory_synch tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  tried_to_list TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (listing_product_id),
  INDEX `condition` (`condition`),
  INDEX dispatch_from (dispatch_from),
  INDEX dispatch_to (dispatch_to),
  INDEX end_date (end_date),
  INDEX general_id (general_id),
  INDEX general_id_search_status (general_id_search_status),
  INDEX general_id_type (general_id_type),
  INDEX ignore_next_inventory_synch (ignore_next_inventory_synch),
  INDEX online_price_euro (online_price_euro),
  INDEX online_price_gbr (online_price_gbr),
  INDEX online_qty (online_qty),
  INDEX online_shipping_price_euro (online_shipping_price_euro),
  INDEX online_shipping_price_gbr (online_shipping_price_gbr),
  INDEX play_listing_id (play_listing_id),
  INDEX sku (sku),
  INDEX start_date (start_date),
  INDEX tried_to_list (tried_to_list)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_listing_product_variation (
  listing_product_variation_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_listing_product_variation_option (
  listing_product_variation_option_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_option_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_marketplace (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (marketplace_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_order (
  order_id INT(11) UNSIGNED NOT NULL,
  play_order_id INT(11) UNSIGNED NOT NULL,
  buyer_name VARCHAR(255) NOT NULL,
  buyer_email VARCHAR(255) DEFAULT NULL,
  status TINYINT(2) NOT NULL DEFAULT 0,
  shipping_status TINYINT(2) NOT NULL DEFAULT 0,
  shipping_address TEXT NOT NULL,
  shipping_price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  paid_amount DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  currency VARCHAR(10) NOT NULL,
  purchase_create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (order_id),
  INDEX buyer_email (buyer_email),
  INDEX buyer_name (buyer_name),
  INDEX paid_amount (paid_amount),
  INDEX play_order_id (play_order_id),
  INDEX shipping_status (shipping_status),
  INDEX status (status)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_order_item (
  order_item_id INT(11) UNSIGNED NOT NULL,
  play_order_item_id INT(11) UNSIGNED NOT NULL,
  listing_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  title VARCHAR(255) DEFAULT NULL,
  price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  qty INT(11) UNSIGNED NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL,
  fee DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  exchange_rate DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  proceed DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (order_item_id),
  INDEX listing_id (listing_id),
  INDEX sku (sku),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_processed_inventory (
  hash VARCHAR(100) NOT NULL,
  sku VARCHAR(100) NOT NULL,
  INDEX hash (hash),
  INDEX sku (sku)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_template_description (
  template_description_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_description_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_template_general (
  template_general_id INT(11) UNSIGNED NOT NULL,
  sku_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  sku_custom_attribute VARCHAR(255) NOT NULL,
  general_id_mode VARCHAR(255) NOT NULL,
  general_id_custom_attribute VARCHAR(255) NOT NULL,
  search_by_magento_title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  dispatch_to_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  dispatch_to_value VARCHAR(255) NOT NULL,
  dispatch_to_custom_attribute VARCHAR(255) NOT NULL,
  dispatch_from_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  dispatch_from_value VARCHAR(255) NOT NULL,
  shipping_price_gbr_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_price_gbr_value DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_price_gbr_custom_attribute VARCHAR(255) NOT NULL,
  shipping_price_euro_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_price_euro_value DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_price_euro_custom_attribute VARCHAR(255) NOT NULL,
  condition_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_value VARCHAR(255) NOT NULL,
  condition_custom_attribute VARCHAR(255) NOT NULL,
  condition_note_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_note_value TEXT NOT NULL,
  condition_note_custom_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_general_id),
  INDEX condition_mode (condition_mode),
  INDEX condition_note_mode (condition_note_mode),
  INDEX dispatch_from_mode (dispatch_from_mode),
  INDEX dispatch_to_mode (dispatch_to_mode),
  INDEX general_id_mode (general_id_mode),
  INDEX shipping_price_euro_mode (shipping_price_euro_mode),
  INDEX shipping_price_gbr_mode (shipping_price_gbr_mode),
  INDEX sku_mode (sku_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_template_selling_format (
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  qty_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_custom_value INT(11) UNSIGNED NOT NULL,
  qty_custom_attribute VARCHAR(255) NOT NULL,
  qty_max_posted_value INT(11) UNSIGNED DEFAULT NULL,
  price_gbr_mode TINYINT(2) UNSIGNED NOT NULL,
  price_gbr_custom_attribute VARCHAR(255) NOT NULL,
  price_gbr_coefficient VARCHAR(255) NOT NULL,
  price_euro_mode TINYINT(2) UNSIGNED NOT NULL,
  price_euro_custom_attribute VARCHAR(255) NOT NULL,
  price_euro_coefficient VARCHAR(255) NOT NULL,
  customer_group_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_selling_format_id),
  INDEX customer_group_id (customer_group_id),
  INDEX price_euro_coefficient (price_euro_coefficient),
  INDEX price_euro_custom_attribute (price_euro_custom_attribute),
  INDEX price_euro_mode (price_euro_mode),
  INDEX price_gbr_coefficient (price_gbr_coefficient),
  INDEX price_gbr_custom_attribute (price_gbr_custom_attribute),
  INDEX price_gbr_mode (price_gbr_mode),
  INDEX qty_custom_attribute (qty_custom_attribute),
  INDEX qty_custom_value (qty_custom_value),
  INDEX qty_max_posted_value (qty_max_posted_value),
  INDEX qty_mode (qty_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_play_template_synchronization (
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  list_mode TINYINT(2) UNSIGNED NOT NULL,
  list_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  list_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  list_qty TINYINT(2) UNSIGNED NOT NULL,
  list_qty_value INT(11) UNSIGNED NOT NULL,
  list_qty_value_max INT(11) UNSIGNED NOT NULL,
  revise_update_qty TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value INT(11) UNSIGNED DEFAULT NULL,
  revise_update_price TINYINT(2) UNSIGNED NOT NULL,
  revise_update_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_sub_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_description TINYINT(2) UNSIGNED NOT NULL,
  relist_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_filter_user_lock TINYINT(2) UNSIGNED NOT NULL,
  relist_send_data TINYINT(2) UNSIGNED NOT NULL,
  relist_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  relist_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  relist_qty TINYINT(2) UNSIGNED NOT NULL,
  relist_qty_value INT(11) UNSIGNED NOT NULL,
  relist_qty_value_max INT(11) UNSIGNED NOT NULL,
  relist_schedule_type TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_through_metric TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_through_value INT(11) UNSIGNED NOT NULL,
  relist_schedule_week VARCHAR(255) NOT NULL,
  relist_schedule_week_start_time TIME DEFAULT NULL,
  relist_schedule_week_end_time TIME DEFAULT NULL,
  stop_status_disabled TINYINT(2) UNSIGNED NOT NULL,
  stop_out_off_stock TINYINT(2) UNSIGNED NOT NULL,
  stop_qty TINYINT(2) UNSIGNED NOT NULL,
  stop_qty_value INT(11) UNSIGNED NOT NULL,
  stop_qty_value_max INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_synchronization_id),
  INDEX list_is_in_stock (list_is_in_stock),
  INDEX list_mode (list_mode),
  INDEX list_qty (list_qty),
  INDEX list_qty_value (list_qty_value),
  INDEX list_qty_value_max (list_qty_value_max),
  INDEX list_status_enabled (list_status_enabled),
  INDEX relist_filter_user_lock (relist_filter_user_lock),
  INDEX relist_is_in_stock (relist_is_in_stock),
  INDEX relist_mode (relist_mode),
  INDEX relist_qty (relist_qty),
  INDEX relist_qty_value (relist_qty_value),
  INDEX relist_qty_value_max (relist_qty_value_max),
  INDEX relist_schedule_through_metric (relist_schedule_through_metric),
  INDEX relist_schedule_through_value (relist_schedule_through_value),
  INDEX relist_schedule_type (relist_schedule_type),
  INDEX relist_schedule_week (relist_schedule_week),
  INDEX relist_schedule_week_end_time (relist_schedule_week_end_time),
  INDEX relist_schedule_week_start_time (relist_schedule_week_start_time),
  INDEX relist_send_data (relist_send_data),
  INDEX relist_status_enabled (relist_status_enabled),
  INDEX revise_update_description (revise_update_description),
  INDEX revise_update_price (revise_update_price),
  INDEX revise_update_qty (revise_update_qty),
  INDEX revise_update_qty_max_applied_value (revise_update_qty_max_applied_value),
  INDEX revise_update_sub_title (revise_update_sub_title),
  INDEX revise_update_title (revise_update_title),
  INDEX stop_out_off_stock (stop_out_off_stock),
  INDEX stop_qty (stop_qty),
  INDEX stop_qty_value (stop_qty_value),
  INDEX stop_qty_value_max (stop_qty_value_max),
  INDEX stop_status_disabled (stop_status_disabled)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

//#############################################

$tempTable = $installer->getTable('ess_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/M2ePro/license/play/'
                               AND   `key` = 'mode'")
                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO ess_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/M2ePro/license/play/', 'mode', '0', '0 - None\r\n1 - Trial\r\n2 - Live',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/M2ePro/license/play/', 'expiration_date', NULL, NULL, '2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/M2ePro/license/play/', 'status', '0', '0 - None\r\n1 - Active\r\n2 - Suspended\r\n3 - Closed',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/M2ePro/license/play/', 'is_free', '0', NULL, '2013-05-01 01:00:00', '2013-05-01 01:00:00');

SQL
);
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/play/connector/'
                               AND   `key` = 'mode'")
                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/play/connector/', 'mode', 'server', 'server', '2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/component/play/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/component/play/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/defaults/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/defaults/update_listings_products/', 'interval', '3600', 'in seconds',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/defaults/update_listings_products/', 'mode', '1',
'0 - disable, \r\n1 - enable', '2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/defaults/update_listings_products/', 'last_time', NULL, 'Last check time',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/defaults/update_listings_products/', 'max_deactivate_time', '86400', 'in seconds',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/defaults/getting_products_links/', 'mode', '1',
'0 - disable, \r\n1 - enable', '2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/marketplaces/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/orders/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/orders/', 'max_deactivate_time', '86400', 'in seconds',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/other_listings/', 'max_deactivate_time', '86400', 'in seconds',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/other_listings/', 'last_time', NULL, 'Last check time',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/other_listings/', 'interval', '3600', 'in seconds',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/templates/end/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/templates/list/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/templates/relist/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/templates/revise/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/templates/start/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/play/synchronization/settings/templates/stop/', 'mode', '1', '0 - disable, \r\n1 - enable',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/wizard/play/', 'status', '0', '0 - Not Started, 1 - Active, 2 - Completed, 3 - Skipped',
'2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/wizard/play/', 'step', NULL, NULL, '2013-05-01 01:00:00', '2013-05-01 01:00:00'),
('/wizard/play/', 'priority', '9', '1 - highest', '2013-05-01 01:00:00', '2013-05-01 01:00:00');

SQL
);
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_marketplace');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `url` = 'play.com'")
                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_marketplace VALUES
(34, 0, 'United Kingdom', 'UK', 'play.com', 1, 1, 'Europe', 'play', '2013-05-01 01:00:00', '2013-05-01 01:00:00');

SQL
);
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_play_marketplace');
$stmt = $connection->query("SELECT count(*) FROM `{$tempTable}`");

if ($stmt->fetchColumn() == 0) {

    $installer->run(<<<SQL

INSERT INTO m2epro_play_marketplace VALUES
(34);

SQL
);
}

//#############################################

$installer->run(<<<SQL

DELETE FROM `m2epro_config`
WHERE `group` = '/cache/servicing/';

SQL
);

//#############################################

$installer->endSetup();

//#############################################