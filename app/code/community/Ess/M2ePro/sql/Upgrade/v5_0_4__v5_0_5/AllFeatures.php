<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v5_0_4__v5_0_5_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `{$this->_installer->getTable('m2epro_exceptions_filters')}` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  preg_match TEXT NOT NULL,
  type TINYINT(2) UNSIGNED NOT NULL,
  create_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX title (title),
  INDEX type (type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `{$this->_installer->getTable('m2epro_order_repair')}` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT(11) UNSIGNED NOT NULL,
  input_data TEXT DEFAULT NULL,
  output_data TEXT DEFAULT NULL,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `hash` VARCHAR(50) DEFAULT NULL,
  component VARCHAR(10) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component (component),
  INDEX hash (hash),
  INDEX product_id (product_id),
  INDEX `type` (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

UPDATE `{$this->_installer->getTable('m2epro_config')}`
SET `value` = 'http://docs.m2epro.com/display/eBayAmazonRakutenMagentoV5/'
WHERE `group` = '/documentation/'
AND `key` = 'baseurl';

UPDATE `{$this->_installer->getTable('m2epro_config')}`
SET `value` = '240'
WHERE `group` = '/cron/task/processing/'
AND `key` = 'interval';

DELETE FROM `{$this->_installer->getTable('m2epro_config')}`
WHERE `group` = '/support/form/' AND
     (`key` = 'defect_mail' OR
      `key` = 'feature_mail' OR
      `key` = 'inquiry_mail');

SQL
        );

        $tempTable = $installer->getTable('m2epro_amazon_template_synchronization');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'revise_update_qty_max_applied_value') === false) {
            $connection->addColumn($tempTable, 'revise_update_qty_max_applied_value',
                                   'INT(11) UNSIGNED DEFAULT NULL AFTER `revise_update_qty`');
        }

        if (!isset($tempTableIndexList[strtoupper('revise_update_qty_max_applied_value')])) {
            $connection->addKey(
                $tempTable, 'revise_update_qty_max_applied_value', 'revise_update_qty_max_applied_value'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_buy_template_synchronization');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'revise_update_qty_max_applied_value') === false) {
            $connection->addColumn($tempTable, 'revise_update_qty_max_applied_value',
                                   'INT(11) UNSIGNED DEFAULT NULL AFTER `revise_update_qty`');
        }

        if (!isset($tempTableIndexList[strtoupper('revise_update_qty_max_applied_value')])) {
            $connection->addKey(
                $tempTable, 'revise_update_qty_max_applied_value', 'revise_update_qty_max_applied_value'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_template_synchronization');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'revise_update_qty_max_applied_value') === false) {
            $connection->addColumn($tempTable, 'revise_update_qty_max_applied_value',
                                   'INT(11) UNSIGNED DEFAULT NULL AFTER `revise_update_qty`');
        }

        if (!isset($tempTableIndexList[strtoupper('revise_update_qty_max_applied_value')])) {
            $connection->addKey(
                $tempTable, 'revise_update_qty_max_applied_value', 'revise_update_qty_max_applied_value'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_template_selling_format');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'qty_max_posted_value') === false) {
            $connection->addColumn($tempTable, 'qty_max_posted_value',
                                   'INT(11) UNSIGNED DEFAULT NULL AFTER `qty_custom_attribute`');
        }

        if (!isset($tempTableIndexList[strtoupper('qty_max_posted_value')])) {
            $connection->addKey($tempTable, 'qty_max_posted_value', 'qty_max_posted_value');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_buy_template_selling_format');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'qty_max_posted_value') === false) {
            $connection->addColumn($tempTable, 'qty_max_posted_value',
                                   'INT(11) UNSIGNED DEFAULT NULL AFTER `qty_custom_attribute`');
        }

        if (!isset($tempTableIndexList[strtoupper('qty_max_posted_value')])) {
            $connection->addKey($tempTable, 'qty_max_posted_value', 'qty_max_posted_value');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_template_selling_format');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'qty_max_posted_value') === false) {
            $connection->addColumn($tempTable, 'qty_max_posted_value',
                                   'INT(11) UNSIGNED DEFAULT NULL AFTER `qty_custom_attribute`');
        }

        if (!isset($tempTableIndexList[strtoupper('qty_max_posted_value')])) {
            $connection->addKey($tempTable, 'qty_max_posted_value', 'qty_max_posted_value');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_order');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'state') === false) {
            $connection->addColumn($tempTable, 'state',
                                   'TINYINT(2) UNSIGNED DEFAULT 0 AFTER `store_id`');
        }
        if ($connection->tableColumnExists($tempTable, 'reservation_state') === false) {
            $connection->addColumn($tempTable, 'reservation_state',
                                   'TINYINT(2) UNSIGNED DEFAULT 0 AFTER `state`');
        }
        if ($connection->tableColumnExists($tempTable, 'reservation_start_date') === false) {
            $connection->addColumn($tempTable, 'reservation_start_date',
                                   'DATETIME DEFAULT NULL AFTER `reservation_state`');
        }
        if (!isset($tempTableIndexList[strtoupper('state')])) {
            $connection->addKey($tempTable, 'state', 'state');
        }
        if (!isset($tempTableIndexList[strtoupper('reservation_state')])) {
            $connection->addKey($tempTable, 'reservation_state', 'reservation_state');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_order_item');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'product_details') === false) {
            $connection->addColumn($tempTable, 'product_details',
                                   'TEXT DEFAULT NULL AFTER `product_id`');
        }
        if ($connection->tableColumnExists($tempTable, 'state') === false) {
            $connection->addColumn($tempTable, 'state',
                                   'TINYINT(2) UNSIGNED DEFAULT 0 AFTER `product_details`');
        }
        if (!isset($tempTableIndexList[strtoupper('state')])) {
            $connection->addKey($tempTable, 'state', 'state');
        }

        //########################################

        $tempTable = $installer->getTable('m2epro_config');
        $tempRow = $connection->query("
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/ebay/synchronization/settings/orders/reserve_cancellation/'
    AND   `key` = 'interval'
")->fetch();

        if ($tempRow === false) {

            $installer->run(<<<SQL

INSERT INTO `{$this->_installer->getTable('m2epro_config')}` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/ebay/synchronization/settings/orders/reserve_cancellation/', 'mode', '1', 'in seconds',
 '2013-02-28 00:00:00', '2013-02-28 00:00:00'),
('/ebay/synchronization/settings/orders/reserve_cancellation/', 'interval', '3600', 'in seconds',
 '2013-02-28 00:00:00', '2013-02-28 00:00:00'),
('/ebay/synchronization/settings/orders/reserve_cancellation/', 'last_access', NULL, 'Last check time',
 '2013-02-28 00:00:00', '2013-02-28 00:00:00'),
('/amazon/synchronization/settings/orders/reserve_cancellation/', 'mode', '1', 'in seconds',
 '2013-02-28 00:00:00', '2013-02-28 00:00:00'),
('/amazon/synchronization/settings/orders/reserve_cancellation/', 'interval', '3600', 'in seconds',
 '2013-02-28 00:00:00', '2013-02-28 00:00:00'),
('/amazon/synchronization/settings/orders/reserve_cancellation/', 'last_access', NULL, 'Last check time',
 '2013-02-28 00:00:00', '2013-02-28 00:00:00'),
('/synchronization/lockFile/', 'mode', '0', '0 - disable, \r\n1 - enable',
 '2013-04-01 00:00:00', '2013-04-01 00:00:00'),
('/logs/cleaning/orders/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2013-04-02 00:00:00', '2013-04-02 00:00:00'),
('/logs/cleaning/orders/', 'days', '90', 'in days', '2013-04-02 00:00:00',
 '2013-04-02 00:00:00'),
('/logs/cleaning/orders/', 'default', '90', 'in days', '2013-04-02 00:00:00',
 '2013-04-02 00:00:00'),
('/debug/exceptions/', 'filters_mode', '0', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/support/form/', 'mail', 'support@m2epro.com', 'Support email address',
 '2013-02-28 00:00:00', '2013-02-28 00:00:00');

SQL
            );
        }
    }

    //########################################
}