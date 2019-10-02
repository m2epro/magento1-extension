<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_0_8__v6_0_9_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_ebay_template_selling_format');

        if ($connection->tableColumnExists($tempTable, 'price_discount_stp_mode') === false) {
            $connection->addColumn(
                $tempTable,
                'price_discount_stp_mode',
                'TINYINT(2) UNSIGNED NOT NULL AFTER `buyitnow_price_custom_attribute`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'price_discount_stp_attribute') === false) {
            $connection->addColumn(
                $tempTable,
                'price_discount_stp_attribute',
                'VARCHAR(255) NOT NULL AFTER `price_discount_stp_mode`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'price_discount_stp_type') === false) {
            $connection->addColumn(
                $tempTable,
                'price_discount_stp_type',
                'TINYINT(2) UNSIGNED NOT NULL AFTER `price_discount_stp_attribute`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'out_of_stock_control') === false) {
            $connection->addColumn(
                $tempTable,
                'out_of_stock_control',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `duration_attribute`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'price_increase_vat_percent') === false) {
            $connection->addColumn(
                $tempTable,
                'price_increase_vat_percent',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `qty_max_posted_value`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_marketplace');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'is_stp') === false) {
            $connection->addColumn(
                $tempTable,
                'is_stp',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_vat`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'is_stp_advanced') === false) {
            $connection->addColumn(
                $tempTable,
                'is_stp_advanced',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_stp`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'is_multi_currency') === false) {
            $connection->addColumn(
                $tempTable,
                'is_multi_currency',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `currency`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'is_charity') === false) {
            $connection->addColumn(
                $tempTable,
                'is_charity',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_global_shipping_program`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'is_tax') !== false &&
            $connection->tableColumnExists($tempTable, 'is_tax_table') === false) {
            $connection->changeColumn($tempTable,'is_tax','is_tax_table', 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0');
        }

        if (isset($tempTableIndexList[strtoupper('is_tax')])) {
            $connection->dropKey($tempTable, 'is_tax');
        }

        $tempTableIndexList = $connection->getIndexList($tempTable);

        if (!isset($tempTableIndexList[strtoupper('is_stp')])) {
            $connection->addKey($tempTable, 'is_stp', 'is_stp');
        }

        if (!isset($tempTableIndexList[strtoupper('is_stp_advanced')])) {
            $connection->addKey($tempTable, 'is_stp_advanced', 'is_stp_advanced');
        }

        if (!isset($tempTableIndexList[strtoupper('is_tax_table')])) {
            $connection->addKey($tempTable, 'is_tax_table', 'is_tax_table');
        }

        if (!isset($tempTableIndexList[strtoupper('is_multi_currency')])) {
            $connection->addKey($tempTable, 'is_multi_currency', 'is_multi_currency');
        }

        if (!isset($tempTableIndexList[strtoupper('is_charity')])) {
            $connection->addKey($tempTable, 'is_charity', 'is_charity');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_item');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'account_id') === false) {
            $connection->addColumn(
                $tempTable,
                'account_id',
                'INT(11) UNSIGNED NOT NULL AFTER `id`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'marketplace_id') === false) {
            $connection->addColumn(
                $tempTable,
                'marketplace_id',
                'INT(11) UNSIGNED NOT NULL AFTER `account_id`'
            );
        }

        if (!isset($tempTableIndexList[strtoupper('account_id')])) {
            $connection->addKey($tempTable, 'account_id', 'account_id');
        }

        if (!isset($tempTableIndexList[strtoupper('marketplace_id')])) {
            $connection->addKey($tempTable, 'marketplace_id', 'marketplace_id');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_template_category');

        if ($connection->tableColumnExists($tempTable, 'variation_enabled') !== false) {
            $connection->dropColumn(
                $tempTable,
                'variation_enabled'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_listing_product');

        if ($connection->tableColumnExists($tempTable, 'is_m2epro_listed_item') !== false) {
            $connection->dropColumn(
                $tempTable,
                'is_m2epro_listed_item'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_template_synchronization');

        if ($connection->tableColumnExists($tempTable, 'revise_update_gallery') !== false &&
            $connection->tableColumnExists($tempTable, 'revise_update_images') === false) {
            $connection->changeColumn(
                $tempTable,'revise_update_gallery','revise_update_images','TINYINT(2) UNSIGNED NOT NULL'
            );
        }

        // ---------------------------------------

        $orderItemTable = $installer->getTable('m2epro_order_item');

        if ($connection->tableColumnExists($orderItemTable, 'qty_reserved') === false) {
            $connection->addColumn(
                $orderItemTable,
                'qty_reserved',
                'INT(11) UNSIGNED DEFAULT 0 AFTER `component_mode`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_amazon_listing_product');

        if ($connection->tableColumnExists($tempTable, 'online_sale_price_start_date') === false) {
            $connection->addColumn(
                $tempTable,
                'online_sale_price_start_date',
                'DATETIME DEFAULT NULL AFTER `online_sale_price`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'online_sale_price_end_date') === false) {
            $connection->addColumn(
                $tempTable,
                'online_sale_price_end_date',
                'DATETIME DEFAULT NULL AFTER `online_sale_price_start_date`'
            );
        }

        //########################################

        $installer->run(<<<SQL

UPDATE `m2epro_order_item` AS `moi`
RIGHT JOIN `m2epro_ebay_order_item` AS `meoi` ON `meoi`.`order_item_id` = `moi`.`id`
LEFT JOIN `m2epro_order` AS `mo` ON `mo`.`id` = `moi`.`order_id`
SET `moi`.`qty_reserved` = `meoi`.`qty_purchased`
WHERE `mo`.`reservation_state` = 1;

UPDATE `m2epro_order_item` AS `moi`
RIGHT JOIN `m2epro_amazon_order_item` AS `maoi` ON `maoi`.`order_item_id` = `moi`.`id`
LEFT JOIN `m2epro_order` AS `mo` ON `mo`.`id` = `moi`.`order_id`
SET `moi`.`qty_reserved` = `maoi`.`qty_purchased`
WHERE `mo`.`reservation_state` = 1;

UPDATE `m2epro_order_item` AS `moi`
RIGHT JOIN `m2epro_buy_order_item` AS `mboi` ON `mboi`.`order_item_id` = `moi`.`id`
LEFT JOIN `m2epro_order` AS `mo` ON `mo`.`id` = `moi`.`order_id`
SET `moi`.`qty_reserved` = `mboi`.`qty`
WHERE `mo`.`reservation_state` = 1;

UPDATE `m2epro_order_item` AS `moi`
RIGHT JOIN `m2epro_play_order_item` AS `mpoi` ON `mpoi`.`order_item_id` = `moi`.`id`
LEFT JOIN `m2epro_order` AS `mo` ON `mo`.`id` = `moi`.`order_id`
SET `moi`.`qty_reserved` = `mpoi`.`qty`
WHERE `mo`.`reservation_state` = 1;

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayMagentoV6/M2E+Pro+-+User+Guide+(eBay)/'
WHERE `group` = '/view/ebay/support/'
AND   `key` = 'documentation_url';

UPDATE `m2epro_config`
SET `group` = '/view/ebay/template/selling_format/'
WHERE `group` = '/view/ebay/template/shipping/'
AND   `key` = 'show_tax_category';

UPDATE `m2epro_ebay_marketplace`
SET `is_global_shipping_program` = 1
WHERE `marketplace_id` = '9';

UPDATE `m2epro_ebay_marketplace`
SET `is_multi_currency` = 1
WHERE `marketplace_id` IN (2, 19);

UPDATE `m2epro_ebay_marketplace`
SET `is_charity` = 1
WHERE `marketplace_id` IN (1, 3, 9);

UPDATE `m2epro_ebay_marketplace`
SET `is_stp` = 1
WHERE `marketplace_id` IN (1, 3, 8);

UPDATE `m2epro_ebay_marketplace`
SET `is_stp_advanced` = 1
WHERE `marketplace_id` IN (3, 8);

SQL
        );

        //########################################

        $tempTable = $installer->getTable('m2epro_config');
        $tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/buy/connector/list/'
    AND   `key` = 'check_sku_existence'
SQL;
        $tempRow = $connection->query($tempQuery)->fetch();

        if ($tempRow === false) {

            $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/buy/connector/list/', 'check_sku_existence', '1', '0 - disable\r\n1 - enable',
 '2013-09-18 00:00:00', '2013-09-18 00:00:00');

SQL
            );
        }

        //########################################

        $ebayListingProductTable = $installer->getTable('m2epro_ebay_listing_product');
        $listingProductTable = $installer->getTable('m2epro_listing_product');
        $listingTable = $installer->getTable('m2epro_listing');
        $ebayItemTable = $installer->getTable('m2epro_ebay_item');

        $ebayListingOtherTable = $installer->getTable('m2epro_ebay_listing_other');
        $listingOtherTable = $installer->getTable('m2epro_listing_other');

        $queryStmt = $connection->query(<<<SQL

SELECT `{$listingTable}`.`account_id`,
       `{$listingTable}`.`marketplace_id`,
       `{$ebayItemTable}`.`id` AS `ebay_item_id`
FROM `{$ebayListingProductTable}`
LEFT JOIN `{$listingProductTable}`
    ON `{$ebayListingProductTable}`.`listing_product_id` = `{$listingProductTable}`.`id`
LEFT JOIN `{$listingTable}`
    ON `{$listingProductTable}`.`listing_id` = `{$listingTable}`.`id`
LEFT JOIN `{$ebayItemTable}`
    ON `{$ebayListingProductTable}`.`ebay_item_id` = `{$ebayItemTable}`.`id`
HAVING `account_id` IS NOT NULL AND `marketplace_id` IS NOT NULL AND `ebay_item_id` IS NOT NULL
    UNION
SELECT `{$listingOtherTable}`.`account_id`,
       `{$listingOtherTable}`.`marketplace_id`,
       `{$ebayItemTable}`.`id` AS `ebay_item_id`
FROM `{$ebayListingOtherTable}`
LEFT JOIN `{$listingOtherTable}`
    ON `{$ebayListingOtherTable}`.`listing_other_id` = `{$listingOtherTable}`.`id`
LEFT JOIN `{$ebayItemTable}`
    ON `{$ebayListingOtherTable}`.`item_id` = `{$ebayItemTable}`.`item_id`
HAVING `account_id` IS NOT NULL AND `marketplace_id` IS NOT NULL AND `ebay_item_id` IS NOT NULL

SQL
        );

        $preparedData = array();
        while ($row = $queryStmt->fetch()) {

            if (empty($row['ebay_item_id'])) {
                continue;
            }

            $preparedData[$row['account_id'].'#'.$row['marketplace_id']][] = $row['ebay_item_id'];
        }

        foreach ($preparedData as $dataKey => $ebayItemIds) {

            foreach (array_chunk($ebayItemIds, 1000) as $partOfEbayItemIds) {

                if (count($partOfEbayItemIds) <= 0) {
                    continue;
                }

                $updateInfo = explode('#', $dataKey);
                list($accountId, $marketplaceId) = $updateInfo;

                $sqlInFilter = implode(',', $partOfEbayItemIds);

                $connection->update(
                    $ebayItemTable,
                    array('account_id' => $accountId, 'marketplace_id' => $marketplaceId),
                    "`id` IN ({$sqlInFilter})"
                );
            }
        }

        //########################################

        $installer->run(<<<SQL

UPDATE `m2epro_processing_request`
SET `responser_model` = REPLACE(
  REPLACE(
    `responser_model`,
    '_ServerResponser',
    '_Responser'
  ),
  'Connector_Server_',
  'Connector_'
);

DELETE FROM `m2epro_locked_object`
WHERE `related_hash` IN (
    SELECT `hash` FROM `m2epro_processing_request`
    WHERE `responser_model` LIKE '%Amazon_Search_%'
       OR `responser_model` LIKE '%Buy_Search_%'
       OR `responser_model` LIKE '%Play_Search_%'
);

DELETE FROM `m2epro_processing_request`
WHERE `responser_model` LIKE '%Amazon_Search_%'
   OR `responser_model` LIKE '%Buy_Search_%'
   OR `responser_model` LIKE '%Play_Search_%';

SQL
        );

        $lockItemTable = $installer->getTable('m2epro_lock_item');

        foreach ($connection->select()->from($lockItemTable,'*')->query() as $row) {

            if (preg_match('/^synchronization_/',$row['nick']) && strpos($row['nick'],'ebay') === false) {

                $nick = preg_replace('/(_\d*)_\d*$/','$1',$row['nick']);
                $connection->update($lockItemTable,array('nick' => $nick),"id={$row['id']}");

            } elseif (strpos($row['nick'], 'list_skus_queue') !== false) {

                $nick = preg_replace('/_\d*(_\d*)$/','$1',$row['nick']);
                $connection->update($lockItemTable,array('nick' => $nick),"id={$row['id']}");

            }
        }

        //########################################

        $tempTable = $installer->getTable('m2epro_ebay_template_selling_format');

        if ($connection->tableColumnExists($tempTable, 'vat_percent') === false) {
            $connection->addColumn(
                $tempTable, 'vat_percent',
                'FLOAT UNSIGNED NOT NULL DEFAULT 0 AFTER `qty_max_posted_value`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'tax_table_mode') === false) {
            $connection->addColumn(
                $tempTable, 'tax_table_mode',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `vat_percent`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'tax_category_mode') === false) {
            $connection->addColumn(
                $tempTable, 'tax_category_mode',
                'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `tax_table_mode`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'tax_category_value') === false) {
            $connection->addColumn(
                $tempTable, 'tax_category_value',
                'VARCHAR(255) NOT NULL AFTER `tax_category_mode`'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'tax_category_attribute') === false) {
            $connection->addColumn(
                $tempTable, 'tax_category_attribute',
                'VARCHAR(255) NOT NULL AFTER `tax_category_value`'
            );
        }

        // ---------------------------------------

        include __DIR__ .'/'. 'MigrationTo609.php';

        $migrationTool = new Ess_M2ePro_Migration_Tool_To_Version_6_0_9($installer);
        $migrationTool->process();

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_template_shipping');

        if ($connection->tableColumnExists($tempTable, 'tax_table_mode') !== false) {
            $connection->dropColumn($tempTable, 'tax_table_mode');
        }

        if ($connection->tableColumnExists($tempTable, 'vat_percent') !== false) {
            $connection->dropColumn($tempTable, 'vat_percent');
        }

        if ($connection->tableColumnExists($tempTable, 'tax_category_mode') !== false) {
            $connection->dropColumn($tempTable, 'tax_category_mode');
        }

        if ($connection->tableColumnExists($tempTable, 'tax_category_value') !== false) {
            $connection->dropColumn($tempTable, 'tax_category_value');
        }

        if ($connection->tableColumnExists($tempTable, 'tax_category_attribute') !== false) {
            $connection->dropColumn($tempTable, 'tax_category_attribute');
        }
    }

    //########################################
}