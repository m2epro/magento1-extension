<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_5_0_9__v6_5_0_10_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
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

        $installer->getMainConfigModifier()
            ->insert('/amazon/business/', 'mode', '0', '0 - disable, \r\n1 - enable');

        //########################################

        $installer->getTableModifier('amazon_marketplace')
            ->addColumn(
                'is_business_available', 'tinyint(2) UNSIGNED NOT NULL', 0,
                'is_merchant_fulfillment_available', true, false
            )
            ->addColumn(
                'is_vat_calculation_service_available', 'tinyint(2) UNSIGNED NOT NULL', 0,
                'is_business_available', true, false
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
                'is_vat_calculation_service_enabled', 'TINYINT(2) UNSIGNED NOT NULL', 0,
                'magento_orders_settings', false, false
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
                'template_product_tax_code_id', 'INT(11) UNSIGNED', 'NULL',
                'template_shipping_override_id', true, false
            )
            ->addColumn(
                'online_business_price', 'DECIMAL(12, 4) UNSIGNED', 'NULL',
                'online_regular_sale_price_end_date', true, false
            )
            ->addColumn(
                'online_business_discounts', 'TEXT', 'NULL', 'online_business_price', false, false
            )
            ->commit();

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
                'sale_price_start_date_custom_attribute', 'regular_sale_price_start_date_custom_attribute',
                false, false
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
                'is_business_customer_allowed', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_regular_customer_allowed',
                false, false
            )
            ->addColumn(
                'business_price_mode', 'TINYINT(2) UNSIGNED NOT NULL', NULL, 'regular_price_vat_percent', false, false
            )
            ->addColumn(
                'business_price_custom_attribute', 'VARCHAR(255) NOT NULL', NULL, 'business_price_mode', false, false
            )
            ->addColumn(
                'business_price_coefficient', 'VARCHAR(255) NOT NULL', NULL, 'business_price_custom_attribute',
                false, false
            )
            ->addColumn(
                'business_price_variation_mode', 'TINYINT(2) UNSIGNED NOT NULL',
                NULL, 'business_price_coefficient', false, false
            )
            ->addColumn(
                'business_price_vat_percent', 'FLOAT UNSIGNED', 'NULL', 'business_price_variation_mode',
                false, false
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

        $installer->getTableModifier('amazon_order')
            ->addColumn('is_business', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_prime', true);

        //########################################

        $installer->getTableModifier('amazon_template_synchronization')->addColumn(
            'revise_change_product_tax_code_template', 'tinyint(2) UNSIGNED NOT NULL', NULL,
            'revise_change_shipping_template'
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

        include __DIR__ .'/'. 'ProductActionsUpgrader.php';

        $productActionsUpgrader = new ProductActionsUpgrader($installer, $connection);
        $productActionsUpgrader->run();

        // Amazon Shipping Template attributes
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

        // Ebay Epids De Uk
        //########################################

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

        $installer->getMainConfigModifier()
                  ->getEntity('/ebay/motors/', 'epids_attribute')->updateKey('epids_motor_attribute');
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

        $installer->getTableModifier('ebay_order_item')
            ->addColumn('waste_recycling_fee', 'DECIMAL(12, 4) NOT NULL', '0.0000', 'final_fee');
    }

    //########################################
}