<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_General
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    private $forceAllSteps = false;

    //####################################

    public function getInstaller()
    {
        return $this->installer;
    }

    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    // -----------------------------------

    public function setForceAllSteps($value = true)
    {
        $this->forceAllSteps = $value;
    }

    //####################################

    /*

        DROP TABLE `m2epro_attribute_set`;

        ALTER TABLE m2epro_listing_log
            ADD COLUMN additional_data TEXT DEFAULT NULL AFTER product_title,
            ADD COLUMN parent_listing_product_id int(11) UNSIGNED DEFAULT NULL AFTER listing_product_id,
            ADD INDEX parent_listing_product_id (parent_listing_product_id);

        ALTER TABLE m2epro_amazon_template_synchronization
            ADD COLUMN revise_update_details tinyint(2) UNSIGNED NOT NULL AFTER revise_update_price,
            ADD COLUMN revise_update_images tinyint(2) UNSIGNED NOT NULL AFTER revise_update_details,
            ADD COLUMN revise_change_description_template tinyint(2) UNSIGNED NOT NULL AFTER revise_update_images;

    */

    //####################################

    public function process()
    {
        if ($this->isNeedToSkip()) {
            return;
        }

        $this->processAttributeSet();
        $this->processRegistry();
        $this->processLog();
        $this->processAmazonTemplates();
        $this->processWizard();
    }

    //####################################

    private function isNeedToSkip()
    {
        if ($this->forceAllSteps) {
            return false;
        }

        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_amazon_template_synchronization');
        if ($connection->tableColumnExists($tempTable, 'revise_change_description_template') !== false) {
            return true;
        }

        return false;
    }

    //####################################

    private function processAttributeSet()
    {
        $this->getInstaller()->run("DROP TABLE IF EXISTS m2epro_attribute_set;");
    }

    private function processRegistry()
    {
        $this->getInstaller()->run(<<<SQL

    DROP TABLE IF EXISTS m2epro_registry;
    CREATE TABLE m2epro_registry (
      id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `key` VARCHAR(255) NOT NULL,
      value TEXT DEFAULT NULL,
      update_date DATETIME DEFAULT NULL,
      create_date DATETIME DEFAULT NULL,
      PRIMARY KEY (id),
      INDEX `key` (`key`)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
        );
    }

    private function processLog()
    {
        $connection = $this->installer->getConnection();

        $tempNewTable    = $this->installer->getTable('m2epro_listing_log');
        $tempBackupTable = $this->installer->getTable('m2epro_backup_v630_listing_log');

        if ($connection->tableColumnExists($tempNewTable, 'additional_data') !== false &&
            $connection->tableColumnExists($tempNewTable, 'parent_listing_product_id') !== false) {

            return;
        }

        $this->getInstaller()->run(<<<SQL
RENAME TABLE m2epro_listing_log TO {$tempBackupTable};

CREATE TABLE m2epro_listing_log (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    listing_id INT(11) UNSIGNED DEFAULT NULL,
    product_id INT(11) UNSIGNED DEFAULT NULL,
    listing_product_id INT(11) UNSIGNED DEFAULT NULL,
    parent_listing_product_id int(11) UNSIGNED DEFAULT NULL,
    listing_title VARCHAR(255) DEFAULT NULL,
    product_title VARCHAR(255) DEFAULT NULL,
    additional_data TEXT DEFAULT NULL,
    action_id INT(11) UNSIGNED DEFAULT NULL,
    action TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
    initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
    creator VARCHAR(255) DEFAULT NULL,
    type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
    priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
    description TEXT DEFAULT NULL,
    component_mode VARCHAR(10) DEFAULT NULL,
    update_date DATETIME DEFAULT NULL,
    create_date DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX action (action),
    INDEX action_id (action_id),
    INDEX component_mode (component_mode),
    INDEX creator (creator),
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

INSERT INTO `m2epro_listing_log`
SELECT
    `id`,
    `listing_id`,
    `product_id`,
    `listing_product_id`,
    NULL,
    `listing_title`,
    `product_title`,
    NULL,
    `action_id`,
    `action`,
    `initiator`,
    `creator`,
    `type`,
    `priority`,
    `description`,
    `component_mode`,
    `update_date`,
    `create_date`
FROM {$tempBackupTable} old
ORDER BY `old`.`id` DESC
LIMIT 100000;

DROP TABLE {$tempBackupTable};

SQL
        );
    }

    private function processAmazonTemplates()
    {
        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_amazon_template_synchronization');

        if ($connection->tableColumnExists($tempTable, 'relist_send_data') === false) {
            $connection->addColumn(
                $tempTable, 'relist_send_data',
                'TINYINT(2) UNSIGNED NOT NULL after relist_filter_user_lock'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'revise_update_details') === false) {
            $connection->addColumn(
                $tempTable, 'revise_update_details',
                'tinyint(2) UNSIGNED NOT NULL AFTER revise_update_price'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'revise_update_images') === false) {
            $connection->addColumn(
                $tempTable, 'revise_update_images',
                'tinyint(2) UNSIGNED NOT NULL AFTER revise_update_details'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'revise_change_description_template') === false) {
            $connection->addColumn(
                $tempTable, 'revise_change_description_template',
                'tinyint(2) UNSIGNED NOT NULL AFTER revise_update_images'
            );
        }

        $this->getInstaller()->run(<<<SQL

    UPDATE `m2epro_amazon_template_selling_format`
    SET `sale_price_mode` = 0
    WHERE `sale_price_mode` = 4;

SQL
        );
    }

    private function processWizard()
    {
        $tempTable = $this->installer->getTable('m2epro_wizard');
        $tempQuery = "SELECT * FROM `{$tempTable}` WHERE `nick` = 'migrationNewAmazon'";

        $tempRow = $this->installer->getConnection()
                                   ->query($tempQuery)
                                   ->fetch();

        if ($tempRow !== false) {
            return;
        }

        $this->getInstaller()->run(<<<SQL

    INSERT INTO `m2epro_wizard` (`nick`, `view`, `status`, `step`, `type`, `priority`)
    VALUES ('migrationNewAmazon', 'common', 0, NULL, 0, 6);

SQL
        );
    }

    //####################################
}