<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_template_shipping`
    CHANGE COLUMN `international_trade` `cross_border_trade` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
    CHANGE COLUMN `dispatch_time_value` `dispatch_time` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    CHANGE COLUMN `local_shipping_cash_on_delivery_cost_value` `cash_on_delivery_cost` VARCHAR(255) DEFAULT NULL,
    CHANGE COLUMN `local_shipping_combined_discount_profile_id` `local_shipping_discount_profile_id` TEXT DEFAULT NULL,
    CHANGE COLUMN `international_shipping_combined_discount_profile_id`
                  `international_shipping_discount_profile_id` TEXT DEFAULT NULL,
    DROP INDEX `international_trade`;

    ALTER TABLE `m2epro_ebay_template_selling_format`
    ADD COLUMN `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100 AFTER `qty_custom_attribute`;

    ALTER TABLE `m2epro_amazon_template_selling_format`
    ADD COLUMN `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100 AFTER `qty_custom_attribute`;

    ALTER TABLE `m2epro_buy_template_selling_format`
    ADD COLUMN `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100 AFTER `qty_custom_attribute`;

    ALTER TABLE `m2epro_play_template_selling_format`
    ADD COLUMN `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100 AFTER `qty_custom_attribute`;

    ALTER TABLE `m2epro_order`
    DROP COLUMN `state`;

    ALTER TABLE `m2epro_order_item`
    DROP COLUMN `state`;

    ALTER TABLE m2epro_amazon_order
    DROP COLUMN `tax_amount`,
    DROP COLUMN `discount_amount`,
    ADD COLUMN `tax_details` text DEFAULT NULL AFTER `paid_amount`,
    ADD COLUMN `discount_details` text DEFAULT NULL AFTER `tax_details`;

    ALTER TABLE `m2epro_amazon_order_item`
    DROP COLUMN `tax_amount`,
    DROP COLUMN `discount_amount`,
    ADD COLUMN `tax_details` text DEFAULT NULL AFTER `gift_type`,
    ADD COLUMN `discount_details` text DEFAULT NULL AFTER `tax_details`;

    ALTER TABLE `m2epro_lock_item`
    ADD COLUMN `kill_now` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `parent_id`;

    ALTER TABLE `m2epro_processing_request`
    ADD COLUMN `expiration_date` DATETIME NOT NULL AFTER `responser_params`;
    UPDATE `m2epro_processing_request` SET `expiration_date` = DATE_ADD(`create_date`, INTERVAL 24 HOUR);

    DELETE FROM `m2epro_marketplace`
    WHERE `id` = 23;

    DELETE FROM `m2epro_ebay_marketplace`
    WHERE `marketplace_id` = 23;

    UPDATE m2epro_ebay_template_shipping
    SET dispatch_time = 1
    WHERE dispatch_time_mode = 1;

    UPDATE m2epro_ebay_template_shipping
    SET cash_on_delivery_cost = NULL
    WHERE local_shipping_cash_on_delivery_cost_mode = 2 OR cash_on_delivery_cost = '';

    ALTER TABLE `m2epro_ebay_template_shipping`
    DROP COLUMN `dispatch_time_mode`,
    DROP COLUMN `dispatch_time_attribute`,
    DROP COLUMN `local_shipping_cash_on_delivery_cost_mode`,
    DROP COLUMN `local_shipping_cash_on_delivery_cost_attribute`;

    ALTER TABLE `m2epro_ebay_template_shipping_calculated`
    CHANGE COLUMN `local_handling_cost_value` `local_handling_cost` VARCHAR(255) DEFAULT NULL,
    CHANGE COLUMN `international_handling_cost_value` `international_handling_cost` VARCHAR(255) DEFAULT NULL;

    UPDATE m2epro_ebay_template_shipping_calculated
    SET local_handling_cost = NULL
    WHERE local_handling_cost_mode = 2 OR local_handling_cost = '';

    UPDATE m2epro_ebay_template_shipping_calculated
    SET international_handling_cost = NULL
    WHERE international_handling_cost_mode = 2 OR international_handling_cost = '';

    ALTER TABLE `m2epro_ebay_template_shipping_calculated`
    DROP COLUMN `local_handling_cost_mode`,
    DROP COLUMN `local_handling_cost_attribute`,
    DROP COLUMN `international_handling_cost_mode`,
    DROP COLUMN `international_handling_cost_attribute`;

    ALTER TABLE `m2epro_ebay_template_shipping_calculated`
    CHANGE COLUMN `dimension_height_value` `dimension_length_value` VARCHAR(500) NOT NULL,
    CHANGE COLUMN `dimension_height_attribute` `dimension_length_attribute` VARCHAR(255) NOT NULL;

    UPDATE m2epro_ebay_template_shipping mets
    JOIN m2epro_ebay_template_shipping_calculated metsc ON (mets.id = metsc.template_shipping_id)
    SET mets.postal_code = metsc.originating_postal_code
    WHERE mets.postal_code = '';

    ALTER TABLE `m2epro_ebay_template_shipping_calculated`
    DROP COLUMN `originating_postal_code`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_shipping');
$indexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'international_trade') !== false &&
    $connection->tableColumnExists($tempTable, 'cross_border_trade') === false) {
    $connection->changeColumn(
        $tempTable,
        'international_trade',
        'cross_border_trade',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0'
    );
}

if ($connection->tableColumnExists($tempTable, 'dispatch_time_value') !== false &&
    $connection->tableColumnExists($tempTable, 'dispatch_time') === false) {
    $connection->changeColumn(
        $tempTable,
        'dispatch_time_value',
        'dispatch_time',
        'INT(11) UNSIGNED NOT NULL DEFAULT 1'
    );
}

if ($connection->tableColumnExists($tempTable, 'local_shipping_cash_on_delivery_cost_value') !== false &&
    $connection->tableColumnExists($tempTable, 'cash_on_delivery_cost') === false) {
    $connection->changeColumn(
        $tempTable,
        'local_shipping_cash_on_delivery_cost_value',
        'cash_on_delivery_cost',
        'VARCHAR(255) DEFAULT NULL'
    );
}

if ($connection->tableColumnExists($tempTable, 'local_shipping_combined_discount_profile_id') !== false &&
    $connection->tableColumnExists($tempTable, 'local_shipping_discount_profile_id') === false) {
    $connection->changeColumn(
        $tempTable,
        'local_shipping_combined_discount_profile_id',
        'local_shipping_discount_profile_id',
        'TEXT DEFAULT NULL'
    );
}

if ($connection->tableColumnExists($tempTable, 'international_shipping_combined_discount_profile_id') !== false &&
    $connection->tableColumnExists($tempTable, 'international_shipping_discount_profile_id') === false) {
    $connection->changeColumn(
        $tempTable,
        'international_shipping_combined_discount_profile_id',
        'international_shipping_discount_profile_id',
        'TEXT DEFAULT NULL'
    );
}

if (isset($indexList[strtoupper('international_trade')])) {
    $connection->dropKey($tempTable, 'international_trade');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_selling_format');

if ($connection->tableColumnExists($tempTable, 'qty_percentage') === false) {
    $connection->addColumn(
        $tempTable,
        'qty_percentage',
        'int(11) UNSIGNED NOT NULL DEFAULT 100 AFTER `qty_custom_attribute`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_selling_format');

if ($connection->tableColumnExists($tempTable, 'qty_percentage') === false) {
    $connection->addColumn(
        $tempTable,
        'qty_percentage',
        'int(11) UNSIGNED NOT NULL DEFAULT 100 AFTER `qty_custom_attribute`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_template_selling_format');

if ($connection->tableColumnExists($tempTable, 'qty_percentage') === false) {
    $connection->addColumn(
        $tempTable,
        'qty_percentage',
        'int(11) UNSIGNED NOT NULL DEFAULT 100 AFTER `qty_custom_attribute`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_play_template_selling_format');

if ($connection->tableColumnExists($tempTable, 'qty_percentage') === false) {
    $connection->addColumn(
        $tempTable,
        'qty_percentage',
        'int(11) UNSIGNED NOT NULL DEFAULT 100 AFTER `qty_custom_attribute`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_order');

if ($connection->tableColumnExists($tempTable, 'state') !== false) {
    $connection->dropColumn($tempTable, 'state');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_order_item');

if ($connection->tableColumnExists($tempTable, 'state') !== false) {
    $connection->dropColumn($tempTable, 'state');
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_order');

if ($connection->tableColumnExists($tempTable, 'tax_amount') !== false) {
    $connection->dropColumn($tempTable,'tax_amount');
}

if ($connection->tableColumnExists($tempTable, 'discount_amount') !== false) {
    $connection->dropColumn($tempTable, 'discount_amount');
}

if ($connection->tableColumnExists($tempTable, 'tax_details') === false) {
    $connection->addColumn(
        $tempTable,
        'tax_details',
        'text DEFAULT NULL AFTER `paid_amount`'
    );
}

if ($connection->tableColumnExists($tempTable, 'discount_details') === false) {
    $connection->addColumn(
        $tempTable,
        'discount_details',
        'text DEFAULT NULL AFTER `tax_details`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_order_item');

if ($connection->tableColumnExists($tempTable, 'tax_amount') !== false) {
    $connection->dropColumn($tempTable, 'tax_amount');
}

if ($connection->tableColumnExists($tempTable, 'discount_amount') !== false) {
    $connection->dropColumn($tempTable, 'discount_amount');
}

if ($connection->tableColumnExists($tempTable, 'tax_details') === false) {
    $connection->addColumn(
        $tempTable,
        'tax_details',
        'text DEFAULT NULL AFTER `gift_type`'
    );
}

if ($connection->tableColumnExists($tempTable, 'discount_details') === false) {
    $connection->addColumn(
        $tempTable,
        'discount_details',
        'text DEFAULT NULL AFTER `tax_details`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_lock_item');

if ($connection->tableColumnExists($tempTable, 'kill_now') === false) {
    $connection->addColumn(
        $tempTable,
        'kill_now',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `parent_id`'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_processing_request');

if ($connection->tableColumnExists($tempTable, 'expiration_date') === false) {
    $connection->addColumn(
        $tempTable,
        'expiration_date',
        'DATETIME NOT NULL AFTER `responser_params`'
    );
}

$connection->update(
    $tempTable,
    array('expiration_date' => new Zend_Db_Expr("DATE_ADD(create_date, INTERVAL 24 HOUR)"))
);

// REMOVE EBAY SWEDEN MARKETPLACE
//#############################################

//-- listings tables
$lTable    = $installer->getTable('m2epro_listing');
$lpTable   = $installer->getTable('m2epro_listing_product');
$lpvTable  = $installer->getTable('m2epro_listing_product_variation');
$lpvoTable = $installer->getTable('m2epro_listing_product_variation_option');

$rowsForDelete = $connection->query(
    "SELECT ml.ID AS listing_id,
            mlp.ID AS listing_product_id,
            mlpv.ID AS listing_product_variation_id,
            mlpvo.ID AS listing_product_variation_option_id
    FROM {$lTable} ml
    LEFT JOIN {$lpTable} mlp
       ON ml.ID = mlp.listing_id
    LEFT JOIN {$lpvTable} mlpv
       ON mlp.ID = mlpv.listing_product_id
    LEFT JOIN {$lpvoTable} mlpvo
       ON mlpv.ID = mlpvo.listing_product_variation_id
    WHERE ml.marketplace_id = 23;"
)->fetchAll();

$lIds = $lpIds = $lpvIds = $lpvoIds = array();
foreach ($rowsForDelete as $row) {
    $lIds[]    = $row['listing_id'];
    $lpIds[]   = $row['listing_product_id'];
    $lpvIds[]  = $row['listing_product_variation_id'];
    $lpvoIds[] = $row['listing_product_variation_option_id'];
}
$lIds = array_filter(array_unique($lIds));
$lpIds = array_filter(array_unique($lpIds));
$lpvIds = array_filter(array_unique($lpvIds));
$lpvoIds = array_filter(array_unique($lpvoIds));

deleteByIdsFromHorizontalTable($installer, $lIds, 'm2epro_listing');
deleteByIdsFromHorizontalTable($installer, $lpIds, 'm2epro_listing_product');
deleteByIdsFromHorizontalTable($installer, $lpvIds, 'm2epro_listing_product_variation');
deleteByIdsFromHorizontalTable($installer, $lpvoIds, 'm2epro_listing_product_variation_option');

//-- listings other tables
$loTable = $installer->getTable('m2epro_listing_other');
$queryStmt = $connection->query(
    "SELECT `lo`.`id` FROM {$loTable} lo WHERE `marketplace_id` = 23"
);

$ids = array();
while ($id = $queryStmt->fetchColumn()) {
    $ids[] = $id;
}
$ids = array_filter(array_unique($ids));

deleteByIdsFromHorizontalTable($installer, $ids, 'm2epro_listing_other');

//-- orders tables
$oTable  = $installer->getTable('m2epro_order');
$oiTable = $installer->getTable('m2epro_order_item');

$rowsForDelete = $connection->query(
    "SELECT mo.ID AS order_id,
            moi.ID AS order_item_id
    FROM {$oTable} mo
    LEFT JOIN {$oiTable} moi
        ON mo.ID = moi.order_id
    WHERE mo.marketplace_id = 23"
)->fetchAll();

$oIds = $oiIds = array();
foreach ($rowsForDelete as $row) {
    $oIds[]  = $row['order_id'];
    $oiIds[] = $row['order_item_id'];
}
$oIds = array_filter(array_unique($oIds));
$oiIds = array_filter(array_unique($oiIds));

deleteByIdsFromHorizontalTable($installer, $oIds, 'm2epro_order');
deleteByIdsFromHorizontalTable($installer, $oiIds, 'm2epro_order_item');

//-- other related tables
$where = array('marketplace_id = 23');

$connection->delete($installer->getTable('m2epro_stop_queue'), $where);
$connection->delete($installer->getTable('m2epro_ebay_account_policy'), $where);
$connection->delete($installer->getTable('m2epro_ebay_dictionary_category'), $where);
$connection->delete($installer->getTable('m2epro_ebay_dictionary_marketplace'), $where);
$connection->delete($installer->getTable('m2epro_ebay_dictionary_shipping'), $where);
$connection->delete($installer->getTable('m2epro_ebay_dictionary_shipping_category'), $where);
$connection->delete($installer->getTable('m2epro_ebay_item'), $where);
$connection->delete($installer->getTable('m2epro_marketplace'), array('id = 23'));
$connection->delete($installer->getTable('m2epro_ebay_marketplace'), $where);
$connection->delete($installer->getTable('m2epro_ebay_dictionary_motor_specific'), $where);
$connection->delete($installer->getTable('m2epro_ebay_template_category'), $where);
$connection->delete($installer->getTable('m2epro_ebay_template_other_category'), $where);
$connection->delete($installer->getTable('m2epro_ebay_template_payment'), $where);
$connection->delete($installer->getTable('m2epro_ebay_template_return'), $where);
$connection->delete($installer->getTable('m2epro_ebay_template_shipping'), $where);

// -- help function
function deleteByIdsFromHorizontalTable($installer, array $ids, $parentTable)
{
    /** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
    /** @var $connection Varien_Db_Adapter_Pdo_Mysql */
    $connection = $installer->getConnection();

    $childTable = str_replace('m2epro_', 'm2epro_ebay_', $parentTable);
    $childIdField = str_replace('m2epro_', '', $parentTable).'_id';

    foreach (array_chunk($ids, 500) as $idsPart) {

        if (count($idsPart) <= 0) {
            continue;
        }

        $mysqlIds = implode(',', $idsPart);

        $connection->delete($installer->getTable($parentTable), "`id` IN ({$mysqlIds})");
        $connection->delete($installer->getTable($childTable), "`{$childIdField}` IN ({$mysqlIds})");
    }
}

// CHANGE SHIPPING TABLE VALUES
//#############################################

$tempTable = $installer->getTable('m2epro_ebay_template_shipping');

// -- dispatch_time
if ($connection->tableColumnExists($tempTable, 'dispatch_time_mode') !== false &&
    $connection->tableColumnExists($tempTable, 'dispatch_time') !== false) {

    $installer->run(<<<SQL
UPDATE m2epro_ebay_template_shipping
SET dispatch_time = 1
WHERE dispatch_time_mode = 1
OR dispatch_time = 0;
SQL
    );
}

if ($connection->tableColumnExists($tempTable, 'dispatch_time_mode') !== false) {
    $connection->dropColumn($tempTable, 'dispatch_time_mode');
}

if ($connection->tableColumnExists($tempTable, 'dispatch_time_attribute') !== false) {
    $connection->dropColumn($tempTable, 'dispatch_time_attribute');
}

// cash_on_delivery
if ($connection->tableColumnExists($tempTable, 'local_shipping_cash_on_delivery_cost_mode') !== false &&
    $connection->tableColumnExists($tempTable, 'cash_on_delivery_cost') !== false) {

    $installer->run(<<<SQL
UPDATE m2epro_ebay_template_shipping
SET cash_on_delivery_cost = NULL
WHERE local_shipping_cash_on_delivery_cost_mode = 2
OR cash_on_delivery_cost = '';
SQL
    );
}

if ($connection->tableColumnExists($tempTable, 'local_shipping_cash_on_delivery_cost_mode') !== false) {
    $connection->dropColumn($tempTable, 'local_shipping_cash_on_delivery_cost_mode');
}

if ($connection->tableColumnExists($tempTable, 'local_shipping_cash_on_delivery_cost_attribute') !== false) {
    $connection->dropColumn($tempTable, 'local_shipping_cash_on_delivery_cost_attribute');
}

// CHANGE CALCULATED SHIPPING TABLE VALUES
//#############################################

$tempTable = $installer->getTable('m2epro_ebay_template_shipping_calculated');

// -- local_handling_cost
if ($connection->tableColumnExists($tempTable, 'local_handling_cost_value') !== false &&
    $connection->tableColumnExists($tempTable, 'local_handling_cost') === false) {
    $connection->changeColumn(
        $tempTable,
        'local_handling_cost_value',
        'local_handling_cost',
        'VARCHAR(255) DEFAULT NULL'
    );
}

if ($connection->tableColumnExists($tempTable, 'local_handling_cost_mode') !== false &&
    $connection->tableColumnExists($tempTable, 'local_handling_cost') !== false) {

    $installer->run(<<<SQL
UPDATE m2epro_ebay_template_shipping_calculated
SET local_handling_cost = NULL
WHERE local_handling_cost_mode = 2
OR local_handling_cost = '';
SQL
    );
}

if ($connection->tableColumnExists($tempTable, 'local_handling_cost_mode') !== false) {
    $connection->dropColumn($tempTable, 'local_handling_cost_mode');
}

if ($connection->tableColumnExists($tempTable, 'local_handling_cost_attribute') !== false) {
    $connection->dropColumn($tempTable, 'local_handling_cost_attribute');
}

// -- international_handling_cost
if ($connection->tableColumnExists($tempTable, 'international_handling_cost_value') !== false &&
    $connection->tableColumnExists($tempTable, 'international_handling_cost') === false) {
    $connection->changeColumn(
        $tempTable,
        'international_handling_cost_value',
        'international_handling_cost',
        'VARCHAR(255) DEFAULT NULL'
    );
}

if ($connection->tableColumnExists($tempTable, 'international_handling_cost_mode') !== false &&
    $connection->tableColumnExists($tempTable, 'international_handling_cost') !== false) {

    $installer->run(<<<SQL
UPDATE m2epro_ebay_template_shipping_calculated
SET international_handling_cost = NULL
WHERE international_handling_cost_mode = 2
OR international_handling_cost = '';
SQL
    );
}

if ($connection->tableColumnExists($tempTable, 'international_handling_cost_mode') !== false) {
    $connection->dropColumn($tempTable, 'international_handling_cost_mode');
}

if ($connection->tableColumnExists($tempTable, 'international_handling_cost_attribute') !== false) {
    $connection->dropColumn($tempTable, 'international_handling_cost_attribute');
}

// -------------------------------------------

// -- dimension_length_value
if ($connection->tableColumnExists($tempTable, 'dimension_height_value') !== false &&
    $connection->tableColumnExists($tempTable, 'dimension_length_value') === false) {
    $connection->changeColumn(
        $tempTable,
        'dimension_height_value',
        'dimension_length_value',
        'VARCHAR(500) NOT NULL'
    );
}
// -- dimension_length_attribute
if ($connection->tableColumnExists($tempTable, 'dimension_height_attribute') !== false &&
    $connection->tableColumnExists($tempTable, 'dimension_length_attribute') === false) {
    $connection->changeColumn(
        $tempTable,
        'dimension_height_attribute',
        'dimension_length_attribute',
        'VARCHAR(255) NOT NULL'
    );
}

// -------------------------------------------

if ($connection->tableColumnExists($tempTable, 'originating_postal_code') !== false) {

    $installer->run(<<<SQL
UPDATE m2epro_ebay_template_shipping mets
JOIN m2epro_ebay_template_shipping_calculated metsc ON (mets.id = metsc.template_shipping_id)
SET mets.postal_code = metsc.originating_postal_code
WHERE mets.postal_code = '';
SQL
    );
}

if ($connection->tableColumnExists($tempTable, 'originating_postal_code') !== false) {
    $connection->dropColumn($tempTable, 'originating_postal_code');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/qty/percentage/'
    AND   `key` = 'rounding_greater'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/qty/percentage/', 'rounding_greater', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
('/cron/service/', 'hostname', 'cron.m2epro.com', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
    );
}

//#############################################

$installer->endSetup();

//#############################################