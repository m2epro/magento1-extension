<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_template_selling_format`
    ADD COLUMN `price_discount_stp_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `buyitnow_price_custom_attribute`,
    ADD COLUMN `price_discount_stp_attribute` VARCHAR(255) NOT NULL AFTER `price_discount_stp_mode`,
    ADD COLUMN `price_discount_stp_type` TINYINT(2) UNSIGNED NOT NULL AFTER `price_discount_stp_attribute`,
    ADD COLUMN `out_of_stock_control` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `duration_attribute`,
    ADD COLUMN `price_increase_vat_percent` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `qty_max_posted_value`;

    ALTER TABLE `m2epro_ebay_marketplace`
    ADD COLUMN `is_stp` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_vat`,
    ADD COLUMN `is_stp_advanced` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_stp`,
    ADD COLUMN `is_charity` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_global_shipping_program`,
    ADD COLUMN `is_multi_currency` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `currency`,
    CHANGE COLUMN `is_tax` `is_tax_table` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
    DROP INDEX `is_tax`,
    ADD INDEX `is_stp` (`is_stp`),
    ADD INDEX `is_stp_advanced` (`is_stp_advanced`),
    ADD INDEX `is_tax_table` (`is_tax_table`),
    ADD INDEX `is_multi_currency` (`is_multi_currency`),
    ADD INDEX `is_charity` (`is_charity`);

    ALTER TABLE `m2epro_ebay_item`
    ADD COLUMN `account_id` INT(11) UNSIGNED NOT NULL AFTER `id`,
    ADD COLUMN `marketplace_id` INT(11) UNSIGNED NOT NULL AFTER `account_id`,
    ADD INDEX `account_id` (`account_id`),
    ADD INDEX `marketplace_id` (`marketplace_id`);

    ALTER TABLE `m2epro_ebay_template_category`
    DROP COLUMN `variation_enabled`;

    ALTER TABLE `m2epro_ebay_listing_product`
    DROP COLUMN `is_m2epro_listed_item`;

    ALTER TABLE `m2epro_ebay_template_synchronization`
    CHANGE COLUMN `revise_update_gallery` `revise_update_images` TINYINT(2) UNSIGNED NOT NULL;

    ALTER TABLE `m2epro_order_item`
    ADD COLUMN `qty_reserved` INT(11) UNSIGNED DEFAULT 0 AFTER `component_mode`;

    ALTER TABLE `m2epro_amazon_listing_product`
    ADD COLUMN `online_sale_price_start_date` DATETIME DEFAULT NULL AFTER `online_sale_price`,
    ADD COLUMN `online_sale_price_end_date` DATETIME DEFAULT NULL AFTER `online_sale_price_start_date`;
*/

//---------------------------------------------

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

//---------------------------------------------

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

//---------------------------------------------

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

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_category');

if ($connection->tableColumnExists($tempTable, 'variation_enabled') !== false) {
    $connection->dropColumn(
        $tempTable,
        'variation_enabled'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing_product');

if ($connection->tableColumnExists($tempTable, 'is_m2epro_listed_item') !== false) {
    $connection->dropColumn(
        $tempTable,
        'is_m2epro_listed_item'
    );
}

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_synchronization');

if ($connection->tableColumnExists($tempTable, 'revise_update_gallery') !== false &&
    $connection->tableColumnExists($tempTable, 'revise_update_images') === false) {
    $connection->changeColumn($tempTable,'revise_update_gallery','revise_update_images','TINYINT(2) UNSIGNED NOT NULL');
}

//---------------------------------------------

$orderItemTable = $installer->getTable('m2epro_order_item');

if ($connection->tableColumnExists($orderItemTable, 'qty_reserved') === false) {
    $connection->addColumn(
        $orderItemTable,
        'qty_reserved',
        'INT(11) UNSIGNED DEFAULT 0 AFTER `component_mode`'
    );
}

//---------------------------------------------

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

//#############################################

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

//#############################################

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

//#############################################

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

//#############################################

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

//#############################################

/*
    ALTER TABLE `m2epro_ebay_template_selling_format`
    ADD COLUMN `vat_percent` FLOAT UNSIGNED NOT NULL DEFAULT 0 AFTER `qty_max_posted_value`,
    ADD COLUMN `tax_table_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `vat_percent`,
    ADD COLUMN `tax_category_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `tax_table_mode`,
    ADD COLUMN `tax_category_value` VARCHAR(255) NOT NULL AFTER `tax_category_mode`,
    ADD COLUMN `tax_category_attribute` VARCHAR(255) NOT NULL AFTER `tax_category_value`;
*/

//---------------------------------------------

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

//---------------------------------------------

try {
    $migrationTool = new Ess_M2ePro_Migration_Tool_To_Version_6_0_9($installer);
    $migrationTool->process();
} catch (Exception $exception) {}

//---------------------------------------------

/*
    ALTER TABLE `m2epro_ebay_template_shipping`
    DROP COLUMN `tax_table_mode`,
    DROP COLUMN `vat_percent`,
    DROP COLUMN `tax_category_mode`,
    DROP COLUMN `tax_category_value`,
    DROP COLUMN `tax_category_attribute`;
*/

//---------------------------------------------

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

//#############################################

$installer->endSetup();

//#############################################

class Ess_M2ePro_Migration_Tool_To_Version_6_0_9
{
    const DEVELOPMENT_MODE = false;

    const PREFIX_TABLE_BACKUP = '__backup_v608';

    const TEMPLATE_MODE_PARENT   = 0;
    const TEMPLATE_MODE_CUSTOM   = 1;
    const TEMPLATE_MODE_TEMPLATE = 2;
    const TEMPLATE_MODE_POLICY   = 3;

    const RESULT_LISTINGS_KEY          = 'listings';
    const RESULT_LISTINGS_PRODUCTS_KEY = 'listings_products';
    const RESULT_CHANGED_MODES         = 'changed_modes';

    //#############################################

    /** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = null;

    private $buffer = array();
    private $result = array();

    //#############################################

    public function __construct(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    //#############################################

    public function process()
    {
        if (!$this->isNeedToMigrate()) {
            return;
        }

        $this->backup();

        $affectedShippingTemplatesIds = $this->getAffectedShippingTemplatesIds();

        if (empty($affectedShippingTemplatesIds)) {
            return;
        }

        if (self::DEVELOPMENT_MODE) {
            var_dump('affected_shipping_templates_ids', $affectedShippingTemplatesIds);
        }

        $this->migrateListings($affectedShippingTemplatesIds);
        $this->migrateListingsProducts($affectedShippingTemplatesIds);

        $this->updateListingsProductsSellingTemplateModes();

        $this->updateMigratedItems(self::RESULT_LISTINGS_KEY);
        $this->updateMigratedItems(self::RESULT_LISTINGS_PRODUCTS_KEY);

        if (self::DEVELOPMENT_MODE) {
            var_dump('buffer', $this->getMigrationBuffer(), 'result', $this->getMigrationResult());
        }
    }

    //#############################################

    public function getInstaller()
    {
        return $this->installer;
    }

    //---------------------------------------------

    public function getMigrationBuffer()
    {
        return $this->buffer;
    }

    //---------------------------------------------

    public function getMigrationResult($key = null)
    {
        if (is_null($key)) {
            return $this->result;
        }

        return isset($this->result[$key]) ? $this->result[$key] : array();
    }

    //#############################################

    private function isNeedToMigrate()
    {
        $tempTable = $this->getInstaller()->getTable('m2epro_ebay_template_shipping');
        $connection = $this->getInstaller()->getConnection();

        return $connection->tableColumnExists($tempTable, 'vat_percent') !== false &&
               $connection->tableColumnExists($tempTable, 'tax_table_mode') !== false &&
               $connection->tableColumnExists($tempTable, 'tax_category_mode') !== false;
    }

    //#############################################

    private function backup()
    {
        $oldTable = $this->getInstaller()->getTable('m2epro_ebay_template_shipping');
        $backupTable = $this->getInstaller()->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_shipping');

        $this->getInstaller()->getConnection()->multi_query(<<<SQL
DROP TABLE IF EXISTS `{$backupTable}`;
CREATE TABLE `$backupTable` SELECT * FROM `$oldTable`;
SQL
);
    }

    //---------------------------------------------

    private function migrateListings($affectedShippingTemplatesIds)
    {
        $affectedListingsByShippingTemplatesId = $this->getAffectedListings($affectedShippingTemplatesIds);

        if (self::DEVELOPMENT_MODE) {
            var_dump('affected_listings', $affectedListingsByShippingTemplatesId);
        }

        foreach ($affectedListingsByShippingTemplatesId as $shippingTemplateId => $affectedListings) {
            foreach ($affectedListings as $listingId => $listingData) {

                $sellingTemplateMode = $listingData['template_selling_format_mode'];

                $sellingTemplateId = $sellingTemplateMode == self::TEMPLATE_MODE_CUSTOM
                    ? $listingData['template_selling_format_custom_id']
                    : $listingData['template_selling_format_id'];

                $newSellingTemplateId = $this->getNewSellingTemplateIdFromBuffer(
                    $shippingTemplateId, $sellingTemplateId
                );

                if (!$newSellingTemplateId) {
                    $newSellingTemplateId = $this->createNewSellingTemplate($sellingTemplateId, $shippingTemplateId);
                    $this->appendToBuffer($shippingTemplateId, $sellingTemplateId, $newSellingTemplateId);
                }

                $this->appendMigrationResult(
                    self::RESULT_LISTINGS_KEY, $sellingTemplateMode, $newSellingTemplateId, $listingId
                );
            }
        }
    }

    private function migrateListingsProducts($affectedShippingTemplatesIds)
    {
        $affectedListingsProductsByShippingTemplatesId = $this->arrayReplaceRecursive(
            $this->getAffectedListingsProductsShippingTemplateModeParent($affectedShippingTemplatesIds),
            $this->getAffectedListingsProductsShippingTemplateModeOther($affectedShippingTemplatesIds)
        );

        if (self::DEVELOPMENT_MODE) {
            var_dump('affected_listings_products', $affectedListingsProductsByShippingTemplatesId);
        }

        foreach ($affectedListingsProductsByShippingTemplatesId as $shippingTemplateId => $affectedListingsProducts) {
            foreach ($affectedListingsProducts as $listingProductId => $listingProductData) {

                switch ($sellingTemplateMode = $listingProductData['template_selling_format_mode']) {

                    case self::TEMPLATE_MODE_CUSTOM:
                        $sellingTemplateId = $listingProductData['template_selling_format_custom_id'];
                        break;

                    case self::TEMPLATE_MODE_TEMPLATE:
                        $sellingTemplateId = $listingProductData['template_selling_format_id'];
                        break;

                    default:
                    case self::TEMPLATE_MODE_PARENT:
                        $sellingTemplateId = $listingProductData['listing_selling_format_id'];
                        $sellingTemplateMode = self::TEMPLATE_MODE_CUSTOM;
                        $this->appendChangedModeResult($listingProductId);
                        break;
                }

                $newSellingTemplateId = $this->getNewSellingTemplateIdFromBuffer(
                    $shippingTemplateId, $sellingTemplateId
                );

                if (!$newSellingTemplateId) {
                    $newSellingTemplateId = $this->createNewSellingTemplate($sellingTemplateId, $shippingTemplateId);
                    $this->appendToBuffer($shippingTemplateId, $sellingTemplateId, $newSellingTemplateId);
                }

                $this->appendMigrationResult(
                    self::RESULT_LISTINGS_PRODUCTS_KEY, $sellingTemplateMode, $newSellingTemplateId, $listingProductId
                );
            }
        }
    }

    //---------------------------------------------

    private function updateMigratedItems($key)
    {
        $tableName = 'm2epro_ebay_listing';
        $mysqlWhereColumn = 'listing_id';

        if ($key == self::RESULT_LISTINGS_PRODUCTS_KEY) {
            $tableName = 'm2epro_ebay_listing_product';
            $mysqlWhereColumn = 'listing_product_id';
        }

        $table = $this->getInstaller()->getTable($tableName);

        foreach ($this->getMigrationResult($key) as $sellingTemplateMode => $sellingTemplatesIds) {

            $mysqlUpdateColumn = $sellingTemplateMode == self::TEMPLATE_MODE_CUSTOM
                ? 'template_selling_format_custom_id' : 'template_selling_format_id';

            foreach ($sellingTemplatesIds as $newTemplateId => $ids) {

                $sqlInCondition = implode(',', $ids);

                $this->getInstaller()->getConnection()->query(<<<SQL
UPDATE `{$table}`
SET `{$mysqlUpdateColumn}` = {$newTemplateId}
WHERE `{$mysqlWhereColumn}` IN ($sqlInCondition)
SQL
);
            }
        }
    }

    private function updateListingsProductsSellingTemplateModes()
    {
        $changedModes = $this->getMigrationResult(self::RESULT_CHANGED_MODES);

        if (empty($changedModes)) {
            return;
        }

        foreach ($changedModes as $listingProductMode => $listingsIds) {

            $listingProductTable = $this->getInstaller()->getTable('m2epro_ebay_listing_product');
            $sqlInCondition = implode(',', $listingsIds);

            $this->getInstaller()->getConnection()->query(<<<SQL
UPDATE `{$listingProductTable}`
SET `template_selling_format_mode` = {$listingProductMode}
WHERE `listing_product_id` IN ({$sqlInCondition})
SQL
);
        }
    }

    //#############################################

    private function getAffectedShippingTemplatesIds()
    {
        $tempTable = $this->getInstaller()->getTable('m2epro_ebay_template_shipping');

        $queryStmt = $this->getInstaller()->getConnection()->query(<<<SQL
SELECT `id` FROM `{$tempTable}`
WHERE `vat_percent` > 0 OR `tax_table_mode` > 0 OR `tax_category_mode` > 0
SQL
);
        $result = array();
        while ($id = $queryStmt->fetchColumn()) {
            $result[] = (int)$id;
        }

        return $result;
    }

    //---------------------------------------------

    private function getAffectedListings($shippingTemplatesIds)
    {
        $tempTable = $this->getInstaller()->getTable('m2epro_ebay_listing');

        $sqlInCondition = implode(',', $shippingTemplatesIds);
        $sqlModeTemplate = self::TEMPLATE_MODE_TEMPLATE;
        $sqlModeCustom   = self::TEMPLATE_MODE_CUSTOM;

        $queryStmt = $this->getInstaller()->getConnection()->query(<<<SQL
SELECT *,
       IF (`template_shipping_mode` = {$sqlModeTemplate},
           `template_shipping_id`,
           `template_shipping_custom_id`) AS `shipping_id`
FROM `{$tempTable}`
WHERE (`template_shipping_mode` = {$sqlModeTemplate} AND `template_shipping_id` IN ({$sqlInCondition}))
   OR (`template_shipping_mode` = {$sqlModeCustom} AND `template_shipping_custom_id` IN ({$sqlInCondition}))
SQL
);
        $result = array();
        while ($row = $queryStmt->fetch()) {
            $result[$row['shipping_id']][$row['listing_id']] = $row;
        }

        return $result;
    }

    private function getAffectedListingsProductsShippingTemplateModeParent($shippingTemplatesIds)
    {
        $listingTable = $this->getInstaller()->getTable('m2epro_ebay_listing');
        $generalListingProductTable = $this->getInstaller()->getTable('m2epro_listing_product');
        $listingProductTable = $this->getInstaller()->getTable('m2epro_ebay_listing_product');

        $sqlInCondition = implode(',', $shippingTemplatesIds);

        $sqlModeTemplate = self::TEMPLATE_MODE_TEMPLATE;
        $sqlModeCustom   = self::TEMPLATE_MODE_CUSTOM;
        $sqlModeParent   = self::TEMPLATE_MODE_PARENT;

        $queryStmt = $this->getInstaller()->getConnection()->query(<<<SQL
    SELECT `{$listingProductTable}`.*,
           IF (`{$listingTable}`.`template_shipping_mode` = {$sqlModeTemplate},
               `{$listingTable}`.`template_shipping_id`,
               `{$listingTable}`.`template_shipping_custom_id`) AS `shipping_id`
    FROM `{$listingProductTable}`
    LEFT JOIN `{$generalListingProductTable}`
        ON `{$listingProductTable}`.`listing_product_id` = `{$generalListingProductTable}`.`id`
    LEFT JOIN `{$listingTable}`
        ON `{$generalListingProductTable}`.`listing_id` = `{$listingTable}`.`listing_id`
    WHERE `{$listingProductTable}`.`template_shipping_mode` = {$sqlModeParent}
        AND `{$listingProductTable}`.`template_selling_format_mode` <> {$sqlModeParent}
        AND (`{$listingTable}`.`template_shipping_mode` = {$sqlModeTemplate}
                AND `{$listingTable}`.`template_shipping_id` IN ({$sqlInCondition})
             OR `{$listingTable}`.`template_shipping_mode` = {$sqlModeCustom}
                AND `{$listingTable}`.`template_shipping_custom_id` IN ({$sqlInCondition}))
SQL
);
        $result = array();
        while ($row = $queryStmt->fetch()) {
            $result[$row['shipping_id']][$row['listing_product_id']] = $row;
        }

        return $result;
    }

    private function getAffectedListingsProductsShippingTemplateModeOther($shippingTemplatesIds)
    {
        $listingTable = $this->getInstaller()->getTable('m2epro_ebay_listing');
        $generalListingProductTable = $this->getInstaller()->getTable('m2epro_listing_product');
        $listingProductTable = $this->getInstaller()->getTable('m2epro_ebay_listing_product');

        $sqlInCondition = implode(',', $shippingTemplatesIds);
        $sqlModeTemplate = self::TEMPLATE_MODE_TEMPLATE;
        $sqlModeCustom   = self::TEMPLATE_MODE_CUSTOM;

        $queryStmt = $this->getInstaller()->getConnection()->query(<<<SQL
SELECT `{$listingProductTable}`.*,
       IF (`{$listingProductTable}`.`template_shipping_mode` = {$sqlModeTemplate},
           `{$listingProductTable}`.`template_shipping_id`,
           `{$listingProductTable}`.`template_shipping_custom_id`) AS `shipping_id`,
       IF (`{$listingTable}`.`template_selling_format_mode` = {$sqlModeTemplate},
           `{$listingTable}`.`template_selling_format_id`,
           `{$listingTable}`.`template_selling_format_custom_id`) AS `listing_selling_format_id`
FROM `{$listingProductTable}`
LEFT JOIN `{$generalListingProductTable}`
    ON `{$listingProductTable}`.`listing_product_id` = `{$generalListingProductTable}`.`id`
LEFT JOIN `{$listingTable}`
    ON `{$generalListingProductTable}`.`listing_id` = `{$listingTable}`.`listing_id`
WHERE (`{$listingProductTable}`.`template_shipping_mode` = {$sqlModeTemplate}
          AND `{$listingProductTable}`.`template_shipping_id` IN ({$sqlInCondition}))
       OR (`{$listingProductTable}`.`template_shipping_mode` = {$sqlModeCustom}
          AND `{$listingProductTable}`.`template_shipping_custom_id` IN ({$sqlInCondition}))
SQL
);
        $result = array();
        while ($row = $queryStmt->fetch()) {
            $result[$row['shipping_id']][$row['listing_product_id']] = $row;
        }

        return $result;
    }

    //#############################################

    private function getNewSellingTemplateIdFromBuffer($shippingTemplateId, $sellingTemplateId)
    {
        $buffer = $this->getMigrationBuffer();

        return isset($buffer[$shippingTemplateId.'#'.$sellingTemplateId])
            ? $buffer[$shippingTemplateId.'#'.$sellingTemplateId] : false;
    }

    private function appendToBuffer($shippingTemplateId, $sellingTemplateId, $newSellingTemplateId)
    {
        $this->buffer[$shippingTemplateId.'#'.$sellingTemplateId] = $newSellingTemplateId;
    }

    //---------------------------------------------

    private function appendMigrationResult($key, $templateMode, $newSellingTemplateId, $targetId)
    {
        $this->result[$key][$templateMode][$newSellingTemplateId][] = $targetId;
    }

    private function appendChangedModeResult($listingProductId, $templateMode = self::TEMPLATE_MODE_CUSTOM)
    {
        $this->result[self::RESULT_CHANGED_MODES][$templateMode][] = $listingProductId;
    }

    //#############################################

    private function createNewSellingTemplate($sellingTemplateId, $shippingTemplateId)
    {
        $generalSellingTable = $this->getInstaller()->getTable('m2epro_template_selling_format');
        $sellingTable = $this->getInstaller()->getTable('m2epro_ebay_template_selling_format');
        $shippingTable = $this->getInstaller()->getTable('m2epro_ebay_template_shipping');

        $this->getInstaller()->getConnection()->multi_query(<<<SQL
INSERT INTO `{$generalSellingTable}`
    (`title`, `component_mode`, `update_date`, `create_date`)
SELECT CONCAT(`{$generalSellingTable}`.`title`,
              IF (`{$shippingTable}`.`title` <> '' AND `{$generalSellingTable}`.`title` <> '',
                  CONCAT(' (',`{$shippingTable}`.`title`,')'),
                  '')),
       `{$generalSellingTable}`.`component_mode`,
       `{$generalSellingTable}`.`update_date`,
       `{$generalSellingTable}`.`create_date`
FROM `{$generalSellingTable}`
INNER JOIN `{$shippingTable}`
    ON `{$shippingTable}`.`id` = {$shippingTemplateId}
WHERE `{$generalSellingTable}`.`id` = {$sellingTemplateId}
SQL
);
        $newTemplateId = (int)$this->getInstaller()->getConnection()->lastInsertId();

        $this->getInstaller()->getConnection()->multi_query(<<<SQL
INSERT INTO `{$sellingTable}`
SELECT {$newTemplateId},
       `{$sellingTable}`.`is_custom_template`,
       `{$sellingTable}`.`listing_type`,
       `{$sellingTable}`.`listing_type_attribute`,
       `{$sellingTable}`.`listing_is_private`,
       `{$sellingTable}`.`duration_mode`,
       `{$sellingTable}`.`duration_attribute`,
       `{$sellingTable}`.`out_of_stock_control`,
       `{$sellingTable}`.`qty_mode`,
       `{$sellingTable}`.`qty_custom_value`,
       `{$sellingTable}`.`qty_custom_attribute`,
       `{$sellingTable}`.`qty_max_posted_value_mode`,
       `{$sellingTable}`.`qty_max_posted_value`,
       `{$shippingTable}`.`vat_percent`,
       `{$shippingTable}`.`tax_table_mode`,
       `{$shippingTable}`.`tax_category_mode`,
       `{$shippingTable}`.`tax_category_value`,
       `{$shippingTable}`.`tax_category_attribute`,
       `{$sellingTable}`.`price_increase_vat_percent`,
       `{$sellingTable}`.`price_variation_mode`,
       `{$sellingTable}`.`start_price_mode`,
       `{$sellingTable}`.`start_price_coefficient`,
       `{$sellingTable}`.`start_price_custom_attribute`,
       `{$sellingTable}`.`reserve_price_mode`,
       `{$sellingTable}`.`reserve_price_coefficient`,
       `{$sellingTable}`.`reserve_price_custom_attribute`,
       `{$sellingTable}`.`buyitnow_price_mode`,
       `{$sellingTable}`.`buyitnow_price_coefficient`,
       `{$sellingTable}`.`buyitnow_price_custom_attribute`,
       `{$sellingTable}`.`price_discount_stp_mode`,
       `{$sellingTable}`.`price_discount_stp_attribute`,
       `{$sellingTable}`.`price_discount_stp_type`,
       `{$sellingTable}`.`best_offer_mode`,
       `{$sellingTable}`.`best_offer_accept_mode`,
       `{$sellingTable}`.`best_offer_accept_value`,
       `{$sellingTable}`.`best_offer_accept_attribute`,
       `{$sellingTable}`.`best_offer_reject_mode`,
       `{$sellingTable}`.`best_offer_reject_value`,
       `{$sellingTable}`.`best_offer_reject_attribute`,
       `{$sellingTable}`.`charity`,
       `{$sellingTable}`.`ignore_variations`
FROM `{$sellingTable}`
INNER JOIN `{$shippingTable}`
    ON `{$shippingTable}`.`id` = {$shippingTemplateId}
WHERE `{$sellingTable}`.`template_selling_format_id` = {$sellingTemplateId}
SQL
);
        return $newTemplateId;
    }

    //#############################################

    private function arrayReplaceRecursive($base, $replacements)
    {
        $args = func_get_args();
        foreach (array_slice($args, 1) as $replacements) {

            $bref_stack = array(&$base);
            $head_stack = array($replacements);

            do {
                end($bref_stack);

                $bref = &$bref_stack[key($bref_stack)];
                $head = array_pop($head_stack);

                unset($bref_stack[key($bref_stack)]);

                foreach (array_keys($head) as $key) {

                    if (isset($key, $bref, $bref[$key]) && is_array($bref[$key]) && is_array($head[$key])) {
                        $bref_stack[] = &$bref[$key];
                        $head_stack[] = $head[$key];
                    } else {
                        $bref[$key] = $head[$key];
                    }

                }
            } while(count($head_stack));
        }

        return $base;
    }

    //#############################################
}

//#############################################