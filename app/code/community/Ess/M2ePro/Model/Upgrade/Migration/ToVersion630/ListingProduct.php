<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion630_ListingProduct
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

        ALTER TABLE `m2epro_buy_listing_product`
            CHANGE COLUMN is_variation_matched is_variation_product_matched TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
            DROP INDEX is_variation_matched,
            ADD INDEX is_variation_product_matched (is_variation_product_matched);

        ALTER TABLE `m2epro_play_listing_product`
            CHANGE COLUMN is_variation_matched is_variation_product_matched TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
            DROP INDEX is_variation_matched,
            ADD INDEX is_variation_product_matched (is_variation_product_matched);

        ALTER TABLE `m2epro_amazon_listing_product`
            DROP COLUMN worldwide_id,
            DROP COLUMN is_upc_worldwide_id,
            CHANGE COLUMN is_variation_matched is_variation_product_matched TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN is_variation_channel_matched TINYINT(2) UNSIGNED NOT NULL DEFAULT 0
                AFTER is_variation_product_matched,
            ADD COLUMN is_variation_parent TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER is_variation_channel_matched,
            ADD COLUMN variation_parent_id INT(11) UNSIGNED DEFAULT NULL AFTER is_variation_parent,
            ADD COLUMN variation_child_statuses TEXT DEFAULT NULL AFTER variation_parent_id,
            ADD COLUMN is_general_id_owner TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER is_isbn_general_id,
            DROP INDEX is_variation_matched,
            ADD INDEX is_variation_product_matched (is_variation_product_matched),
            ADD INDEX is_variation_parent (is_variation_parent),
            ADD INDEX variation_parent_id (variation_parent_id),
            ADD INDEX is_general_id_owner (is_general_id_owner),
            ADD INDEX is_variation_channel_matched (is_variation_channel_matched);

        ALTER TABLE `m2epro_amazon_listing_product`
            CHANGE `general_id_search_status` `search_settings_status` TINYINT(2) UNSIGNED DEFAULT NULL,
            CHANGE `general_id_search_suggest_data` `search_settings_data` TEXT DEFAULT NULL,
            ADD COLUMN `general_id_search_info` TEXT DEFAULT NULL AFTER `general_id`,
            DROP INDEX `general_id_search_status`,
            ADD INDEX `search_settings_status` (`search_settings_status`);

        ALTER TABLE `m2epro_buy_listing_product`
            CHANGE `general_id_search_status` `search_settings_status` TINYINT(2) UNSIGNED DEFAULT NULL,
            CHANGE `general_id_search_suggest_data` `search_settings_data` TEXT DEFAULT NULL,
            ADD COLUMN `general_id_search_info` TEXT DEFAULT NULL AFTER `general_id`,
            DROP INDEX `general_id_search_status`,
            ADD INDEX `search_settings_status` (`search_settings_status`);

        ALTER TABLE `m2epro_play_listing_product`
            CHANGE `general_id_search_status` `search_settings_status` TINYINT(2) UNSIGNED DEFAULT NULL,
            CHANGE `general_id_search_suggest_data` `search_settings_data` TEXT DEFAULT NULL,
            ADD COLUMN `general_id_search_info` TEXT DEFAULT NULL AFTER `link_info`,
            DROP INDEX `general_id_search_status`,
            ADD INDEX `search_settings_status` (`search_settings_status`);

    */

    //####################################

    public function process()
    {
        if ($this->isNeedToSkip()) {
            return;
        }

        $this->processGeneral();
        $this->processVariation();
        $this->processSearch();
    }

    //####################################

    private function isNeedToSkip()
    {
        if ($this->forceAllSteps) {
            return false;
        }

        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_amazon_listing_product');
        if ($connection->tableColumnExists($tempTable, 'is_general_id_owner') !== false) {
            return true;
        }

        return false;
    }

    //####################################

    private function processGeneral()
    {
        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_amazon_listing_product');

        if ($connection->tableColumnExists($tempTable, 'is_general_id_owner') === false) {
            $connection->addColumn(
                $tempTable, 'is_general_id_owner',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER is_isbn_general_id'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'worldwide_id') !== false) {
            $connection->dropColumn($tempTable, 'worldwide_id');
        }

        if ($connection->tableColumnExists($tempTable, 'is_upc_worldwide_id') !== false) {
            $connection->dropColumn($tempTable, 'is_upc_worldwide_id');
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (!isset($tempTableIndexList[strtoupper('is_general_id_owner')])) {
            $connection->addKey($tempTable, 'is_general_id_owner', 'is_general_id_owner');
        }
    }

    private function processVariation()
    {
        $connection = $this->installer->getConnection();

        $tempTable = $this->installer->getTable('m2epro_amazon_listing_product');

        if ($connection->tableColumnExists($tempTable, 'is_variation_matched') !== false &&
            $connection->tableColumnExists($tempTable, 'is_variation_product_matched') === false) {
            $connection->changeColumn(
                $tempTable, 'is_variation_matched', 'is_variation_product_matched',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'is_variation_channel_matched') === false) {
            $connection->addColumn(
                $tempTable, 'is_variation_channel_matched',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER is_variation_product_matched'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'is_variation_parent') === false) {
            $connection->addColumn(
                $tempTable, 'is_variation_parent',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER is_variation_channel_matched'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'variation_parent_id') === false) {
            $connection->addColumn(
                $tempTable, 'variation_parent_id',
                'INT(11) UNSIGNED DEFAULT NULL AFTER is_variation_parent'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'variation_child_statuses') === false) {
            $connection->addColumn(
                $tempTable, 'variation_child_statuses',
                'TEXT DEFAULT NULL AFTER variation_parent_id'
            );
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('is_variation_matched')])) {
            $connection->dropKey($tempTable, 'is_variation_matched');
        }

        if (!isset($tempTableIndexList[strtoupper('is_variation_product_matched')])) {
            $connection->addKey($tempTable, 'is_variation_product_matched', 'is_variation_product_matched');
        }

        if (!isset($tempTableIndexList[strtoupper('is_variation_channel_matched')])) {
            $connection->addKey($tempTable, 'is_variation_channel_matched', 'is_variation_channel_matched');
        }

        if (!isset($tempTableIndexList[strtoupper('is_variation_parent')])) {
            $connection->addKey($tempTable, 'is_variation_parent', 'is_variation_parent');
        }

        if (!isset($tempTableIndexList[strtoupper('variation_parent_id')])) {
            $connection->addKey($tempTable, 'variation_parent_id', 'variation_parent_id');
        }

        $tempTable = $this->installer->getTable('m2epro_buy_listing_product');

        if ($connection->tableColumnExists($tempTable, 'is_variation_matched') !== false &&
            $connection->tableColumnExists($tempTable, 'is_variation_product_matched') === false) {
            $connection->changeColumn(
                $tempTable, 'is_variation_matched', 'is_variation_product_matched',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0'
            );
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('is_variation_matched')])) {
            $connection->dropKey($tempTable, 'is_variation_matched');
        }

        if (!isset($tempTableIndexList[strtoupper('is_variation_product_matched')])) {
            $connection->addKey($tempTable, 'is_variation_product_matched', 'is_variation_product_matched');
        }

        $tempTable = $this->installer->getTable('m2epro_play_listing_product');

        if ($connection->tableColumnExists($tempTable, 'is_variation_matched') !== false &&
            $connection->tableColumnExists($tempTable, 'is_variation_product_matched') === false) {
            $connection->changeColumn(
                $tempTable, 'is_variation_matched', 'is_variation_product_matched',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0'
            );
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('is_variation_matched')])) {
            $connection->dropKey($tempTable, 'is_variation_matched');
        }

        if (!isset($tempTableIndexList[strtoupper('is_variation_product_matched')])) {
            $connection->addKey($tempTable, 'is_variation_product_matched', 'is_variation_product_matched');
        }

        $this->installer->run(<<<SQL

    UPDATE `m2epro_listing_product`
    SET additional_data = REPLACE(additional_data, '"variation_options":', '"variation_product_options":')
    WHERE INSTR(additional_data, '"variation_options":') > 0;

SQL
        );
    }

    private function processSearch()
    {
        $connection = $this->installer->getConnection();
        $tempTable  = $this->installer->getTable('m2epro_amazon_listing_product');

        if ($connection->tableColumnExists($tempTable, 'general_id_search_status') !== false) {

            $this->installer->run(<<<SQL

UPDATE `m2epro_amazon_listing_product`
SET general_id_search_status = 0,
    general_id_search_suggest_data = NULL;

UPDATE `m2epro_buy_listing_product`
SET general_id_search_status = 0,
    general_id_search_suggest_data = NULL;

UPDATE `m2epro_play_listing_product`
SET general_id_search_status = 0,
    general_id_search_suggest_data = NULL;
SQL
            );
        }

        if ($connection->tableColumnExists($tempTable, 'general_id_search_status') !== false &&
            $connection->tableColumnExists($tempTable, 'search_settings_status') === false) {
            $connection->changeColumn(
                $tempTable, 'general_id_search_status', 'search_settings_status',
                'TINYINT(2) UNSIGNED DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'general_id_search_suggest_data') !== false &&
            $connection->tableColumnExists($tempTable, 'search_settings_data') === false) {
            $connection->changeColumn(
                $tempTable, 'general_id_search_suggest_data', 'search_settings_data',
                'TEXT DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'general_id_search_info') === false) {
            $connection->addColumn(
                $tempTable, 'general_id_search_info',
                'TEXT DEFAULT NULL AFTER general_id'
            );
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('general_id_search_status')])) {
            $connection->dropKey($tempTable, 'general_id_search_status');
        }

        if (!isset($tempTableIndexList[strtoupper('search_settings_status')])) {
            $connection->addKey($tempTable, 'search_settings_status', 'search_settings_status');
        }

        $tempTable = $this->installer->getTable('m2epro_buy_listing_product');

        if ($connection->tableColumnExists($tempTable, 'general_id_search_status') !== false &&
            $connection->tableColumnExists($tempTable, 'search_settings_status') === false) {
            $connection->changeColumn(
                $tempTable, 'general_id_search_status', 'search_settings_status',
                'TINYINT(2) UNSIGNED DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'general_id_search_suggest_data') !== false &&
            $connection->tableColumnExists($tempTable, 'search_settings_data') === false) {
            $connection->changeColumn(
                $tempTable, 'general_id_search_suggest_data', 'search_settings_data',
                'TEXT DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'general_id_search_info') === false) {
            $connection->addColumn(
                $tempTable, 'general_id_search_info',
                'TEXT DEFAULT NULL AFTER general_id'
            );
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('general_id_search_status')])) {
            $connection->dropKey($tempTable, 'general_id_search_status');
        }

        if (!isset($tempTableIndexList[strtoupper('search_settings_status')])) {
            $connection->addKey($tempTable, 'search_settings_status', 'search_settings_status');
        }

        $tempTable = $this->installer->getTable('m2epro_play_listing_product');

        if ($connection->tableColumnExists($tempTable, 'general_id_search_status') !== false &&
            $connection->tableColumnExists($tempTable, 'search_settings_status') === false) {
            $connection->changeColumn(
                $tempTable, 'general_id_search_status', 'search_settings_status',
                'TINYINT(2) UNSIGNED DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'general_id_search_suggest_data') !== false &&
            $connection->tableColumnExists($tempTable, 'search_settings_data') === false) {
            $connection->changeColumn(
                $tempTable, 'general_id_search_suggest_data', 'search_settings_data',
                'TEXT DEFAULT NULL'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'general_id_search_info') === false) {
            $connection->addColumn(
                $tempTable, 'general_id_search_info',
                'TEXT DEFAULT NULL AFTER link_info'
            );
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (isset($tempTableIndexList[strtoupper('general_id_search_status')])) {
            $connection->dropKey($tempTable, 'general_id_search_status');
        }

        if (!isset($tempTableIndexList[strtoupper('search_settings_status')])) {
            $connection->addKey($tempTable, 'search_settings_status', 'search_settings_status');
        }
    }

    //####################################
}