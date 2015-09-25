<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS m2epro_buy_account (
  account_id INT(11) UNSIGNED NOT NULL,
  server_hash VARCHAR(255) NOT NULL,
  seller_id INT(11) UNSIGNED NOT NULL,
  web_login VARCHAR(255) NOT NULL,
  ftp_login VARCHAR(255) NOT NULL,
  ftp_new_sku_access TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  ftp_inventory_access TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  ftp_orders_access TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  related_store_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_synchronization TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  other_listings_first_synchronization DATETIME DEFAULT NULL,
  other_listings_mapping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_mapping_settings VARCHAR(255) DEFAULT NULL,
  other_listings_move_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_move_settings VARCHAR(255) DEFAULT NULL,
  other_listings_update_titles_settings VARCHAR(255) DEFAULT NULL,
  orders_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_last_synchronization DATETIME DEFAULT NULL,
  magento_orders_settings TEXT NOT NULL,
  info TEXT DEFAULT NULL,
  PRIMARY KEY (account_id),
  INDEX ftp_inventory_access (ftp_inventory_access),
  INDEX ftp_login (ftp_login),
  INDEX ftp_new_sku_access (ftp_new_sku_access),
  INDEX ftp_orders_access (ftp_orders_access),
  INDEX other_listings_mapping_mode (other_listings_mapping_mode),
  INDEX other_listings_move_mode (other_listings_move_mode),
  INDEX other_listings_synchronization (other_listings_synchronization),
  INDEX related_store_id (related_store_id),
  INDEX seller_id (seller_id),
  INDEX server_hash (server_hash),
  INDEX web_login (web_login)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_dictionary_category (
  id INT(11) UNSIGNED NOT NULL,
  category_id VARCHAR(255) DEFAULT NULL,
  parent_id INT(11) UNSIGNED DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  path VARCHAR(255) NOT NULL,
  is_listable TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  attributes LONGTEXT DEFAULT NULL,
  sorder INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  INDEX category_id (category_id),
  INDEX is_listable (is_listable),
  UNIQUE INDEX parent_id (parent_id),
  INDEX path (path),
  INDEX sorder (sorder),
  INDEX title (title)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_item (
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

CREATE TABLE IF NOT EXISTS m2epro_buy_listing (
  listing_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_listing_other (
  listing_other_id INT(11) UNSIGNED NOT NULL,
  general_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  online_price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  online_qty INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `condition` TINYINT(4) UNSIGNED NOT NULL,
  condition_note VARCHAR(255) NOT NULL,
  shipping_standard_rate DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_expedited_mode TINYINT(2) UNSIGNED NOT NULL,
  shipping_expedited_rate DECIMAL(12, 4) UNSIGNED NOT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_other_id),
  INDEX `condition` (`condition`),
  INDEX end_date (end_date),
  INDEX general_id (general_id),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX shipping_expedited_mode (shipping_expedited_mode),
  INDEX shipping_expedited_rate (shipping_expedited_rate),
  INDEX shipping_standard_rate (shipping_standard_rate),
  INDEX sku (sku),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_listing_product (
  listing_product_id INT(11) UNSIGNED NOT NULL,
  general_id INT(11) UNSIGNED DEFAULT NULL,
  general_id_search_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_search_suggest_data TEXT DEFAULT NULL,
  existance_check_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  sku VARCHAR(255) DEFAULT NULL,
  online_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  `condition` TINYINT(4) UNSIGNED DEFAULT NULL,
  condition_note VARCHAR(255) DEFAULT NULL,
  shipping_standard_rate DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  shipping_expedited_mode TINYINT(2) UNSIGNED DEFAULT NULL,
  shipping_expedited_rate DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_product_id),
  INDEX `condition` (`condition`),
  INDEX end_date (end_date),
  INDEX existance_check_status (existance_check_status),
  INDEX general_id (general_id),
  INDEX general_id_search_status (general_id_search_status),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX shipping_expedited_mode (shipping_expedited_mode),
  INDEX shipping_expedited_rate (shipping_expedited_rate),
  INDEX shipping_standard_rate (shipping_standard_rate),
  INDEX sku (sku),
  INDEX start_date (start_date)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_listing_product_variation (
  listing_product_variation_id INT(11) UNSIGNED NOT NULL,
  online_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (listing_product_variation_id),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_listing_product_variation_option (
  listing_product_variation_option_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_option_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_marketplace (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (marketplace_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_order (
  order_id INT(11) UNSIGNED NOT NULL,
  seller_id INT(11) UNSIGNED NOT NULL,
  buy_order_id INT(11) UNSIGNED NOT NULL,
  buyer_name VARCHAR(255) NOT NULL,
  buyer_email VARCHAR(255) DEFAULT NULL,
  billing_address TEXT NOT NULL,
  shipping_address TEXT NOT NULL,
  shipping_method VARCHAR(255) DEFAULT NULL,
  shipping_price DECIMAL(12, 4) UNSIGNED NOT NULL,
  paid_amount DECIMAL(12, 4) UNSIGNED NOT NULL,
  currency VARCHAR(10) NOT NULL,
  purchase_create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (order_id),
  INDEX buy_order_id (buy_order_id),
  INDEX buyer_email (buyer_email),
  INDEX buyer_name (buyer_name),
  INDEX paid_amount (paid_amount)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_order_item (
  order_item_id INT(11) UNSIGNED NOT NULL,
  buy_order_item_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  general_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  tax_amount DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  qty INT(11) UNSIGNED NOT NULL DEFAULT 0,
  qty_shipped INT(11) UNSIGNED NOT NULL DEFAULT 0,
  qty_cancelled INT(11) UNSIGNED NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL,
  product_owed DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  shipping_owed DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  commission DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  shipping_fee DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  per_item_fee DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (order_item_id),
  INDEX buy_order_item_id (buy_order_item_id),
  INDEX general_id (general_id),
  INDEX sku (sku),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_template_description (
  template_description_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_description_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_template_general (
  template_general_id INT(11) UNSIGNED NOT NULL,
  sku_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  sku_custom_attribute VARCHAR(255) NOT NULL,
  general_id_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_custom_attribute VARCHAR(255) NOT NULL,
  search_by_magento_title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  condition_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_value VARCHAR(255) NOT NULL,
  condition_custom_attribute VARCHAR(255) NOT NULL,
  condition_note_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_note_value TEXT NOT NULL,
  condition_note_custom_attribute VARCHAR(255) NOT NULL,
  shipping_standard_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_standard_value DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_standard_custom_attribute VARCHAR(255) NOT NULL,
  shipping_expedited_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_expedited_value DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_expedited_custom_attribute VARCHAR(255) NOT NULL,
  shipping_one_day_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_one_day_value DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_one_day_custom_attribute VARCHAR(255) NOT NULL,
  shipping_two_day_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_two_day_value DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_two_day_custom_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_general_id),
  INDEX condition_mode (condition_mode),
  INDEX condition_note_mode (condition_note_mode),
  INDEX general_id_mode (general_id_mode),
  INDEX shipping_expedited_mode (shipping_expedited_mode),
  INDEX shipping_one_day_mode (shipping_one_day_mode),
  INDEX shipping_standard_mode (shipping_standard_mode),
  INDEX shipping_two_day_mode (shipping_two_day_mode),
  INDEX sku_mode (sku_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_template_selling_format (
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  qty_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_custom_value INT(11) UNSIGNED NOT NULL,
  qty_custom_attribute VARCHAR(255) NOT NULL,
  qty_coefficient VARCHAR(255) NOT NULL,
  price_mode TINYINT(2) UNSIGNED NOT NULL,
  price_custom_attribute VARCHAR(255) NOT NULL,
  price_coefficient VARCHAR(255) NOT NULL,
  customer_group_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_selling_format_id),
  INDEX customer_group_id (customer_group_id),
  INDEX price_coefficient (price_coefficient),
  INDEX price_custom_attribute (price_custom_attribute),
  INDEX price_mode (price_mode),
  INDEX qty_coefficient (qty_coefficient),
  INDEX qty_custom_attribute (qty_custom_attribute),
  INDEX qty_custom_value (qty_custom_value),
  INDEX qty_mode (price_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_buy_template_synchronization (
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  start_auto_list TINYINT(2) UNSIGNED NOT NULL,
  end_auto_stop TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty TINYINT(2) UNSIGNED NOT NULL,
  revise_update_price TINYINT(2) UNSIGNED NOT NULL,
  relist_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_list_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_filter_user_lock TINYINT(2) UNSIGNED NOT NULL,
  relist_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  relist_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  relist_qty TINYINT(2) UNSIGNED NOT NULL,
  relist_qty_value INT(11) UNSIGNED NOT NULL,
  relist_qty_value_max TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_type TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_through_metric TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_through_value INT(11) UNSIGNED NOT NULL,
  relist_schedule_week VARCHAR(255) NOT NULL,
  relist_schedule_week_start_time TIME DEFAULT NULL,
  relist_schedule_week_end_time TIME DEFAULT NULL,
  stop_status_disabled TINYINT(2) UNSIGNED NOT NULL,
  stop_out_off_stock TINYINT(2) NOT NULL,
  stop_qty TINYINT(2) UNSIGNED NOT NULL,
  stop_qty_value INT(11) UNSIGNED NOT NULL,
  stop_qty_value_max INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_synchronization_id),
  INDEX end_auto_stop (end_auto_stop),
  INDEX relist_filter_user_lock (relist_filter_user_lock),
  INDEX relist_is_in_stock (relist_is_in_stock),
  INDEX relist_list_mode (relist_list_mode),
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
  INDEX relist_status_enabled (relist_status_enabled),
  INDEX revise_update_price (revise_update_price),
  INDEX revise_update_qty (revise_update_qty),
  INDEX start_auto_list (start_auto_list),
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

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS m2epro_order_change (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  component VARCHAR(10) NOT NULL,
  order_id INT(11) UNSIGNED NOT NULL,
  `action` VARCHAR(50) NOT NULL,
  params LONGTEXT NOT NULL,
  creator_type TINYINT(2) NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `action` (`action`),
  INDEX creator_type (creator_type),
  INDEX order_id (order_id)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

//#############################################

$tempTable = $installer->getTable('ess_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/M2ePro/license/buy/'
                               AND   `key` = 'mode'")
                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO ess_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/M2ePro/license/buy/', 'mode', '0', '0 - None\r\n1 - Trial\r\n2 - Live',
 '2012-12-05 16:20:54', '2012-05-21 10:47:49'),
('/M2ePro/license/buy/', 'expiration_date', NULL, NULL, '2012-12-05 16:20:54', '2012-05-21 10:47:49'),
('/M2ePro/license/buy/', 'status', '0', '0 - None\r\n1 - Active\r\n2 - Suspended\r\n3 - Closed',
 '2012-12-05 16:20:54', '2012-05-21 10:47:49'),
('/M2ePro/license/buy/', 'is_free', '0', NULL, '2012-12-05 16:20:55', '2012-08-20 10:33:47');

SQL
);
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `group` = '/buy/connector/'
                               AND   `key` = 'mode'")
                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/buy/connector/', 'mode', 'server', 'server', '2012-04-12 14:21:48', '2012-04-12 14:21:48'),
('/component/buy/', 'mode', '1', '0 - disable, \r\n1 - enable', '2012-12-03 14:49:22', '2012-05-21 10:47:49'),
('/component/buy/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2012-08-10 11:42:44', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/defaults/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/defaults/update_listings_products/', 'interval', '3600', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/defaults/update_listings_products/', 'mode', '1',
 '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/defaults/update_listings_products/', 'last_time', NULL, 'Last check time',
 '2012-12-05 12:52:01', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/defaults/update_listings_products/', 'max_deactivate_time', '86400', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/defaults/update_listings_products/', 'existance_mode', '1',
 '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/marketplaces/', 'mode', '0', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/marketplaces/categories/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/orders/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-12-05 12:51:55', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/orders/', 'max_deactivate_time', '86400', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/orders/receive/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-12-05 12:51:55', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/orders/receive/', 'max_deactivate_time', '86400', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/orders/receive/', 'interval', '900', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/orders/update/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-12-05 12:51:55', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/orders/update/', 'max_deactivate_time', '86400', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/orders/update/', 'interval', '3600', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-12-05 12:51:55', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/other_listings/', 'max_deactivate_time', '86400', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/other_listings/', 'last_time', NULL, 'Last check time',
 '2012-12-05 12:52:06', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/other_listings/', 'interval', '3600', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-12-05 12:51:55', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/templates/end/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/templates/relist/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/templates/revise/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/templates/start/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/buy/synchronization/settings/templates/stop/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/wizard/buy/', 'status', '0', '0 - Not Started, 1 - Active, 2 - Completed, 3 - Skipped',
 '2012-10-15 18:00:15', '2012-10-03 10:47:49'),
('/wizard/buy/', 'step', NULL, NULL, '2012-10-15 18:00:15', '2012-10-03 10:47:49'),
('/wizard/buy/', 'priority', '6', '1 - highest', '2012-10-03 10:47:49', '2012-10-03 10:47:49');

SQL
);
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_marketplace');
$tempRow = $connection->query("SELECT * FROM `{$tempTable}`
                               WHERE `url` = 'rakuten.com'")
                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_marketplace VALUES
(33, 0, 'United States', 'US', 'rakuten.com', 1, 1, 'America', 'buy', '2012-05-17 09:26:38', '2012-05-17 09:26:38');

SQL
);
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_marketplace');
$stmt = $connection->query("SELECT count(*) FROM `{$tempTable}`");

if ($stmt->fetchColumn() == 0) {

    $installer->run(<<<SQL

INSERT INTO m2epro_buy_marketplace VALUES
(33);

SQL
);
}

//#############################################

$installer->run(<<<SQL

DELETE FROM `m2epro_config`
WHERE `group` = '/cache/license/status/';

SQL
);

//#############################################

$installer->endSetup();

//#############################################