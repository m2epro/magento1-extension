<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_AutoAction
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
        CREATE TABLE m2epro_listing_auto_category (
            id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            group_id int(11) UNSIGNED NOT NULL,
            category_id int(11) UNSIGNED NOT NULL,
            update_date datetime DEFAULT NULL,
            create_date datetime DEFAULT NULL,
            PRIMARY KEY (id),
            INDEX category_id (category_id),
            INDEX group_id (group_id)
        )
        ENGINE = INNODB
        CHARACTER SET utf8
        COLLATE utf8_general_ci;

        CREATE TABLE m2epro_listing_auto_category_group (
            id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            listing_id int(11) UNSIGNED NOT NULL,
            title varchar(255) NOT NULL,
            adding_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
            deleting_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
            component_mode varchar(10) DEFAULT NULL,
            update_date datetime DEFAULT NULL,
            create_date datetime DEFAULT NULL,
            PRIMARY KEY (id),
            INDEX listing_id (listing_id),
            INDEX title (title),
            INDEX component_mode (component_mode)
        )
        ENGINE = INNODB
        CHARACTER SET utf8
        COLLATE utf8_general_ci;

        CREATE TABLE m2epro_temp_ebay_listing_auto_category_group (
            listing_auto_category_group_id int(11) UNSIGNED NOT NULL,
            adding_template_category_id int(11) UNSIGNED DEFAULT NULL,
            adding_template_other_category_id int(11) UNSIGNED DEFAULT NULL,
            PRIMARY KEY (listing_auto_category_group_id),
            INDEX adding_template_category_id (adding_template_category_id),
            INDEX adding_template_other_category_id (adding_template_other_category_id)
        )
        ENGINE = INNODB
        CHARACTER SET utf8
        COLLATE utf8_general_ci;

        CREATE TABLE m2epro_amazon_listing_auto_category_group (
            listing_auto_category_group_id int(11) UNSIGNED NOT NULL,
            adding_description_template_id int(11) UNSIGNED DEFAULT NULL,
            PRIMARY KEY (listing_auto_category_group_id),
            INDEX adding_description_template_id (adding_description_template_id)
        )
        ENGINE = INNODB
        CHARACTER SET utf8
        COLLATE utf8_general_ci;

        CREATE TABLE m2epro_buy_listing_auto_category_group (
            listing_auto_category_group_id int(11) UNSIGNED NOT NULL,
            PRIMARY KEY (listing_auto_category_group_id)
        )
        ENGINE = INNODB
        CHARACTER SET utf8
        COLLATE utf8_general_ci;

        CREATE TABLE m2epro_play_listing_auto_category_group (
            listing_auto_category_group_id int(11) UNSIGNED NOT NULL,
            PRIMARY KEY (listing_auto_category_group_id)
        )
        ENGINE = INNODB
        CHARACTER SET utf8
        COLLATE utf8_general_ci;

        ALTER TABLE m2epro_listing
            ADD COLUMN auto_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 0 after component_mode,
            ADD COLUMN auto_global_adding_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 0 after auto_mode,
            ADD COLUMN auto_website_adding_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 0 after auto_global_adding_mode,
            ADD COLUMN auto_website_deleting_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 0 after auto_website_adding_mode,
            ADD INDEX auto_mode (auto_mode),
            ADD INDEX auto_global_adding_mode (auto_global_adding_mode),
            ADD INDEX auto_website_adding_mode (auto_website_adding_mode),
            ADD INDEX auto_website_deleting_mode (auto_website_deleting_mode);

        ALTER TABLE m2epro_amazon_listing
            ADD COLUMN auto_global_adding_description_template_id int(11) UNSIGNED DEFAULT NULL after listing_id,
            ADD COLUMN auto_website_adding_description_template_id int(11) UNSIGNED DEFAULT NULL
                after auto_global_adding_description_template_id,
            ADD INDEX auto_global_adding_description_template_id (auto_global_adding_description_template_id),
            ADD INDEX auto_website_adding_description_template_id (auto_website_adding_description_template_id);

        DROP TABLE m2epro_listing_category;
        DROP TABLE m2epro_ebay_listing_auto_category;
        DROP TABLE m2epro_ebay_listing_auto_category_group;

        ALTER TABLE m2epro_temp_ebay_listing_auto_category_group RENAME m2epro_ebay_listing_auto_category_group;

        ALTER TABLE m2epro_ebay_listing
            DROP COLUMN auto_mode,
            DROP COLUMN auto_global_adding_mode,
            DROP COLUMN auto_website_adding_mode,
            DROP COLUMN auto_website_deleting_mode,
            DROP KEY auto_mode,
            DROP KEY auto_global_adding_mode,
            DROP KEY auto_website_adding_mode,
            DROP KEY auto_website_deleting_mode;

        ALTER TABLE m2epro_listing
            DROP COLUMN categories_add_action,
            DROP COLUMN categories_delete_action;
    */

    //####################################

    public function process()
    {
        if ($this->isNeedToSkip()) {
            return;
        }

        $this->prepareStructure();
        $this->migrateData();
        $this->deleteOldData();
    }

    //####################################

    private function isNeedToSkip()
    {
        if ($this->forceAllSteps) {
            return false;
        }

        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_listing');
        if ($connection->tableColumnExists($tempTable, 'categories_delete_action') === false) {
            return true;
        }

        return false;
    }

    //####################################

    private function prepareStructure()
    {
        $tempTable = $this->installer->getTable('m2epro_temp_ebay_listing_auto_category_group');

        $this->installer->run(<<<SQL

    DROP TABLE IF EXISTS `m2epro_listing_auto_category`;
    CREATE TABLE `m2epro_listing_auto_category` (
    id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id int(11) UNSIGNED NOT NULL,
    category_id int(11) UNSIGNED NOT NULL,
    update_date datetime DEFAULT NULL,
    create_date datetime DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX category_id (category_id),
    INDEX group_id (group_id)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    DROP TABLE IF EXISTS `m2epro_listing_auto_category_group`;
    CREATE TABLE `m2epro_listing_auto_category_group` (
        id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        listing_id int(11) UNSIGNED NOT NULL,
        title varchar(255) NOT NULL,
        adding_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
        deleting_mode tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
        component_mode varchar(10) DEFAULT NULL,
        update_date datetime DEFAULT NULL,
        create_date datetime DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX listing_id (listing_id),
        INDEX title (title),
        INDEX component_mode (component_mode)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    DROP TABLE IF EXISTS `{$tempTable}`;
    CREATE TABLE `{$tempTable}` (
        listing_auto_category_group_id int(11) UNSIGNED NOT NULL,
        adding_template_category_id int(11) UNSIGNED DEFAULT NULL,
        adding_template_other_category_id int(11) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (listing_auto_category_group_id),
        INDEX adding_template_category_id (adding_template_category_id),
        INDEX adding_template_other_category_id (adding_template_other_category_id)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    DROP TABLE IF EXISTS `m2epro_amazon_listing_auto_category_group`;
    CREATE TABLE `m2epro_amazon_listing_auto_category_group` (
        listing_auto_category_group_id int(11) UNSIGNED NOT NULL,
        adding_description_template_id int(11) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (listing_auto_category_group_id),
        INDEX adding_description_template_id (adding_description_template_id)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    DROP TABLE IF EXISTS `m2epro_buy_listing_auto_category_group`;
    CREATE TABLE `m2epro_buy_listing_auto_category_group` (
        listing_auto_category_group_id int(11) UNSIGNED NOT NULL,
        PRIMARY KEY (listing_auto_category_group_id)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    DROP TABLE IF EXISTS `m2epro_play_listing_auto_category_group`;
    CREATE TABLE `m2epro_play_listing_auto_category_group` (
        listing_auto_category_group_id int(11) UNSIGNED NOT NULL,
        PRIMARY KEY (listing_auto_category_group_id)
    )
    ENGINE = INNODB
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

SQL
        );

        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_listing');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'auto_mode') === false) {
            $connection->addColumn(
                $tempTable, 'auto_mode',
                'tinyint(2) UNSIGNED NOT NULL DEFAULT 0 after component_mode'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'auto_global_adding_mode') === false) {
            $connection->addColumn(
                $tempTable, 'auto_global_adding_mode',
                'tinyint(2) UNSIGNED NOT NULL DEFAULT 0 after auto_mode'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'auto_website_adding_mode') === false) {
            $connection->addColumn(
                $tempTable, 'auto_website_adding_mode',
                'tinyint(2) UNSIGNED NOT NULL DEFAULT 0 after auto_global_adding_mode'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'auto_website_deleting_mode') === false) {
            $connection->addColumn(
                $tempTable, 'auto_website_deleting_mode',
                'tinyint(2) UNSIGNED NOT NULL DEFAULT 0 after auto_website_adding_mode'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('auto_mode')])) {
            $connection->addKey($tempTable, 'auto_mode', 'auto_mode');
        }

        if (!isset($tempTableIndexList[strtoupper('auto_global_adding_mode')])) {
            $connection->addKey($tempTable, 'auto_global_adding_mode', 'auto_global_adding_mode');
        }

        if (!isset($tempTableIndexList[strtoupper('auto_website_adding_mode')])) {
            $connection->addKey($tempTable, 'auto_website_adding_mode', 'auto_website_adding_mode');
        }

        if (!isset($tempTableIndexList[strtoupper('auto_website_deleting_mode')])) {
            $connection->addKey($tempTable, 'auto_website_deleting_mode', 'auto_website_deleting_mode');
        }

        $tempTable = $this->installer->getTable('m2epro_amazon_listing');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'auto_global_adding_description_template_id') === false) {
            $connection->addColumn(
                $tempTable, 'auto_global_adding_description_template_id',
                'int(11) UNSIGNED DEFAULT NULL after listing_id'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'auto_website_adding_description_template_id') === false) {
            $connection->addColumn(
                $tempTable, 'auto_website_adding_description_template_id',
                'int(11) UNSIGNED DEFAULT NULL after auto_global_adding_description_template_id'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('auto_global_adding_description_template_id')])) {
            $connection->addKey($tempTable,
                'auto_global_adding_description_template_id', 'auto_global_adding_description_template_id');
        }

        if (!isset($tempTableIndexList[strtoupper('auto_website_adding_description_template_id')])) {
            $connection->addKey($tempTable,
                'auto_website_adding_description_template_id', 'auto_website_adding_description_template_id');
        }

    }

    //####################################

    private function migrateData()
    {
        $connection = $this->installer->getConnection();
        $tempTable = $this->installer->getTable('m2epro_ebay_listing');

        if ($connection->tableColumnExists($tempTable, 'auto_mode') === false) {
            return;
        }

        $tempTable = $this->installer->getTable('m2epro_temp_ebay_listing_auto_category_group');

        $this->installer->run(<<<SQL

    UPDATE `m2epro_listing` ml
        JOIN `m2epro_ebay_listing` mel ON (ml.id = mel.listing_id)
    SET ml.auto_mode = mel.auto_mode,
        ml.auto_global_adding_mode = mel.auto_global_adding_mode,
        ml.auto_website_adding_mode = mel.auto_website_adding_mode,
        ml.auto_website_deleting_mode = mel.auto_website_deleting_mode;

    INSERT INTO `m2epro_listing_auto_category_group` (id, listing_id, title, adding_mode, deleting_mode)
    SELECT DISTINCT melacg.id, melacg.listing_id, melacg.title, melac.adding_mode, melac.deleting_mode
    FROM `m2epro_ebay_listing_auto_category_group` melacg
        JOIN `m2epro_ebay_listing_auto_category` melac ON (melacg.id = melac.group_id);

    UPDATE `m2epro_listing_auto_category_group` mlacg
    SET mlacg.component_mode = "ebay",
        mlacg.update_date = NOW(),
        mlacg.create_date = NOW();

    INSERT INTO `{$tempTable}` (listing_auto_category_group_id,
                                adding_template_category_id,
                                adding_template_other_category_id)
    SELECT DISTINCT group_id, adding_template_category_id, adding_template_other_category_id
    FROM `m2epro_ebay_listing_auto_category`;

    INSERT INTO `m2epro_listing_auto_category` (group_id, category_id, update_date, create_date)
    SELECT group_id, category_id, update_date, create_date
    FROM `m2epro_ebay_listing_auto_category`;

    INSERT INTO `m2epro_listing_auto_category_group` (listing_id, adding_mode, deleting_mode, component_mode)
    SELECT DISTINCT mlc.listing_id, ml.categories_add_action, ml.categories_delete_action, ml.component_mode
    FROM `m2epro_listing_category` mlc
        JOIN `m2epro_listing` ml ON (ml.id = mlc.listing_id);

    UPDATE `m2epro_listing_auto_category_group` mlacg
    SET mlacg.title = "Automatic Action Rule",
        mlacg.update_date = NOW(),
        mlacg.create_date = NOW()
    WHERE mlacg.title = "";

    INSERT INTO `m2epro_amazon_listing_auto_category_group` (listing_auto_category_group_id)
    SELECT mlacg.id
    FROM `m2epro_listing_auto_category_group` mlacg
    WHERE mlacg.component_mode = 'amazon';

    UPDATE `m2epro_listing` ml
        JOIN `m2epro_listing_auto_category_group` mlacg ON (ml.id = mlacg.listing_id)
    SET ml.auto_mode = 3;

    INSERT INTO `m2epro_buy_listing_auto_category_group` (listing_auto_category_group_id)
    SELECT mlacg.id
    FROM `m2epro_listing_auto_category_group` mlacg
    WHERE mlacg.component_mode = 'buy';

    INSERT INTO `m2epro_play_listing_auto_category_group` (listing_auto_category_group_id)
    SELECT mlacg.id
    FROM `m2epro_listing_auto_category_group` mlacg
    WHERE mlacg.component_mode = 'play';

    INSERT INTO `m2epro_listing_auto_category` (group_id, category_id, update_date, create_date)
    SELECT mlacg.id, mlc.category_id, mlc.update_date, mlc.create_date
    FROM `m2epro_listing_category` mlc
        JOIN `m2epro_listing` ml ON (ml.id = mlc.listing_id)
        JOIN `m2epro_listing_auto_category_group` mlacg ON (mlc.listing_id = mlacg.listing_id);
SQL
        );
    }

    //####################################

    private function deleteOldData()
    {
        $connection = $this->installer->getConnection();

        $this->installer->run(<<<SQL

    DROP TABLE IF EXISTS `m2epro_listing_category`;
    DROP TABLE IF EXISTS `m2epro_ebay_listing_auto_category`;
    DROP TABLE IF EXISTS `m2epro_ebay_listing_auto_category_group`;

SQL
        );

        $oldTable = $this->installer->getTable('m2epro_temp_ebay_listing_auto_category_group');
        $newTable = $this->installer->getTable('m2epro_ebay_listing_auto_category_group');

        if ($this->installer->tableExists($oldTable) && !$this->installer->tableExists($newTable)) {
            $connection->query("RENAME TABLE `{$oldTable}` TO `{$newTable}`");
        }

        $tempTable = $this->installer->getTable('m2epro_ebay_listing');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('auto_mode')])) {
            $connection->dropKey($tempTable, 'auto_mode');
        }

        if (isset($tempTableIndexList[strtoupper('auto_global_adding_mode')])) {
            $connection->dropKey($tempTable, 'auto_global_adding_mode');
        }

        if (isset($tempTableIndexList[strtoupper('auto_website_adding_mode')])) {
            $connection->dropKey($tempTable, 'auto_website_adding_mode');
        }

        if (isset($tempTableIndexList[strtoupper('auto_website_deleting_mode')])) {
            $connection->dropKey($tempTable, 'auto_website_deleting_mode');
        }

        if ($connection->tableColumnExists($tempTable, 'auto_mode') !== false) {
            $connection->dropColumn($tempTable, 'auto_mode');
        }

        if ($connection->tableColumnExists($tempTable, 'auto_global_adding_mode') !== false) {
            $connection->dropColumn($tempTable, 'auto_global_adding_mode');
        }

        if ($connection->tableColumnExists($tempTable, 'auto_website_adding_mode') !== false) {
            $connection->dropColumn($tempTable, 'auto_website_adding_mode');
        }

        if ($connection->tableColumnExists($tempTable, 'auto_website_deleting_mode') !== false) {
            $connection->dropColumn($tempTable, 'auto_website_deleting_mode');
        }

        $tempTable = $this->installer->getTable('m2epro_listing');

        if ($connection->tableColumnExists($tempTable, 'categories_add_action') !== false) {
            $connection->dropColumn($tempTable, 'categories_add_action');
        }

        if ($connection->tableColumnExists($tempTable, 'categories_delete_action') !== false) {
            $connection->dropColumn($tempTable, 'categories_delete_action');
        }
    }

    //####################################
}