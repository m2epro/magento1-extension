<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

// TransactionalLocks
//########################################

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `m2epro_lock_transactional` (
   `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
   `nick` VARCHAR(255) NOT NULL,
   `create_date` DATETIME DEFAULT NULL,
   PRIMARY KEY (`id`),
   INDEX `nick` (`nick`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

// ArchivedEntity
//########################################

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `m2epro_archived_entity` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `origin_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `data` LONGTEXT NOT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `origin_id__name` (`origin_id`, `name`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
    );

//----------------------------------------

$installer->getMainConfigModifier()->insert(
    '/cron/task/archive_orders_entities/', 'mode', '1', '0 - disable, \r\n1 - enable'
);
$installer->getMainConfigModifier()->insert(
    '/cron/task/archive_orders_entities/', 'interval', '3600', 'in seconds'
);
$installer->getMainConfigModifier()->insert(
    '/cron/task/archive_orders_entities/', 'last_access', NULL, 'date of last access'
);
$installer->getMainConfigModifier()->insert(
    '/cron/task/archive_orders_entities/', 'last_run', NULL, 'date of last run'
);

// OrdersGridIndexes
//########################################

$installer->getTableModifier('amazon_order')->addIndex('purchase_create_date');
$installer->getTableModifier('ebay_order')->addIndex('purchase_create_date');
$installer->getTableModifier('buy_order')->addIndex('purchase_create_date');

// Price Convert
//########################################

$installer->getMainConfigModifier()->insert(
    '/magento/attribute/', 'price_type_converting', '0', '0 - disable, \r\n1 - enable'
);

// Recommended value specifics migration
//########################################

$listingTable = $installer->getTablesObject()->getFullName('listing');
$listingsStmt = $installer->getConnection()->query("
  SELECT * FROM {$listingTable} WHERE `additional_data` LIKE '%mode_same_category_data%';
");

while ($listing = $listingsStmt->fetch(PDO::FETCH_ASSOC)) {

    $listingId = $listing['id'];
    $additionalData = (array)@json_decode($listing['additional_data'], true);
    $hasOldStructure = false;

    if (!empty($additionalData['mode_same_category_data']['specifics'])) {

        foreach ($additionalData['mode_same_category_data']['specifics'] as &$specific) {

            if (!empty($specific['value_ebay_recommended'])) {

                $recommendedValues = (array)@json_decode($specific['value_ebay_recommended'], true);

                if (empty($recommendedValues)) {
                    continue;
                }

                foreach ($recommendedValues as &$recommendedValue) {
                    if (!empty($recommendedValue['value'])) {
                        $recommendedValue = $recommendedValue['value'];
                        $hasOldStructure = true;
                    }
                }
                unset($recommendedValue);

                $specific['value_ebay_recommended'] = json_encode($recommendedValues);
            }
        }
        unset($specific);
    }

    if (!$hasOldStructure) {
        continue;
    }

    $connection->update(
        $listingTable,
        array('additional_data' => json_encode($additionalData)),
        array('id = ?' => $listingId)
    );
}

// Ebay kType Spain
//########################################

$installer->run(<<<SQL
    UPDATE `{$installer->getTable('m2epro_ebay_marketplace')}`
    SET `is_ktype` = 1
    WHERE `marketplace_id` = 13;
SQL
);

// Amazon Business
//########################################

/*
    INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/amazon/business/', 'mode', '0', '0 - disable, \r\n1 - enable',
     '2013-05-08 00:00:00', '2013-05-08 00:00:00');
 */

$installer->getMainConfigModifier()->insert('/amazon/business/', 'mode', '0', '0 - disable, \r\n1 - enable');

//########################################

$installer->getTableModifier('amazon_marketplace')
    ->addColumn(
        'is_business_available', 'tinyint(2) UNSIGNED NOT NULL', 0, 'is_merchant_fulfillment_available', true, false
    )
    ->addColumn(
        'is_vat_calculation_service_available', 'tinyint(2) UNSIGNED NOT NULL', 0, 'is_business_available', true, false
    )
    ->addColumn(
        'is_product_tax_code_policy_available', 'tinyint(2) UNSIGNED NOT NULL', 0,
        'is_vat_calculation_service_available', true, false
    )
    ->commit();

$connection->update(
    $installer->getTablesObject()->getFullName('amazon_marketplace'),
    array('is_business_available' => 1),
    array('marketplace_id IN (?)' => array(25, 28, 29)) // DE, UK, US
);

$connection->update(
    $installer->getTablesObject()->getFullName('amazon_marketplace'),
    array('is_vat_calculation_service_available' => 1),
    array('marketplace_id IN (?)' => array(25, 26, 28, 30, 31)) // Europe
);

$connection->update(
    $installer->getTablesObject()->getFullName('amazon_marketplace'),
    array('is_product_tax_code_policy_available' => 1),
    array('marketplace_id IN (?)' => array(25, 28)) // DE, UK
);

$installer->getTableModifier('amazon_account')
    ->addColumn(
        'is_vat_calculation_service_enabled', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'magento_orders_settings', false, false
    )
    ->addColumn(
        'is_magento_invoice_creation_disabled', 'TINYINT(2) UNSIGNED NOT NULL', 0,
        'is_vat_calculation_service_enabled', false, false
    )
    ->commit();

//########################################

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `m2epro_amazon_template_product_tax_code` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `product_tax_code_mode` TINYINT(2) NOT NULL,
    `product_tax_code_value` VARCHAR(255) DEFAULT NULL,
    `product_tax_code_attribute` VARCHAR(255) DEFAULT NULL,
    `update_date` DATETIME DEFAULT NULL,
    `create_date` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_template_selling_format_business_discount` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
    `qty` INT(11) UNSIGNED NOT NULL,
    `mode` TINYINT(2) UNSIGNED NOT NULL,
    `attribute` VARCHAR(255) DEFAULT NULL,
    `coefficient` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `template_selling_format_id` (`template_selling_format_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_indexer_listing_product_parent`;

CREATE TABLE IF NOT EXISTS `m2epro_ebay_indexer_listing_product_parent` (
    `listing_product_id` INT(11) UNSIGNED NOT NULL,
    `listing_id` INT(11) UNSIGNED NOT NULL,
    `component_mode` VARCHAR(10) DEFAULT NULL,
    `min_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `max_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `create_date` DATETIME NOT NULL,
    PRIMARY KEY (`listing_product_id`),
    INDEX `listing_id` (`listing_id`),
    INDEX `component_mode` (`component_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `m2epro_amazon_indexer_listing_product_parent` (
    `listing_product_id` INT(11) UNSIGNED NOT NULL,
    `listing_id` INT(11) UNSIGNED NOT NULL,
    `component_mode` VARCHAR(10) DEFAULT NULL,
    `min_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `max_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `min_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `max_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `create_date` DATETIME NOT NULL,
    PRIMARY KEY (`listing_product_id`),
    INDEX `listing_id` (`listing_id`),
    INDEX `component_mode` (`component_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

//########################################

/*
    ALTER TABLE `m2epro_amazon_listing_product`
    CHANGE COLUMN `online_price` `online_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    CHANGE COLUMN `online_sale_price` `online_regular_sale_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    CHANGE COLUMN `online_sale_price_start_date` `online_regular_sale_price_start_date` DATETIME DEFAULT NULL,
    CHANGE COLUMN `online_sale_price_end_date` `online_regular_sale_price_end_date` DATETIME DEFAULT NULL,
    ADD COLUMN `template_product_tax_code_id` INT(11) UNSIGNED DEFAULT NULL AFTER `template_shipping_override_id`,
    ADD COLUMN `online_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL AFTER `online_regular_sale_price_end_date`,
    ADD COLUMN `online_business_discounts` TEXT DEFAULT NULL AFTER `online_business_price`,
    DROP INDEX `online_price`,
    DROP INDEX `online_sale_price`,
    ADD INDEX `online_regular_price` (`online_regular_price`),
    ADD INDEX `online_regular_sale_price` (`online_regular_sale_price`),
    ADD INDEX `template_product_tax_code_id` (`template_product_tax_code_id`),
    ADD INDEX `online_business_price` (`online_business_price`);
 */

$installer->getTableModifier('amazon_listing_product')
    ->renameColumn(
        'online_price', 'online_regular_price', true, false
    )
    ->renameColumn(
        'online_sale_price', 'online_regular_sale_price', true, false
    )
    ->renameColumn(
        'online_sale_price_start_date', 'online_regular_sale_price_start_date', true, false
    )
    ->renameColumn(
        'online_sale_price_end_date', 'online_regular_sale_price_end_date', true, false
    )
    ->addColumn(
        'template_product_tax_code_id', 'INT(11) UNSIGNED', 'NULL', 'template_shipping_override_id', true, false
    )
    ->addColumn(
        'online_business_price', 'DECIMAL(12, 4) UNSIGNED', 'NULL', 'online_regular_sale_price_end_date', true, false
    )
    ->addColumn(
        'online_business_discounts', 'TEXT', 'NULL', 'online_business_price', false, false
    )
    ->commit();

//########################################

/*
    ALTER TABLE `m2epro_amazon_template_selling_format`
    CHANGE COLUMN `price_mode` `regular_price_mode` TINYINT(2) UNSIGNED NOT NULL,
    CHANGE COLUMN `price_custom_attribute` `regular_price_custom_attribute` VARCHAR(255) NOT NULL,
    CHANGE COLUMN `price_coefficient` `regular_price_coefficient` VARCHAR(255) NOT NULL,
    CHANGE COLUMN `map_price_mode` `regular_map_price_mode` TINYINT(2) UNSIGNED NOT NULL,
    CHANGE COLUMN `map_price_custom_attribute` `regular_map_price_custom_attribute` VARCHAR(255) NOT NULL,
    CHANGE COLUMN `sale_price_mode` `regular_sale_price_mode` TINYINT(2) UNSIGNED NOT NULL,
    CHANGE COLUMN `sale_price_custom_attribute` `regular_sale_price_custom_attribute` VARCHAR(255) NOT NULL,
    CHANGE COLUMN `sale_price_coefficient` `regular_sale_price_coefficient` VARCHAR(255) NOT NULL,
    CHANGE COLUMN `price_variation_mode` `regular_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
    CHANGE COLUMN `sale_price_start_date_mode` `regular_sale_price_start_date_mode` TINYINT(2) UNSIGNED NOT NULL,
    CHANGE COLUMN `sale_price_start_date_value` `regular_sale_price_start_date_value` DATETIME NOT NULL,
    CHANGE COLUMN `sale_price_start_date_custom_attribute`
                  `regular_sale_price_start_date_custom_attribute` VARCHAR(255) NOT NULL,
    CHANGE COLUMN `sale_price_end_date_mode` `regular_sale_price_end_date_mode` TINYINT(2) UNSIGNED NOT NULL,
    CHANGE COLUMN `sale_price_end_date_value` `regular_sale_price_end_date_value` DATETIME NOT NULL,
    CHANGE COLUMN `sale_price_end_date_custom_attribute`
                  `regular_sale_price_end_date_custom_attribute` VARCHAR(255) NOT NULL,
    CHANGE COLUMN `price_vat_percent` `regular_price_vat_percent` FLOAT UNSIGNED,
    ADD COLUMN `is_regular_customer_allowed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `qty_max_posted_value`,
    ADD COLUMN `is_business_customer_allowed`
               TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_regular_customer_allowed`,
    ADD COLUMN `business_price_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `regular_price_vat_percent`,
    ADD COLUMN `business_price_custom_attribute` VARCHAR(255) NOT NULL AFTER `business_price_mode`,
    ADD COLUMN `business_price_coefficient` VARCHAR(255) NOT NULL AFTER `business_price_custom_attribute`,
    ADD COLUMN `business_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `business_price_coefficient`,
    ADD COLUMN `business_price_vat_percent` FLOAT UNSIGNED NOT NULL DEFAULT 0 AFTER `business_price_variation_mode`,
    ADD COLUMN `business_discounts_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `business_price_vat_percent`,
    ADD COLUMN `business_discounts_tier_coefficient` VARCHAR(255) NOT NULL AFTER `business_discounts_mode`,
    ADD COLUMN `business_discounts_tier_customer_group_id`
               INT(11) UNSIGNED DEFAULT NULL AFTER `business_discounts_tier_coefficient`,
    DROP INDEX `price_variation_mode`;
 */

$installer->getTableModifier('amazon_template_selling_format')
    ->renameColumn(
        'price_mode', 'regular_price_mode', false, false
    )
    ->renameColumn(
        'price_custom_attribute', 'regular_price_custom_attribute', false, false
    )
    ->renameColumn(
        'price_coefficient', 'regular_price_coefficient', false, false
    )
    ->renameColumn(
        'map_price_mode', 'regular_map_price_mode', false, false
    )
    ->renameColumn(
        'map_price_custom_attribute', 'regular_map_price_custom_attribute', false, false
    )
    ->renameColumn(
        'sale_price_mode', 'regular_sale_price_mode', false, false
    )
    ->renameColumn(
        'sale_price_custom_attribute', 'regular_sale_price_custom_attribute', false, false
    )
    ->renameColumn(
        'sale_price_coefficient', 'regular_sale_price_coefficient', false, false
    )
    ->renameColumn(
        'price_variation_mode', 'regular_price_variation_mode', false, false
    )
    ->renameColumn(
        'sale_price_start_date_mode', 'regular_sale_price_start_date_mode', false, false
    )
    ->renameColumn(
        'sale_price_start_date_value', 'regular_sale_price_start_date_value', false, false
    )
    ->renameColumn(
        'sale_price_start_date_custom_attribute', 'regular_sale_price_start_date_custom_attribute', false, false
    )
    ->renameColumn(
        'sale_price_end_date_mode', 'regular_sale_price_end_date_mode', false, false
    )
    ->renameColumn(
        'sale_price_end_date_value', 'regular_sale_price_end_date_value', false, false
    )
    ->renameColumn(
        'sale_price_end_date_custom_attribute', 'regular_sale_price_end_date_custom_attribute', false, false
    )
    ->renameColumn(
        'price_vat_percent', 'regular_price_vat_percent', false, false
    )
    ->addColumn(
        'is_regular_customer_allowed', 'TINYINT(2) UNSIGNED NOT NULL', 1, 'qty_max_posted_value', false, false
    )
    ->addColumn(
        'is_business_customer_allowed', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_regular_customer_allowed', false, false
    )
    ->addColumn(
        'business_price_mode', 'TINYINT(2) UNSIGNED NOT NULL', NULL, 'regular_price_vat_percent', false, false
    )
    ->addColumn(
        'business_price_custom_attribute', 'VARCHAR(255) NOT NULL', NULL, 'business_price_mode', false, false
    )
    ->addColumn(
        'business_price_coefficient', 'VARCHAR(255) NOT NULL', NULL, 'business_price_custom_attribute', false, false
    )
    ->addColumn(
        'business_price_variation_mode', 'TINYINT(2) UNSIGNED NOT NULL',
        NULL, 'business_price_coefficient', false, false
    )
    ->addColumn(
        'business_price_vat_percent', 'FLOAT UNSIGNED', 'NULL', 'business_price_variation_mode', false, false
    )
    ->addColumn(
        'business_discounts_mode', 'TINYINT(2) UNSIGNED NOT NULL',
        NULL, 'business_price_vat_percent', false, false
    )
    ->addColumn(
        'business_discounts_tier_coefficient', 'VARCHAR(255) NOT NULL',
        NULL, 'business_discounts_mode', false, false
    )
    ->addColumn(
        'business_discounts_tier_customer_group_id', 'INT(11) UNSIGNED',
        'NULL', 'business_discounts_tier_coefficient', false, false
    )
    ->dropIndex('price_variation_mode', false)
    ->commit();

$installer->getTableModifier('amazon_template_selling_format')
    ->changeColumn('regular_price_vat_percent', 'FLOAT UNSIGNED', 'NULL');

//########################################

/*
    ALTER TABLE `m2epro_amazon_order`
    ADD COLUMN `is_business` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_prime`,
    ADD INDEX `is_business` (`is_business`);
 */

$installer->getTableModifier('amazon_order')
    ->addColumn('is_business', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_prime', true);

//########################################

/*
    ALTER TABLE `m2epro_amazon_template_synchronization`
    ADD COLUMN `revise_change_product_tax_code_template`
               tinyint(2) UNSIGNED NOT NULL AFTER `revise_change_shipping_template`;
 */

$installer->getTableModifier('amazon_template_synchronization')->addColumn(
    'revise_change_product_tax_code_template', 'tinyint(2) UNSIGNED NOT NULL', NULL, 'revise_change_shipping_template'
);

//########################################

$processingTable = $installer->getTablesObject()->getFullName('processing');

$processingsStmt = $installer->getConnection()->query("
    SELECT * FROM {$processingTable}
    WHERE `model` LIKE 'M2ePro/Amazon_Connector_Product_%' OR
          `model` LIKE 'M2ePro/Ebay_Connector_Item_%' OR
          `model` LIKE 'M2ePro/Buy_Connector_Product_%';
");

while ($processing = $processingsStmt->fetch(PDO::FETCH_ASSOC)) {

    if (empty($processing['params'])) {
        continue;
    }

    $params = (array)@json_decode($processing['params'], true);
    if (!isset($params['responser_params']['products']) && !isset($params['responser_params']['product'])) {
        continue;
    }

    if (isset($params['responser_params']['products'])) {
        $productsData = (array)$params['responser_params']['products'];
    } else {
        $productsData = array($params['responser_params']['product']);
    }

    $isDataChanged = false;

    foreach ($productsData as &$productData) {
        if (!isset($productData['configurator']['mode'])) {
            continue;
        }

        $isDataChanged = true;

        if ($productData['configurator']['mode'] == 'full') {
            $productData['configurator']['is_default_mode'] = true;
        } else {
            $productData['configurator']['is_default_mode'] = false;
        }

        unset($productData['configurator']['mode']);

        if (strpos($processing['model'], 'Amazon') === false ||
            !isset($productData['configurator']['allowed_data_types'])
        ) {
            continue;
        }

        $allowedDataTypes = $productData['configurator']['allowed_data_types'];

        $priceDataTypeIndex = array_search('price', $allowedDataTypes);

        if ($priceDataTypeIndex === false) {
            continue;
        }

        unset($allowedDataTypes[$priceDataTypeIndex]);
        $allowedDataTypes[] = 'regular_price';

        $productData['configurator']['allowed_data_types'] = $allowedDataTypes;
    }

    if (!$isDataChanged) {
        continue;
    }

    if (isset($params['responser_params']['products'])) {
        $params['responser_params']['products'] = $productsData;
    } else {
        $params['responser_params']['product'] = reset($productsData);
    }

    $connection->update(
        $processingTable,
        array('params' => json_encode($params)),
        array('id = ?' => $processing['id'])
    );
}

//########################################

$productActionsUpgrader = new ProductActionsUpgrader($installer, $connection);
$productActionsUpgrader->run();

class ProductActionsUpgrader
{
    const BACKUP_TABLE_SUFFIX             = '_b';
    const BACKUP_TABLE_IDENTIFIER_MAX_LEN = 20;

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup $installer */
    private $installer  = NULL;

    /** @var Varien_Db_Adapter_Pdo_Mysql $connection */
    private $connection = NULL;

    //########################################

    public function __construct(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer, Varien_Db_Adapter_Pdo_Mysql $connection)
    {
        $this->installer  = $installer;
        $this->connection = $connection;
    }

    //########################################

    public function run()
    {
        if ($this->isCompleted()) {
            return;
        }

        if (!$this->isMovedToBackup()) {
            $this->moveToBackup();
            $this->markAsMovedToBackup();
        }

        $this->prepareStructure();

        $processingsStmt = $this->connection->select()
            ->from($this->getBackupTableName('processing'))
            ->query();

        while ($oldProcessingRow = $processingsStmt->fetch()) {

            if (strpos($oldProcessingRow['model'], 'Ebay_Connector_Item') !== false) {
                $this->processEbayItemProcessing($oldProcessingRow);
                continue;
            }

            if (strpos($oldProcessingRow['model'], 'Amazon_Connector_Product') !== false) {
                $this->processAmazonProductProcessing($oldProcessingRow);
                continue;
            }

            if (strpos($oldProcessingRow['model'], 'Amazon_Connector_Order') !== false) {
                $this->processAmazonOrderProcessing($oldProcessingRow);
                continue;
            }

            $newProcessingRow = $oldProcessingRow;
            unset($newProcessingRow['id']);

            $this->connection->insert($this->getTableName('processing'), $newProcessingRow);

            $this->updateProcessingLocks($oldProcessingRow, $this->connection->lastInsertId());
        }

        $this->removeBackup();
    }

    //########################################

    private function moveToBackup()
    {
        // required for correct work $this->connection->createTableByDdl() method
        $this->installer->getTableModifier('processing')->changeColumn(
            'model', 'VARCHAR(255) NOT NULL', NULL, 'id'
        );

        $this->moveTableToBackup('processing');

        $this->moveTableToBackup('ebay_processing_action');
        $this->moveTableToBackup('ebay_processing_action_item');

        $this->moveTableToBackup('amazon_processing_action');
        $this->moveTableToBackup('amazon_processing_action_item');
    }

    private function removeBackup()
    {
        $this->connection->dropTable($this->getBackupTableName('processing'));

        $this->connection->dropTable($this->getBackupTableName('ebay_processing_action'));
        $this->connection->dropTable($this->getBackupTableName('ebay_processing_action_item'));

        $this->connection->dropTable($this->getBackupTableName('amazon_processing_action'));
        $this->connection->dropTable($this->getBackupTableName('amazon_processing_action_item'));
    }

    private function prepareStructure()
    {
        $this->connection->dropTable($this->getTableName('processing'));
        $this->connection->dropTable($this->getTableName('ebay_processing_action'));
        $this->connection->dropTable($this->getTableName('amazon_processing_action'));

        if (!$this->installer->getTablesObject()->isExists('processing')) {
            $this->installer->run(<<<SQL

CREATE TABLE `m2epro_processing` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `model` VARCHAR(255) NOT NULL,
  `params` LONGTEXT DEFAULT NULL,
  `result_data` LONGTEXT DEFAULT NULL,
  `result_messages` LONGTEXT DEFAULT NULL,
  `is_completed` TINYINT(2) NOT NULL DEFAULT 0,
  `expiration_date` DATETIME NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `model` (`model`),
  INDEX `is_completed` (`is_completed`),
  INDEX `expiration_date` (`expiration_date`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        if (!$this->installer->getTablesObject()->isExists('ebay_processing_action')) {
            $this->installer->run(<<<SQL

CREATE TABLE `m2epro_ebay_processing_action` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `related_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `priority` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `request_timeout` INT(11) UNSIGNED DEFAULT NULL,
  `request_data` LONGTEXT NOT NULL,
  `start_date` DATETIME DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `related_id` (`related_id`),
  INDEX `type` (`type`),
  INDEX `priority` (`priority`),
  INDEX `start_date` (`start_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        if (!$this->installer->getTablesObject()->isExists('amazon_processing_action')) {
            $this->installer->run(<<<SQL

CREATE TABLE `m2epro_amazon_processing_action` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `request_pending_single_id` INT(11) UNSIGNED DEFAULT NULL,
  `related_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `request_data` LONGTEXT NOT NULL,
  `start_date` DATETIME DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `request_pending_single_id` (`request_pending_single_id`),
  INDEX `related_id` (`related_id`),
  INDEX `type` (`type`),
  INDEX `start_date` (`start_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        $this->installer->getTableModifier('listing_product')->addColumn(
            'need_synch_rules_check', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'tried_to_list', true
        );

        $this->connection->dropTable($this->getTableName('ebay_processing_action_item'));
        $this->connection->dropTable($this->getTableName('amazon_processing_action_item'));
    }

    //########################################

    private function processEbayItemProcessing(array $oldProcessingRow)
    {
        if (!empty($oldProcessingRow['is_completed'])) {
            $isMultiple = strpos($oldProcessingRow['model'], 'Multiple') !== false;
            $oldProcessingParams = json_decode($oldProcessingRow['params'], true);

            if (!$isMultiple) {
                $listingsProductsIds = array($oldProcessingParams['listing_product_id']);
            } else {
                $listingsProductsIds = array_keys($oldProcessingParams['request_data']['items']);
            }

            foreach ($listingsProductsIds as $listingProductId) {
                $this->connection->insert(
                    $this->getTableName('processing'),
                    $this->prepareEbayItemProcessingData($oldProcessingRow, $listingProductId)
                );

                $this->updateProcessingLocks(
                    $oldProcessingRow, $this->connection->lastInsertId(), $listingProductId
                );
            }
        } else {
            $processingActionBackupTable = $this->getBackupTableName('ebay_processing_action');
            $processingActionItemBackupTable = $this->getBackupTableName('ebay_processing_action_item');
            $oldActionsData = $this->connection->query("
              SELECT `epab`.`account_id` AS `account_id`,
                     `epab`.`marketplace_id` AS `marketplace_id`,
                     `epab`.`type` AS `action_type`,
                     `epab`.`request_timeout` AS `request_timeout`,
                     `epab`.`update_date` AS `update_date`,
                     `epab`.`create_date` AS `create_date`,
                     `epaib`.`related_id` AS `related_id`,
                     `epaib`.`input_data` AS `input_data`,
                     `epaib`.`is_skipped` AS `is_skipped`
              FROM `{$processingActionItemBackupTable}` AS `epaib`
              LEFT JOIN `{$processingActionBackupTable}` AS `epab` ON `epab`.`id` = `epaib`.`action_id`
              WHERE `epab`.`processing_id` = {$oldProcessingRow['id']}
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($oldActionsData as $oldActionData) {
                $this->connection->insert(
                    $this->getTableName('processing'),
                    $this->prepareEbayItemProcessingData($oldProcessingRow, $oldActionData['related_id'])
                );

                $newProcessingId = $this->connection->lastInsertId();

                $this->updateProcessingLocks(
                    $oldProcessingRow, $newProcessingId, $oldActionData['related_id']
                );

                $newActionData = array(
                    'processing_id'   => $newProcessingId,
                    'account_id'      => $oldActionData['account_id'],
                    'marketplace_id'  => $oldActionData['marketplace_id'],
                    'related_id'      => $oldActionData['related_id'],
                    'type'            => $oldActionData['action_type'],
                    'request_timeout' => $oldActionData['request_timeout'],
                    'request_data'    => $oldActionData['input_data'],
                    'start_date'      => $oldActionData['create_date'],
                    'update_date'     => $oldActionData['update_date'],
                    'create_date'     => $oldActionData['create_date'],
                );

                $this->connection->insert($this->getTableName('ebay_processing_action'), $newActionData);

                if (!empty($oldActionData['is_skipped'])) {
                    $this->connection->update(
                        $this->getTableName('listing_product'),
                        array('need_synch_rules_check' => 1),
                        array('id = ?' => $oldActionData['related_id'])
                    );
                }
            }
        }

        $this->connection->delete(
            $this->getTableName('processing_lock'),
            array('processing_id = ?' => $oldProcessingRow['id'])
        );
    }

    private function processAmazonProductProcessing(array $oldProcessingRow)
    {
        if (!empty($oldProcessingRow['is_completed'])) {
            $oldProcessingParams = json_decode($oldProcessingRow['params'], true);
            $listingsProductsIds = array_keys($oldProcessingParams['request_data']['items']);

            foreach ($listingsProductsIds as $listingProductId) {
                $this->connection->insert(
                    $this->getTableName('processing'),
                    $this->prepareAmazonProductProcessingData($oldProcessingRow, $listingProductId)
                );

                $this->updateProcessingLocks(
                    $oldProcessingRow, $this->connection->lastInsertId(), $listingProductId
                );
            }
        } else {
            $processingActionBackupTable = $this->getBackupTableName('amazon_processing_action');
            $processingActionItemBackupTable = $this->getBackupTableName('amazon_processing_action_item');

            $oldActionsData = $this->connection->query("
              SELECT `apab`.`account_id` AS `account_id`,
                     `apab`.`type` AS `action_type`,
                     `apab`.`update_date` AS `update_date`,
                     `apab`.`create_date` AS `create_date`,
                     `apaib`.`request_pending_single_id` AS `request_pending_single_id`,
                     `apaib`.`related_id` AS `related_id`,
                     `apaib`.`input_data` AS `input_data`,
                     `apaib`.`output_data` AS `output_data`,
                     `apaib`.`output_messages` AS `output_messages`,
                     `apaib`.`is_skipped` AS `is_skipped`
              FROM `{$processingActionItemBackupTable}` AS `apaib`
              LEFT JOIN `{$processingActionBackupTable}` AS `apab` ON `apab`.`id` = `apaib`.`action_id`
              WHERE `apab`.`processing_id` = {$oldProcessingRow['id']}
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($oldActionsData as $oldActionData) {
                $listingProductId = $oldActionData['related_id'];

                $newProcessingData = $this->prepareAmazonProductProcessingData(
                    $oldProcessingRow, $oldActionData['related_id']
                );

                if (is_null($newProcessingData['result_data']) &&
                    !empty($oldActionData['is_completed']) &&
                    !empty($oldActionData['output_messages'])
                ) {
                    $newProcessingData['result_data'] = array(
                        'messages' => json_decode($oldActionData['output_messages'], true),
                    );

                    if (!empty($oldActionData['output_data'])) {
                        $newProcessingData['result_data'] = array_merge(
                            json_decode($oldActionData['output_data'], true), $newProcessingData['result_data']
                        );
                    }
                }

                $this->connection->insert($this->getTableName('processing'), $newProcessingData);

                $newProcessingId = $this->connection->lastInsertId();

                $this->updateProcessingLocks($oldProcessingRow, $newProcessingId, $oldActionData['related_id']);

                $newActionData = array(
                    'processing_id'             => $newProcessingId,
                    'account_id'                => $oldActionData['account_id'],
                    'request_pending_single_id' => $oldActionData['request_pending_single_id'],
                    'related_id'                => $listingProductId,
                    'type'                      => $oldActionData['action_type'],
                    'request_data'              => $oldActionData['input_data'],
                    'start_date'                => $oldActionData['create_date'],
                    'update_date'               => $oldActionData['update_date'],
                    'create_date'               => $oldActionData['create_date'],
                );

                $this->connection->insert($this->getTableName('amazon_processing_action'), $newActionData);

                if (!empty($oldActionData['is_skipped'])) {
                    $this->connection->update(
                        $this->getTableName('listing_product'),
                        array('need_synch_rules_check' => 1),
                        array('id = ?' => $listingProductId)
                    );
                }
            }
        }

        $this->connection->delete(
            $this->getTableName('processing_lock'),
            array('processing_id = ?' => $oldProcessingRow['id'])
        );
    }

    private function processAmazonOrderProcessing(array $oldProcessingRow)
    {
        if (!empty($oldProcessingRow['is_completed'])) {
            $oldProcessingParams = json_decode($oldProcessingRow['params'], true);
            if (isset($oldProcessingParams['request_data']['items'])) {
                $changesIds = array_keys($oldProcessingParams['request_data']['items']);
            } else {
                $changesIds = array_keys($oldProcessingParams['request_data']['orders']);
            }

            foreach ($changesIds as $changeId) {
                $newProcessingData = $this->prepareAmazonOrderProcessingData($oldProcessingRow, $changeId);

                $this->connection->insert($this->getTableName('processing'), $newProcessingData);

                $newProcessingParams = json_decode($newProcessingData['params'], true);
                $this->updateProcessingLocks(
                    $oldProcessingRow, $this->connection->lastInsertId(), $newProcessingParams['order_id']
                );
            }
        } else {
            $processingActionBackupTable = $this->getBackupTableName('amazon_processing_action');
            $processingActionItemBackupTable = $this->getBackupTableName('amazon_processing_action_item');

            $oldActionsData = $this->connection->query("
              SELECT `apab`.`account_id` AS `account_id`,
                     `apab`.`type` AS `action_type`,
                     `apab`.`update_date` AS `update_date`,
                     `apab`.`create_date` AS `create_date`,
                     `apaib`.`request_pending_single_id` AS `request_pending_single_id`,
                     `apaib`.`related_id` AS `related_id`,
                     `apaib`.`input_data` AS `input_data`,
                     `apaib`.`output_data` AS `output_data`,
                     `apaib`.`output_messages` AS `output_messages`,
                     `apaib`.`is_skipped` AS `is_skipped`
              FROM `{$processingActionItemBackupTable}` AS `apaib`
              LEFT JOIN `{$processingActionBackupTable}` AS `apab` ON `apab`.`id` = `apaib`.`action_id`
              WHERE `apab`.`processing_id` = {$oldProcessingRow['id']}
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($oldActionsData as $oldActionData) {
                $newProcessingData = $this->prepareAmazonOrderProcessingData(
                    $oldProcessingRow, $oldActionData['related_id']
                );

                if (is_null($newProcessingData['result_data']) &&
                    !empty($oldActionData['is_completed']) &&
                    !empty($oldActionData['output_messages'])
                ) {
                    $newProcessingData['result_data'] = array(
                        'messages' => json_decode($oldActionData['output_messages'], true),
                    );

                    if (!empty($oldActionData['output_data'])) {
                        $newProcessingData['result_data'] = array_merge(
                            json_decode($oldActionData['output_data'], true), $newProcessingData['result_data']
                        );
                    }
                }

                $this->connection->insert($this->getTableName('processing'), $newProcessingData);

                $newProcessingId = $this->connection->lastInsertId();

                $newProcessingParams = json_decode($newProcessingData['params'], true);
                $this->updateProcessingLocks($oldProcessingRow, $newProcessingId, $newProcessingParams['order_id']);

                $newActionData = array(
                    'processing_id'             => $newProcessingId,
                    'account_id'                => $oldActionData['account_id'],
                    'request_pending_single_id' => $oldActionData['request_pending_single_id'],
                    'related_id'                => $oldActionData['related_id'],
                    'type'                      => $oldActionData['action_type'],
                    'request_data'              => $oldActionData['input_data'],
                    'start_date'                => $oldActionData['create_date'],
                    'update_date'               => $oldActionData['update_date'],
                    'create_date'               => $oldActionData['create_date'],
                );

                $this->connection->insert($this->getTableName('amazon_processing_action'), $newActionData);
            }
        }

        $this->connection->delete(
            $this->getTableName('processing_lock'),
            array('processing_id = ?' => $oldProcessingRow['id'])
        );
    }

    //########################################

    private function prepareEbayItemProcessingData(array $oldProcessingRow, $listingProductId)
    {
        $isMultiple = strpos($oldProcessingRow['model'], 'Multiple') !== false;

        $oldProcessingParams = json_decode($oldProcessingRow['params'], true);
        $oldResponserParams  = $oldProcessingParams['responser_params'];

        if (!$isMultiple) {
            $productResponserData = $oldResponserParams['product'];
        } else {
            $productResponserData = array_merge(
                array('id' => $listingProductId),
                $oldResponserParams['products'][$listingProductId]
            );
        }

        $newResponserParams = array(
            'is_realtime'     => $oldResponserParams['is_realtime'],
            'account_id'      => $oldResponserParams['account_id'],
            'action_type'     => $oldResponserParams['action_type'],
            'lock_identifier' => $oldResponserParams['lock_identifier'],
            'logs_action'     => $oldResponserParams['logs_action'],
            'logs_action_id'  => $oldResponserParams['logs_action_id'],
            'status_changer'  => $oldResponserParams['status_changer'],
            'params'          => $oldResponserParams['params'],
            'product'         => $productResponserData,
        );

        if (!$isMultiple) {
            $processingRequestData = $oldProcessingParams['request_data'];
            $responserModelName    = str_replace('Single', '', $oldProcessingParams['responser_model_name']);
        } else {
            $processingRequestData = $oldProcessingParams['request_data']['items'][$listingProductId];
            $responserModelName    = str_replace('Multiple', '', $oldProcessingParams['responser_model_name']);
        }

        $newProcessingParams = array(
            'component'            => $oldProcessingParams['component'],
            'server_hash'          => $oldProcessingParams['server_hash'],
            'account_id'           => $oldProcessingParams['account_id'],
            'marketplace_id'       => $oldProcessingParams['marketplace_id'],
            'request_data'         => $processingRequestData,
            'listing_product_id'   => $listingProductId,
            'lock_identifier'      => $oldProcessingParams['lock_identifier'],
            'action_type'          => $oldProcessingParams['action_type'],
            'start_date'           => $oldProcessingRow['create_date'],
            'responser_model_name' => $responserModelName,
            'responser_params'     => $newResponserParams,
        );

        $newProcessingResultData = NULL;
        if (!empty($oldProcessingRow['result_data'])) {
            $newProcessingResultData = json_decode($oldProcessingRow['result_data'], true);
            if ($isMultiple) {
                $newProcessingResultData = $newProcessingResultData['result'][$listingProductId];
            }
        }

        return array(
            'model'           => str_replace(array('Single_', 'Multiple_'), array('', ''), $oldProcessingRow['model']),
            'params'          => json_encode($newProcessingParams),
            'is_completed'    => $oldProcessingRow['is_completed'],
            'result_data'     => !is_null($newProcessingResultData) ? json_encode($newProcessingResultData) : NULL,
            'expiration_date' => $oldProcessingRow['expiration_date'],
            'update_date'     => $oldProcessingRow['update_date'],
            'create_date'     => $oldProcessingRow['create_date'],
        );
    }

    private function prepareAmazonProductProcessingData(array $oldProcessingRow, $listingProductId)
    {
        $oldProcessingParams = json_decode($oldProcessingRow['params'], true);
        $oldResponserParams  = $oldProcessingParams['responser_params'];

        $newResponserParams = array(
            'account_id'      => $oldResponserParams['account_id'],
            'action_type'     => $oldResponserParams['action_type'],
            'lock_identifier' => $oldResponserParams['lock_identifier'],
            'logs_action'     => $oldResponserParams['logs_action'],
            'logs_action_id'  => $oldResponserParams['logs_action_id'],
            'status_changer'  => $oldResponserParams['status_changer'],
            'params'          => $oldResponserParams['params'],
            'product'         => array_merge(
                array('id' => $listingProductId),
                $oldResponserParams['products'][$listingProductId]
            ),
        );

        $newProcessingParams = array(
            'component'            => $oldProcessingParams['component'],
            'server_hash'          => $oldProcessingParams['server_hash'],
            'account_id'           => $oldProcessingParams['account_id'],
            'request_data'         => $oldProcessingParams['request_data']['items'][$listingProductId],
            'listing_product_id'   => $listingProductId,
            'lock_identifier'      => $oldProcessingParams['lock_identifier'],
            'action_type'          => $oldProcessingParams['action_type'],
            'start_date'           => $oldProcessingRow['create_date'],
            'responser_model_name' => str_replace('Multiple', '', $oldProcessingParams['responser_model_name']),
            'responser_params'     => $newResponserParams,
        );

        $newProcessingResultData = NULL;
        if (!empty($oldProcessingRow['result_data'])) {
            $oldProcessingResultData = json_decode($oldProcessingRow['result_data'], true);

            $newProcessingResultData = array(
                'messages' => $oldProcessingResultData['messages'][$listingProductId],
            );

            if (isset($oldProcessingResultData['asins'][$listingProductId])) {
                $newProcessingResultData['asins'] = $oldProcessingResultData['asins'][$listingProductId];
            }
        }

        return array(
            'model'           => str_replace('Multiple', '', $oldProcessingRow['model']),
            'params'          => json_encode($newProcessingParams),
            'result_data'     => !is_null($newProcessingResultData) ? json_encode($newProcessingResultData) : NULL,
            'is_completed'    => $oldProcessingRow['is_completed'],
            'expiration_date' => $oldProcessingRow['expiration_date'],
            'update_date'     => $oldProcessingRow['update_date'],
            'create_date'     => $oldProcessingRow['create_date'],
        );
    }

    private function prepareAmazonOrderProcessingData(array $oldProcessingRow, $changeId)
    {
        $oldProcessingParams = json_decode($oldProcessingRow['params'], true);
        $oldResponserParams  = $oldProcessingParams['responser_params'];

        $orderId = NULL;
        foreach ($oldResponserParams as $responserParamsChangeId => $orderResponserParams) {
            if ($responserParamsChangeId != $changeId) {
                continue;
            }

            $orderId = $orderResponserParams['order_id'];
            break;
        }

        $newResponserParams = array(
            'order' => $oldResponserParams[$changeId],
        );

        $newProcessingParams = array(
            'component'            => $oldProcessingParams['component'],
            'server_hash'          => $oldProcessingParams['server_hash'],
            'account_id'           => $oldProcessingParams['account_id'],
            'request_data'         => isset($oldProcessingParams['request_data']['items'])
                ? $oldProcessingParams['request_data']['items'][$changeId]
                : $oldProcessingParams['request_data']['orders'][$changeId],
            'order_id'             => $orderId,
            'responser_model_name' => $oldProcessingParams['responser_model_name'],
            'responser_params'     => $newResponserParams,
        );

        $newProcessingResultData = NULL;

        if (!empty($oldProcessingRow['result_data'])) {
            $oldProcessingResultData = json_decode($oldProcessingRow['result_data'], true);

            $newProcessingResultData = array(
                'messages' => $oldProcessingResultData['messages'][$changeId],
            );
        }

        return array(
            'model'           => $oldProcessingRow['model'],
            'params'          => json_encode($newProcessingParams),
            'result_data'     => !is_null($newProcessingResultData) ? json_encode($newProcessingResultData) : NULL,
            'is_completed'    => $oldProcessingRow['is_completed'],
            'expiration_date' => $oldProcessingRow['expiration_date'],
            'update_date'     => $oldProcessingRow['update_date'],
            'create_date'     => $oldProcessingRow['create_date'],
        );
    }

    //########################################

    private function isCompleted()
    {
        return !$this->connection->isTableExists($this->getTableName('ebay_processing_action_item')) &&
               !$this->connection->isTableExists($this->getBackupTableName('processing'));
    }

    //########################################

    private function isMovedToBackup()
    {
        if (!$this->connection->isTableExists($this->getBackupTableName('ebay_processing_action_item'))) {
            return false;
        }

        $select = $this->connection->select()
            ->from($this->getBackupTableName('ebay_processing_action_item'))
            ->order('id DESC')
            ->limit(1);

        $row = $this->connection->fetchRow($select);

        if (empty($row['input_data'])) {
            return false;
        }

        $rowInputData = json_decode($row['input_data'], true);

        return !empty($rowInputData['to_651_moved_to_backup']);
    }

    private function markAsMovedToBackup()
    {
        $this->connection->insert(
            $this->getBackupTableName('ebay_processing_action_item'),
            array(
                'action_id'  => 0,
                'related_id' => 0,
                'input_data' => json_encode(array('to_651_moved_to_backup' => true)),
                'is_skipped' => 0,
            )
        );
    }

    //----------------------------------------

    private function moveTableToBackup($tableName)
    {
        if (!$this->connection->isTableExists($this->getTableName($tableName))) {
            return;
        }

        if ($this->connection->isTableExists($this->getBackupTableName($tableName))) {
            $this->connection->dropTable($this->getBackupTableName($tableName));
        }

        $this->connection->renameTable($this->getTableName($tableName), $this->getBackupTableName($tableName));
    }

    //########################################

    private function updateProcessingLocks(array $oldProcessingRow, $newProcessingId, $objectId = NULL)
    {
        $where = array(
            'processing_id = ?' => $oldProcessingRow['id'],
        );

        if (!is_null($objectId)) {
            $where['object_id = ?'] = $objectId;
        }

        $this->connection->update(
            $this->getTableName('processing_lock'),
            array('processing_id' => $newProcessingId),
            $where
        );
    }

    //########################################

    private function getTableName($table)
    {
        return $this->installer->getTable('m2epro_'.$table);
    }

    private function getBackupTableName($table)
    {
        $tableName = $this->getTableName($table).self::BACKUP_TABLE_SUFFIX;

        if (strlen($tableName) > self::BACKUP_TABLE_IDENTIFIER_MAX_LEN) {
            $tableName = 'm2epro'.'_'.sha1($tableName).self::BACKUP_TABLE_SUFFIX;
        }

        return $tableName;
    }

    //########################################
}

// Amazon Shipping Template attributes
//########################################

/*
    ALTER TABLE `m2epro_amazon_template_shipping_template`
        CHANGE COLUMN `template_name` `template_name_value` VARCHAR(255) NOT NULL,
        ADD COLUMN `template_name_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `title`,
        ADD COLUMN `template_name_attribute` VARCHAR(255) NOT NULL AFTER `template_name_value`,
        DROP INDEX `template_name`;

    UPDATE `m2epro_amazon_template_shipping_template`
        SET `template_name_mode` = 1;
*/

//########################################

if ($installer->getTableModifier('amazon_template_shipping_template')->isColumnExists('template_name')) {
    $installer->getTableModifier('amazon_template_shipping_template')
        ->renameColumn(
            'template_name', 'template_name_value', false, false
        )
        ->addColumn(
            'template_name_mode', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'title', false, false
        )
        ->addColumn(
            'template_name_attribute', 'VARCHAR(255) NOT NULL', NULL, 'template_name_value', false, false
        )
        ->dropIndex('template_name', false)
        ->commit();

    $installer->run(<<<SQL
    UPDATE `{$installer->getTable('m2epro_amazon_template_shipping_template')}`
    SET `template_name_mode` = 1;
SQL
    );
}

//########################################

$installer->getSynchConfigModifier()->getEntity('/ebay/other_listings/update/', 'interval')->insert(3600);

$installer->getSynchConfigModifier()->delete('/amazon/defaults/update_repricing/');
$installer->getSynchConfigModifier()->delete('/amazon/general/update_repricing/');

//########################################

// Ebay Epids De Uk
//########################################

/*
    ALTER TABLE `m2epro_ebay_dictionary_motor_epid`
    ADD COLUMN `scope` TINYINT(2) UNSIGNED NOT NULL AFTER `is_custom`,
    ADD INDEX `scope` (`scope`);

    ALTER TABLE `m2epro_ebay_listing`
    ADD COLUMN `parts_compatibility_mode` VARCHAR(10) DEFAULT NULL AFTER `product_add_ids`;
*/

// ---------------------------------------

$installer->getTableModifier('ebay_dictionary_motor_epid')
    ->addColumn('scope', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_custom', true);

$installer->run(<<<SQL
    UPDATE `{$installer->getTable('m2epro_ebay_dictionary_motor_epid')}`
    SET `scope` = 1;
SQL
);

// ---------------------------------------

$installer->run(<<<SQL
    UPDATE `{$installer->getTable('m2epro_ebay_marketplace')}`
    SET `is_epid` = 1
    WHERE marketplace_id IN (3, 8);
SQL
);

// ---------------------------------------

$installer->getMainConfigModifier()->getEntity('/ebay/motors/', 'epids_attribute')->updateKey('epids_motor_attribute');
$installer->getMainConfigModifier()->getEntity('/ebay/motors/', 'epids_uk_attribute')->insert(NULL);
$installer->getMainConfigModifier()->getEntity('/ebay/motors/', 'epids_de_attribute')->insert(NULL);

// ---------------------------------------

$installer->getTableModifier('ebay_listing')
    ->addColumn('parts_compatibility_mode', 'VARCHAR(10)', 'NULL', 'product_add_ids');

$installer->run(<<<SQL
    UPDATE `{$installer->getTable('m2epro_ebay_listing')}` mel
    INNER JOIN `{$installer->getTable('m2epro_listing')}` ml ON ml.id = mel.listing_id
    SET `parts_compatibility_mode` = 'ktypes'
    WHERE ml.marketplace_id IN (3, 8);
SQL
);

// Issues Resolver Cron Task
//########################################

$installer->getMainConfigModifier()->insert(
    '/cron/task/issues_resolver/', 'mode', '1', '0 - disable, \r\n1 - enable'
);
$installer->getMainConfigModifier()->insert(
    '/cron/task/issues_resolver/', 'interval', '3600', 'in seconds'
);
$installer->getMainConfigModifier()->insert(
    '/cron/task/issues_resolver/', 'last_access', NULL, 'date of last access'
);
$installer->getMainConfigModifier()->insert(
    '/cron/task/issues_resolver/', 'last_run', NULL, 'date of last run'
);

// Remove Terapeak
//########################################

$installer->getMainConfigModifier()->delete('/view/ebay/terapeak/');

// eBay Waste Recycling Fee
//########################################

/*
    ALTER TABLE `m2epro_ebay_order_item`
    ADD COLUMN `waste_recycling_fee` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000 AFTER `final_fee`;
 */
$installer->getTableModifier('ebay_order_item')
    ->addColumn('waste_recycling_fee', 'DECIMAL(12, 4) NOT NULL', '0.0000', 'final_fee');

//########################################

$installer->endSetup();

//########################################