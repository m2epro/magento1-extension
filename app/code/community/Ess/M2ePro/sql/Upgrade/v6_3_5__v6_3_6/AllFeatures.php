<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_3_5__v6_3_6_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_system_log');

        if (!$installer->tableExists($tempTable)) {

            $installer->run(<<<SQL

    CREATE TABLE `{$this->_installer->getTable('m2epro_system_log')}` (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `type` VARCHAR(255) DEFAULT NULL,
        description TEXT DEFAULT NULL,
        additional_data LONGTEXT DEFAULT NULL,
        update_date DATETIME DEFAULT NULL,
        create_date DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX `type` (`type`)
    )
    ENGINE = MYISAM
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_listing_log');
        $tempBackupTable = $installer->getTable('m2epro_backup_v636_listing_log');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'creator') || isset($tempTableIndexList[strtoupper('creator')])
        ) {
            $this->_installer->getTablesObject()->renameTable(
                'm2epro_listing_log',
                $tempBackupTable
            );

            $installer->run(<<<SQL
    CREATE TABLE `{$this->_installer->getTable('m2epro_listing_log')}` (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        listing_id INT(11) UNSIGNED DEFAULT NULL,
        product_id INT(11) UNSIGNED DEFAULT NULL,
        listing_product_id INT(11) UNSIGNED DEFAULT NULL,
        parent_listing_product_id int(11) UNSIGNED DEFAULT NULL,
        listing_title VARCHAR(255) DEFAULT NULL,
        product_title VARCHAR(255) DEFAULT NULL,
        action_id INT(11) UNSIGNED DEFAULT NULL,
        action TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
        initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
        priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
        description TEXT DEFAULT NULL,
        component_mode VARCHAR(10) DEFAULT NULL,
        additional_data LONGTEXT DEFAULT NULL,
        update_date DATETIME DEFAULT NULL,
        create_date DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX action (action),
        INDEX action_id (action_id),
        INDEX component_mode (component_mode),
        INDEX initiator (initiator),
        INDEX listing_id (listing_id),
        INDEX listing_product_id (listing_product_id),
        INDEX parent_listing_product_id (parent_listing_product_id),
        INDEX listing_title (listing_title),
        INDEX priority (priority),
        INDEX product_id (product_id),
        INDEX product_title (product_title),
        INDEX type (type)
    )
    ENGINE = MYISAM
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    INSERT INTO `{$this->_installer->getTable('m2epro_listing_log')}`
    SELECT
        `id`,
        `listing_id`,
        `product_id`,
        `listing_product_id`,
        `parent_listing_product_id`,
        `listing_title`,
        `product_title`,
        `action_id`,
        `action`,
        `initiator`,
        `type`,
        `priority`,
        `description`,
        `component_mode`,
        `additional_data`,
        `update_date`,
        `create_date`
    FROM `{$tempBackupTable}` old
    ORDER BY `old`.`id` DESC
    LIMIT 100000;

    DROP TABLE `{$tempBackupTable}`;

SQL
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_listing_other_log');
        $tempBackupTable = $installer->getTable('m2epro_backup_v636_listing_other_log');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (!$connection->tableColumnExists($tempTable, 'additional_data') ||
            $connection->tableColumnExists($tempTable, 'creator') ||
            isset($tempTableIndexList[strtoupper('creator')])
        ) {
            $this->_installer->getTablesObject()->renameTable(
                'm2epro_listing_other_log',
                $tempBackupTable
            );

            $installer->run(<<<SQL
    CREATE TABLE `{$this->_installer->getTable('m2epro_listing_other_log')}` (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        listing_other_id INT(11) UNSIGNED DEFAULT NULL,
        identifier VARCHAR(32) DEFAULT NULL,
        title VARCHAR(255) DEFAULT NULL,
        action_id INT(11) UNSIGNED DEFAULT NULL,
        `action` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
        initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
        priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
        description TEXT DEFAULT NULL,
        component_mode VARCHAR(10) DEFAULT NULL,
        additional_data LONGTEXT DEFAULT NULL,
        update_date DATETIME DEFAULT NULL,
        create_date DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX action (action),
        INDEX action_id (action_id),
        INDEX component_mode (component_mode),
        INDEX initiator (initiator),
        INDEX identifier (identifier),
        INDEX listing_other_id (listing_other_id),
        INDEX priority (priority),
        INDEX title (title),
        INDEX type (type)
    )
    ENGINE = MYISAM
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    INSERT INTO `{$this->_installer->getTable('m2epro_listing_other_log')}`
    SELECT
        `id`,
        `listing_other_id`,
        `identifier`,
        `title`,
        `action_id`,
        `action`,
        `initiator`,
        `type`,
        `priority`,
        `description`,
        `component_mode`,
        NULL,
        `update_date`,
        `create_date`
    FROM `{$tempBackupTable}` old
    ORDER BY `old`.`id` DESC
    LIMIT 100000;

    DROP TABLE `{$tempBackupTable}`;

SQL
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_synchronization_log');
        $tempBackupTable = $installer->getTable('m2epro_backup_v636_synchronization_log');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (!$connection->tableColumnExists($tempTable, 'additional_data') ||
            $connection->tableColumnExists($tempTable, 'creator') ||
            isset($tempTableIndexList[strtoupper('creator')])
        ) {
            $this->_installer->getTablesObject()->renameTable(
                'm2epro_synchronization_log',
                $tempBackupTable
            );

            $installer->run(<<<SQL
    CREATE TABLE `{$this->_installer->getTable('m2epro_synchronization_log')}` (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        operation_history_id INT(11) UNSIGNED DEFAULT NULL,
        task TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
        priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
        description TEXT DEFAULT NULL,
        component_mode VARCHAR(10) DEFAULT NULL,
        additional_data LONGTEXT DEFAULT NULL,
        update_date DATETIME DEFAULT NULL,
        create_date DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX component_mode (component_mode),
        INDEX initiator (initiator),
        INDEX priority (priority),
        INDEX task (task),
        INDEX operation_history_id (operation_history_id),
        INDEX type (type)
    )
    ENGINE = MYISAM
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    INSERT INTO `{$this->_installer->getTable('m2epro_synchronization_log')}`
    SELECT
        `id`,
        `operation_history_id`,
        `task`,
        `initiator`,
        `type`,
        `priority`,
        `description`,
        `component_mode`,
        NULL,
        `update_date`,
        `create_date`
    FROM `{$tempBackupTable}` old
    ORDER BY `old`.`id` DESC
    LIMIT 100000;

    DROP TABLE `{$tempBackupTable}`;

SQL
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_order_log');
        $tempBackupTable = $installer->getTable('m2epro_backup_v636_order_log');

        if (!$connection->tableColumnExists($tempTable, 'additional_data')) {
            $this->_installer->getTablesObject()->renameTable(
                'm2epro_order_log',
                $tempBackupTable
            );

            $installer->run(<<<SQL
    CREATE TABLE `{$this->_installer->getTable('m2epro_order_log')}` (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id INT(11) UNSIGNED DEFAULT NULL,
        type TINYINT(2) UNSIGNED NOT NULL DEFAULT 2,
        initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        message TEXT NOT NULL,
        component_mode VARCHAR(10) DEFAULT NULL,
        additional_data LONGTEXT DEFAULT NULL,
        create_date DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX component_mode (component_mode),
        INDEX initiator (initiator),
        INDEX order_id (order_id),
        INDEX type (type)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    INSERT INTO `{$this->_installer->getTable('m2epro_order_log')}`
    SELECT
        `id`,
        `order_id`,
        `type`,
        `initiator`,
        `message`,
        `component_mode`,
        NULL,
        `create_date`
    FROM `{$tempBackupTable}` old
    ORDER BY `old`.`id` DESC
    LIMIT 100000;

    DROP TABLE `{$tempBackupTable}`;

SQL
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_dictionary_shipping_override');

        if (!$installer->tableExists($tempTable)) {

            $installer->run(<<<SQL

    CREATE TABLE `{$this->_installer->getTable('m2epro_amazon_dictionary_shipping_override')}` (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        marketplace_id INT(11) UNSIGNED NOT NULL,
        `service` VARCHAR(255) NOT NULL,
        `location` VARCHAR(255) NOT NULL,
        `option` VARCHAR(255) NOT NULL,
        PRIMARY KEY (id),
        INDEX marketplace_id (marketplace_id)
    )
    ENGINE = MYISAM
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_template_shipping_override');

        if (!$installer->tableExists($tempTable)) {

            $installer->run(<<<SQL

    CREATE TABLE `{$this->_installer->getTable('m2epro_amazon_template_shipping_override')}` (
        id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        marketplace_id INT(11) UNSIGNED NOT NULL,
        update_date datetime DEFAULT NULL,
        create_date datetime DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX title (title),
        INDEX marketplace_id (marketplace_id)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_template_shipping_override_service');

        if (!$installer->tableExists($tempTable)) {

            $installer->run(<<<SQL

    CREATE TABLE `{$this->_installer->getTable('m2epro_amazon_template_shipping_override_service')}` (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        template_shipping_override_id INT(11) UNSIGNED NOT NULL,
        `service` VARCHAR(255) NOT NULL,
        `location` VARCHAR(255) NOT NULL,
        `option` VARCHAR(255) NOT NULL,
        `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        cost_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        cost_value VARCHAR(255) NOT NULL,
        PRIMARY KEY (id),
        INDEX template_shipping_override_id (template_shipping_override_id)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_listing_product');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (!$connection->tableColumnExists($tempTable, 'template_shipping_override_id')) {
            $connection->addColumn(
                $tempTable, 'template_shipping_override_id',
                'INT(11) UNSIGNED DEFAULT NULL AFTER `template_description_id`'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('template_shipping_override_id')])) {
            $connection->addKey($tempTable, 'template_shipping_override_id', 'template_shipping_override_id');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_template_synchronization');

        if (!$connection->tableColumnExists($tempTable, 'revise_change_shipping_override_template')) {

            $connection->addColumn(
                $tempTable, 'revise_change_shipping_override_template',
                'TINYINT(2) UNSIGNED NOT NULL AFTER `revise_change_description_template`'
            );

            $installer->run(<<<SQL

    UPDATE `{$this->_installer->getTable('m2epro_amazon_template_synchronization')}`
    SET `revise_change_shipping_override_template` = 1;

SQL
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_template_description');

        if (!$connection->tableColumnExists($tempTable, 'variation_images_mode')) {
            $connection->addColumn(
                $tempTable, 'variation_images_mode',
                'tinyint(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `gallery_images_attribute`'
            );
        }

        if (!$connection->tableColumnExists($tempTable, 'variation_images_limit')) {
            $connection->addColumn(
                $tempTable, 'variation_images_limit',
                'tinyint(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `variation_images_mode`'
            );
        }

        if (!$connection->tableColumnExists($tempTable, 'variation_images_attribute')) {
            $connection->addColumn(
                $tempTable, 'variation_images_attribute',
                'varchar(255) NOT NULL AFTER `variation_images_limit`'
            );
        }

        //########################################

        $tempTable = $installer->getTable('m2epro_config');

        $tempRow = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE `group` = '/ebay/description/'
    AND `key` = 'upload_images_mode'
")->fetch();

        if ($tempRow === false) {

            $installer->run(<<<SQL

        INSERT INTO `{$this->_installer->getTable('m2epro_config')}` 
        (`group`,`key`,`value`,`notice`,`update_date`,`create_date`)
        VALUES ('/debug/logging/', 'send_to_server', 1, '0 - disable,\r\n1 - enable',
                '2015-08-12 00:00:00', '2015-08-12 00:00:00'),
               ('/ebay/description/', 'upload_images_mode', 2, NULL, '2015-08-21 00:00:00', '2015-08-21 00:00:00');
SQL
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_wizard');

        $tempRow = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE `nick` = 'amazonShippingOverridePolicy'
")->fetch();

        if ($tempRow === false) {

            $installer->run(<<<SQL

        INSERT INTO `{$this->_installer->getTable('m2epro_wizard')}` 
        (`nick`, `view`, `status`, `step`, `type`, `priority`)
        VALUES ('amazonShippingOverridePolicy', 'common', 0, NULL, 1, 9);
SQL
            );
        }

        // ---------------------------------------

        $installer->run(<<<SQL

    UPDATE `{$this->_installer->getTable('m2epro_ebay_marketplace')}`
    SET `is_international_shipping_rate_table` = 1
    WHERE `marketplace_id` = 1;

    UPDATE `{$this->_installer->getTable('m2epro_ebay_marketplace')}`
    SET `is_holiday_return` = 1
    WHERE `marketplace_id` = 19;

    UPDATE `{$this->_installer->getTable('m2epro_ebay_marketplace')}`
    SET `is_freight_shipping` = 1
    WHERE `marketplace_id` = 19;

    UPDATE `{$this->_installer->getTable('m2epro_synchronization_config')}`
    SET `value` = 1
    WHERE (`group` = '/ebay/other_listings/' AND `key` = 'mode')
    OR    (`group` = '/amazon/other_listings/' AND `key` = 'mode')
    OR    (`group` = '/buy/other_listings/' AND `key` = 'mode')
    OR    (`group` = '/ebay/other_listings/update/' AND `key` = 'mode')
    OR    (`group` = '/amazon/other_listings/update/' AND `key` = 'mode')
    OR    (`group` = '/buy/other_listings/update/' AND `key` = 'mode');

    UPDATE `{$this->_installer->getTable('m2epro_wizard')}` as `mw`
    SET `mw`.`status` = 3
    WHERE `mw`.`nick` = 'amazonShippingOverridePolicy'
    AND (
        (SELECT `mc`.`value`
         FROM `{$this->_installer->getTable('m2epro_config')}` as `mc`
         WHERE `mc`.`value` IS NOT NULL
         AND `mc`.`group` = '/component/amazon/'
         AND `mc`.`key` = 'mode'
         LIMIT 1) < 1

         OR

        (SELECT `mc`.`value`
         FROM `{$this->_installer->getTable('m2epro_config')}` as `mc`
         WHERE `mc`.`value` IS NOT NULL
         AND `mc`.`group` = '/component/amazon/'
         AND `mc`.`key` = 'allowed'
         LIMIT 1) < 1

         OR

         (SELECT COUNT(`mm`.`id`) FROM `{$this->_installer->getTable('m2epro_marketplace')}` as `mm`
          WHERE `mm`.`component_mode` = 'amazon'
          AND `mm`.`status` = 1) = 0
    );

SQL
        );
    }

    //########################################
}