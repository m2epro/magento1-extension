<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_5_0_10__v6_5_0_11_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $installer->run(<<<SQL

DELETE mp.*, mpl.* FROM `m2epro_processing` mp
LEFT JOIN `m2epro_processing_lock` mpl ON mpl.processing_id = mp.id
WHERE mp.model LIKE '%Amazon_Synchronization_Orders_Receive_ProcessingRunner%'

SQL
        );

        //########################################

        if (!$installer->getTablesObject()->isExists('amazon_listing_product_action_processing_list_sku')) {
            $installer->run(<<<SQL

CREATE TABLE `{$this->_installer->getTable('m2epro_amazon_listing_product_action_processing_list_sku')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id__sku` (`account_id`, `sku`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        $lockItemTable = $installer->getTablesObject()->getFullName('lock_item');
        $skuQueueLockItemsStmt = $installer->getConnection()->query("
  SELECT * FROM {$lockItemTable} WHERE `nick` LIKE 'amazon_list_skus_queue_%';
");

        $accountsTable = $installer->getTablesObject()->getFullName('account');
        $amazonAccountsIds = $installer->getConnection()->query("
  SELECT `id` FROM {$accountsTable} WHERE `component_mode` = 'amazon';
")->fetchAll(PDO::FETCH_COLUMN);

        $amazonProcessingListSkuTable = $installer->getTablesObject()->getFullName(
            'amazon_listing_product_action_processing_list_sku'
        );

        $lockItemsIds = array();

        while ($lockItemData = $skuQueueLockItemsStmt->fetch(PDO::FETCH_ASSOC)) {
            $lockItemsIds[] = $lockItemData['id'];

            $accountId = str_replace('amazon_list_skus_queue_', '', $lockItemData['nick']);
            if (!in_array($accountId, $amazonAccountsIds)) {
                continue;
            }

            $skus = @json_decode($lockItemData['data'], true);
            if (empty($skus)) {
                continue;
            }

            $insertData = array();

            foreach (array_unique($skus) as $sku) {
                $insertData[] = array(
                    'account_id'  => $accountId,
                    'sku'         => $sku,
                    'create_date' => date('Y-m-d H:i:s', gmdate('U'))
                );
            }

            $connection->insertMultiple($amazonProcessingListSkuTable, $insertData);
        }

        if (!empty($lockItemsIds)) {
            $connection->delete($lockItemTable, array('id IN (?)' => array_unique($lockItemsIds)));
        }

        //########################################

        $installer->getTableModifier("ebay_listing_other")
            ->dropColumn("old_items");

        $installer->getTableModifier('amazon_account')->dropColumn('other_listings_move_settings');

        //########################################

        // Operation History Migration
        //----------------------------------------

        $installer->getTableModifier('operation_history')->truncate();

        //########################################

        $installer->getTableModifier('stop_queue')
            ->dropColumn('item_data')
            ->dropColumn('account_hash')
            ->dropColumn('marketplace_id')
            ->addColumn('additional_data', 'TEXT', NULL, 'is_processed');

        //########################################

        $installer->getTableModifier("ebay_listing_product")
            ->addColumn("online_is_variation", "TINYINT(2) UNSIGNED", "NULL", "is_duplicate", true);

        $mainTableName      = $installer->getTablesObject()->getFullName("ebay_listing_product");
        $productTableName   = $installer->getTablesObject()->getFullName("listing_product");
        $variationTableName = $installer->getTablesObject()->getFullName("listing_product_variation");
        $optionTableName    = $installer->getTablesObject()->getFullName("listing_product_variation_option");

        $stmt = $connection->query(<<<SQL
    SELECT DISTINCT `elp`.`listing_product_id` as `id`
    FROM {$mainTableName} as `elp`
    INNER JOIN {$productTableName} as `lp` ON `elp`.`listing_product_id` = `lp`.`id`
    INNER JOIN {$variationTableName} as `lpv` ON `elp`.`listing_product_id` = `lpv`.`listing_product_id`
    INNER JOIN {$optionTableName} as `lpvo` ON `lpv`.`id` = `lpvo`.`listing_product_variation_id`
    WHERE `lp`.`status` != 0 AND `elp`.`online_is_variation` IS NULL
SQL
        );

        $iteration = 0;
        $itemsIds = array();

        while (true) {

            $iteration++;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            !empty($row["id"]) && $itemsIds[] = $row["id"];

            if (($iteration % 1000 === 0 || $row === false) && !empty($itemsIds)) {

                $connection->update($mainTableName,
                                    array("online_is_variation" => 1),
                                    array("listing_product_id IN (?)" => $itemsIds)
                );
                $itemsIds = array();
            }

            if ($row === false) {
                break;
            }
        }

        //----------------------------------------

        $installer->getTableModifier("ebay_listing_product")
            ->addColumn("online_is_auction_type", "TINYINT(2) UNSIGNED", "NULL", "online_is_variation", true);

        $stmt = $connection->query(<<<SQL
    SELECT `elp`.`listing_product_id` as `id`
    FROM {$mainTableName} as `elp`
    INNER JOIN {$productTableName} as `lp` ON `elp`.`listing_product_id` = `lp`.`id`
    WHERE `lp`.`status` != 0 AND `lp`.`status` != 6
    AND `elp`.`online_start_price` > 0 AND
        `elp`.`online_is_variation` IS NULL AND
        `elp`.`online_is_auction_type` IS NULL
SQL
        );

        $iteration = 0;
        $itemsIds = array();

        while (true) {

            $iteration++;
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            !empty($row["id"]) && $itemsIds[] = $row["id"];

            if (($iteration % 1000 === 0 || $row === false) && !empty($itemsIds)) {

                $connection->update($mainTableName,
                                    array("online_is_auction_type" => 1),
                                    array("listing_product_id IN (?)" => $itemsIds)
                );
                $itemsIds = array();
            }

            if ($row === false) {
                break;
            }
        }

        //########################################

        $mainTableName = $installer->getTablesObject()->getFullName("ebay_marketplace");

        $connection->update($mainTableName,
                            array("currency" => "CAD"),
                            array("marketplace_id IN (?)" => array(2, 19))
        );

        $installer->getMainConfigModifier()->delete("/ebay/selling/currency/");

        $installer->getCacheConfigModifier()
                  ->delete("/view/ebay/template/selling_format/multi_currency_marketplace_2/");
        $installer->getCacheConfigModifier()
                  ->delete("/view/ebay/template/selling_format/multi_currency_marketplace_19/");

        $installer->getTableModifier("ebay_marketplace")->dropColumn("is_multi_currency");

        //########################################

        $installer->getTableModifier('amazon_listing_product_repricing')->changeColumn(
            'is_online_disabled', 'TINYINT(2) UNSIGNED NOT NULL', '0');

        $installer->getTableModifier('amazon_listing_product_repricing')->addColumn(
            'is_online_inactive', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_online_disabled', true);

        $installer->getTableModifier('amazon_listing_other')->addColumn(
            'is_repricing_inactive', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_repricing_disabled', true);

        $installer->run(<<<SQL
    UPDATE `m2epro_amazon_listing_product_repricing`
    SET `is_online_inactive` = 0;
SQL
        );

        $installer->run(<<<SQL
    UPDATE `m2epro_amazon_listing_other`
    SET `is_repricing_inactive` = 0;
SQL
        );

        $installer->run(<<<SQL
UPDATE `m2epro_listing_product` mlp
  JOIN `m2epro_amazon_listing_product` malp
    ON mlp.id = malp.listing_product_id
  SET mlp.additional_data = REPLACE(`additional_data`, 'repricing_disabled_count', 'repricing_not_managed_count')
  WHERE malp.is_repricing = 1;
SQL
        );

        $installer->run(<<<SQL
UPDATE `m2epro_listing_product` mlp
  JOIN `m2epro_amazon_listing_product` malp
    ON mlp.id = malp.listing_product_id
  SET mlp.additional_data = REPLACE(`additional_data`, 'repricing_enabled_count', 'repricing_managed_count')
  WHERE malp.is_repricing = 1;
SQL
        );

        //########################################

        $processingTable = $installer->getTablesObject()->getFullName('processing');

        $processings = $installer->getConnection()->query("
    SELECT * FROM {$processingTable}
    WHERE `model` LIKE 'M2ePro/Amazon_Connector_Product_%' OR
          `model` LIKE 'M2ePro/Ebay_Connector_Item_%';
")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($processings as $processing) {
            if (empty($processing['params'])) {
                continue;
            }

            $params = (array)@json_decode($processing['params'], true);
            if (!isset($params['responser_params']['product'])) {
                continue;
            }

            $productData = (array)$params['responser_params']['product'];

            $isDataChanged = false;

            if (!isset($productData['configurator']['is_default_mode'])) {
                continue;
            }

            if ($productData['configurator']['is_default_mode']) {
                $productData['configurator']['mode'] = 'excluding';
            } else {
                $productData['configurator']['mode'] = 'including';
            }

            unset($productData['configurator']['is_default_mode']);

            $params['responser_params']['product'] = $productData;

            $connection->update(
                $processingTable,
                array('params' => json_encode($params)),
                array('id = ?' => $processing['id'])
            );
        }

        //########################################

        $installer->getTableModifier('amazon_template_description_definition')
            ->addColumn('msrp_rrp_mode', 'TINYINT(2) UNSIGNED', '0', 'number_of_items_custom_attribute', false, false)
            ->addColumn('msrp_rrp_custom_attribute', 'VARCHAR(255)', NULL, 'msrp_rrp_mode', false, false)
            ->commit();

        // Magento Product Websites Update
        //########################################

        if (!$installer->getTablesObject()->isExists('magento_product_websites_update')) {
            $installer->run(<<<SQL

CREATE TABLE `{$this->_installer->getTable('m2epro_magento_product_websites_update')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NOT NULL,
  `action` TINYINT(2) UNSIGNED NOT NULL,
  `website_id` SMALLINT(5) UNSIGNED NOT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `product_id` (`product_id`),
  INDEX `action` (`action`),
  INDEX `website_id` (`website_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        // -- MpnValueCanBeChanged
        //########################################

        $installer->getMainConfigModifier()
            ->insert('/component/ebay/variation/', 'mpn_can_be_changed', '0');

        // -- EbayGuaranteeDelivery
        //########################################

        $installer->getTableModifier('ebay_account')
            ->addColumn('sell_api_token_session', 'VARCHAR(255) NOT NULL', NULL, 'token_expired_date');

        $installer->getTableModifier('ebay_account')
            ->addColumn('sell_api_token_expired_date', 'DATETIME NOT NULL', NULL, 'sell_api_token_session');

        //########################################

        $installer->getTableModifier('ebay_account')
            ->addColumn('rate_tables', 'TEXT', 'NULL', 'user_preferences');

        $installer->getTableModifier('ebay_template_shipping')
            ->addColumn('local_shipping_rate_table', 'TEXT', 'NULL', 'local_shipping_rate_table_mode')
            ->addColumn('international_shipping_rate_table', 'TEXT', 'NULL', 'international_shipping_rate_table_mode');

        if ($installer->getTableModifier('ebay_template_shipping')->isColumnExists('local_shipping_rate_table_mode')) {

            $tableName = $installer->getTablesObject()->getFullName('ebay_account');
            $query = $connection->query("SELECT account_id FROM {$tableName}");
            $accounts = $query->fetchAll(PDO::FETCH_ASSOC);

            $enabledShippingRateTable = array();
            $disabledShippingRateTable = array();
            foreach ($accounts as $account) {

                $enabledShippingRateTable[$account['account_id']] = array(
                    "mode" => 1,
                    "value" => 1
                );

                $disabledShippingRateTable[$account['account_id']] = array(
                    "mode" => 1,
                    "value" => 0
                );
            }

            $tableName = $installer->getTablesObject()->getFullName('ebay_template_shipping');

            $connection->update(
                $tableName,
                array('local_shipping_rate_table' => json_encode($enabledShippingRateTable)),
                array('local_shipping_rate_table_mode = ?' => 1)
            );

            $connection->update(
                $tableName,
                array('international_shipping_rate_table' => json_encode($enabledShippingRateTable)),
                array('international_shipping_rate_table_mode = ?' => 1)
            );

            $connection->update(
                $tableName,
                array('local_shipping_rate_table' => json_encode($disabledShippingRateTable)),
                array('local_shipping_rate_table_mode = ?' => 0)
            );

            $connection->update(
                $tableName,
                array('international_shipping_rate_table' => json_encode($disabledShippingRateTable)),
                array('international_shipping_rate_table_mode = ?' => 0)
            );
        }

        $installer->getTableModifier('ebay_template_shipping')->dropColumn('local_shipping_rate_table_mode');
        $installer->getTableModifier('ebay_template_shipping')->dropColumn('international_shipping_rate_table_mode');

        // -- MagentoAttributesForDispatchTime
        //########################################

        $installer->getTableModifier('ebay_template_shipping')
            ->addColumn('dispatch_time_mode', 'TINYINT(2) UNSIGNED NOT NULL', 1, 'address_custom_attribute')
            ->renameColumn('dispatch_time', 'dispatch_time_value')
            ->addColumn('dispatch_time_attribute', 'VARCHAR(255)', NULL, 'dispatch_time_value');

        // -- Remove kill now feature
        //########################################

        $installer->getTableModifier("lock_item")
            ->dropColumn("kill_now");

        // -- RenameDiscountFields
        //########################################

        $installer->getTableModifier('ebay_template_shipping')
            ->renameColumn('local_shipping_discount_mode', 'local_shipping_discount_promotional_mode')
            ->renameColumn('local_shipping_discount_profile_id', 'local_shipping_discount_combined_profile_id')
            ->renameColumn('international_shipping_discount_mode', 'international_shipping_discount_promotional_mode')
            ->renameColumn(
                'international_shipping_discount_profile_id', 'international_shipping_discount_combined_profile_id'
            );

        // -- NewAmazonMarketplaces
        //########################################

        $tableName = $installer->getTablesObject()->getFullName('marketplace');
        $query = $connection->query("SELECT * FROM {$tableName} WHERE `id` IN (34, 35, 36)");
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {

            $installer->run(<<<SQL

INSERT INTO `m2epro_marketplace` VALUES
  (34, 9, 'Mexico', 'MX', 'amazon.com.mx', 0, 10, 'America', 'amazon', '2017-09-27 00:00:00', '2017-09-27 00:00:00'),
  (35, 10, 'Australia', 'AU', 'amazon.com.au', 0, 11, 'Asia / Pacific', 'amazon',
   '2017-09-27 00:00:00', '2017-09-27 00:00:00'),
  (36, 0, 'India', 'IN', 'amazon.in', 0, 12, 'Asia / Pacific', 'amazon', '2017-09-27 00:00:00', '2017-09-27 00:00:00');

INSERT INTO `m2epro_amazon_marketplace` VALUES
  (34, '8636-1433-4377', 'MXN',0,0,0,0,0),
  (35, '2770-5005-3793', 'AUD',1,0,0,0,0),
  (36, NULL, '',0,0,0,0,0);

SQL
            );
        }

        $marketplaceIds = array(34, 35, 36);

        $listingTable = $installer->getTablesObject()->getFullName('listing');
        $accountTable = $installer->getTablesObject()->getFullName('amazon_account');

        foreach ($marketplaceIds as $marketplaceId) {
            $listingsCount = $connection->query(
                "SELECT COUNT(*) FROM {$listingTable} WHERE `marketplace_id` = {$marketplaceId}"
            )->fetchColumn();

            $accountsCount = $connection->query(
                "SELECT COUNT(*) FROM {$accountTable} WHERE `marketplace_id` = {$marketplaceId}"
            )->fetchColumn();

            if ((int)$listingsCount > 0 || (int)$accountsCount > 0) {
                $installer->run(<<<SQL
UPDATE `m2epro_marketplace` SET `status` = 1 WHERE `id` = {$marketplaceId};
SQL
                );
            }
        }

        // -- NewAmazonMarketplaces2
        //########################################

        $installer->getTableModifier('amazon_marketplace')
            ->addColumn('is_automatic_token_retrieving_available', 'TINYINT(2) UNSIGNED NOT NULL', 0,
                        'is_product_tax_code_policy_available');

        $mainTableName = $installer->getTablesObject()->getFullName("amazon_marketplace");

        $connection->update($mainTableName,
                            array('is_automatic_token_retrieving_available' => 1),
                            array('marketplace_id IN (?)' => array(24, 25, 26, 28, 29, 30, 31, 34))
        );

        // -- TryToCreateMagentoOrderAgainIfInitialCreationWasFailed
        //########################################

        $installer->getTableModifier('order')
            ->addColumn(
                'magento_order_creation_failure', 'TINYINT(2) UNSIGNED NOT NULL', '0',
                'magento_order_id', true, false
            )
            ->addColumn(
                'magento_order_creation_fails_count', 'TINYINT(2) UNSIGNED NOT NULL', '0',
                'magento_order_creation_failure', true, false
            )
            ->addColumn(
                'magento_order_creation_latest_attempt_date', 'DATETIME', NULL,
                'magento_order_creation_fails_count', true, false
            )
            ->commit();

        // -- ShouldBeUrlsSecureFromConfig
        //########################################

        $value = $connection
            ->select()
            ->from($installer->getTable('core_config_data'), array('value'))
            ->where('path = ?', 'web/secure/use_in_frontend')
            ->where('scope_id = ?', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->query()
            ->fetchColumn();

        $installer->getMainConfigModifier()->insert(
            '/ebay/description/', 'should_be_ulrs_secure', (int)$value, '0 - No, \r\n1 - Yes'
        );

        // -- EbayVariationsWithASpace
        //########################################

        $listingProductVariationOption = $installer->getTable('m2epro_listing_product_variation_option');
        $listingProductVariation       = $installer->getTable('m2epro_listing_product_variation');

        $ebayListingProductVariationOption = $installer->getTable('m2epro_ebay_listing_product_variation_option');
        $ebayListingProductVariation       = $installer->getTable('m2epro_ebay_listing_product_variation');

        $installer->run(<<<SQL
UPDATE `{$listingProductVariationOption}`

SET `attribute` = TRIM(`attribute`),
    `option` = TRIM(`option`)

WHERE `component_mode` = 'ebay' AND
      `product_type` = 'configurable' AND
      (
         `attribute` LIKE '% ' OR `attribute` LIKE ' %' OR
         `option` LIKE '% ' OR `option` LIKE ' %'
      );
SQL
        );

        $stmt = $connection->query(<<<SQL
SELECT
  COUNT(`lpv`.`id`) - 1 AS `duplicates`,
  GROUP_CONCAT(DISTINCT `lpvo`.`listing_product_variation_id` SEPARATOR ',') AS `duplicated_variations_ids`

FROM `{$listingProductVariationOption}` `lpvo`
  INNER JOIN `{$listingProductVariation}` `lpv` ON `lpv`.`id` = `lpvo`.`listing_product_variation_id`

WHERE
  `lpvo`.`component_mode` = 'ebay' AND
  `lpvo`.`product_type` = 'configurable'

GROUP BY `lpv`.`listing_product_id`, `lpvo`.`product_id`, `lpvo`.`attribute`
HAVING `duplicates` >= 1;
SQL
        );

        $duplicatedVariationsIds = array(); // will be deleted
        $originalVariationsIds   = array(); // will be updated

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $idsForRemove = explode(',', $row['duplicated_variations_ids']);
            $originalVariationsIds[] = array_shift($idsForRemove);

            $duplicatedVariationsIds = array_merge($duplicatedVariationsIds, $idsForRemove);
        }

        foreach (array_chunk($originalVariationsIds, 1000) as $originalVariationsIdsPart) {

            $originalVariationsIdsPart = implode(',', $originalVariationsIdsPart);
            $stmt = $connection->query(<<<SQL
        UPDATE `{$ebayListingProductVariation}`
        SET `add` = 0, `delete` = 0
        WHERE `listing_product_variation_id` IN ({$originalVariationsIdsPart});
SQL
            );
        }

        foreach (array_chunk($duplicatedVariationsIds, 1000) as $duplicatedVariationsIdsPart) {

            $duplicatedVariationsIdsPart = implode(',', $duplicatedVariationsIdsPart);
            $stmt = $connection->query(<<<SQL
            DELETE `lpv`, `elpv`
                FROM `{$listingProductVariation}` `lpv`
            INNER JOIN `{$ebayListingProductVariation}` `elpv`
                ON `lpv`.`id` = `elpv`.`listing_product_variation_id`
            WHERE `lpv`.`id` IN ({$duplicatedVariationsIdsPart});
SQL
            );

            $stmt = $connection->query(<<<SQL
            DELETE `lpvo`, `elpvo`
                FROM `{$listingProductVariationOption}` `lpvo`
            INNER JOIN `{$ebayListingProductVariationOption}` `elpvo`
                ON `lpvo`.`id` = `elpvo`.`listing_product_variation_option_id`
            WHERE `lpvo`.`listing_product_variation_id` IN ({$duplicatedVariationsIdsPart});
SQL
            );
        }

        // - Remove Buy
        //########################################

        $select = $connection->select()
            ->from($installer->getTablesObject()->getFullName('listing_product'), 'id')
            ->where('component_mode = ?', 'buy')
            ->where('status != ?', 0);

        if (count($connection->fetchCol($select)) > 0) {
            $removedBuyWizardStatus = 0;
        } else {
            $removedBuyWizardStatus = 3;
        }

        $select = $connection->select()
            ->from($installer->getTablesObject()->getFullName('wizard'));

        $oldWizardsData = $connection->fetchAll($select);

        $newWizardsData = array(
            'installationEbay' => array(
                'nick'     => 'installationEbay',
                'view'     => 'ebay',
                'status'   => 0,
                'step'     => NULL,
                'type'     => 1,
                'priority' => 1,
            ),
            'amazon' => array(
                'nick'     => 'installationAmazon',
                'view'     => 'amazon',
                'status'   => 0,
                'step'     => NULL,
                'type'     => 1,
                'priority' => 2,
            ),
            'migrationNewAmazon' => array(
                'nick'     => 'migrationNewAmazon',
                'view'     => 'amazon',
                'status'   => 3,
                'step'     => NULL,
                'type'     => 1,
                'priority' => 3,
            ),
            'removedPlay' => array(
                'nick'     => 'removedPlay',
                'view'     => '*',
                'status'   => 3,
                'step'     => NULL,
                'type'     => 0,
                'priority' => 4,
            ),
            'ebayProductDetails' => array(
                'nick'     => 'ebayProductDetails',
                'view'     => 'ebay',
                'status'   => 3,
                'step'     => NULL,
                'type'     => 1,
                'priority' => 5,
            ),
            'fullAmazonCategories' => array(
                'nick'     => 'fullAmazonCategories',
                'view'     => 'amazon',
                'status'   => 3,
                'step'     => NULL,
                'type'     => 1,
                'priority' => 6,
            ),
            'amazonShippingOverridePolicy' => array(
                'nick'     => 'amazonShippingOverridePolicy',
                'view'     => 'amazon',
                'status'   => 3,
                'step'     => NULL,
                'type'     => 1,
                'priority' => 7,
            ),
            'removedEbay3rdParty' => array(
                'nick'     => 'removedEbay3rdParty',
                'view'     => 'ebay',
                'status'   => 3,
                'step'     => NULL,
                'type'     => 0,
                'priority' => 8,
            ),
            'removedBuy' => array(
                'nick'     => 'removedBuy',
                'view'     => '*',
                'status'   => $removedBuyWizardStatus,
                'step'     => NULL,
                'type'     => 0,
                'priority' => 9,
            ),
        );

        foreach ($oldWizardsData as $oldWizardData) {
            if (!isset($newWizardsData[$oldWizardData['nick']])) {
                continue;
            }

            $newWizardsData[$oldWizardData['nick']]['status'] = $oldWizardData['status'];
            $newWizardsData[$oldWizardData['nick']]['step']   = $oldWizardData['step'];
        }

        $installer->run(<<<SQL
TRUNCATE TABLE `m2epro_wizard`;
SQL
        );

        $connection->insertMultiple(
            $installer->getTablesObject()->getFullName('wizard'), array_values($newWizardsData)
        );

        // ---------------------------------------

        $tablesForRemove = array(
            'buy_account',
            'buy_item',
            'buy_listing',
            'buy_listing_auto_category_group',
            'buy_listing_other',
            'buy_listing_product',
            'buy_listing_product_variation',
            'buy_listing_product_variation_option',
            'buy_marketplace',
            'buy_order',
            'buy_order_item',
            'buy_template_selling_format',
            'buy_template_synchronization',
        );

        foreach ($tablesForRemove as $table) {
            if ($installer->tableExists($installer->getTablesObject()->getFullName($table))) {
                $connection->dropTable($installer->getTablesObject()->getFullName($table));
            }
        }

        $tablesForRemoveDataByComponentMode = array(
            'account',
            'marketplace',

            'listing',
            'listing_auto_category_group',
            'listing_other',
            'listing_product',
            'listing_product_variation',
            'listing_product_variation_option',
            'listing_log',
            'listing_other_log',

            'template_selling_format',
            'template_synchronization',

            'order',
            'order_item',
            'order_log',

            'stop_queue',
            'synchronization_log',
        );

        foreach ($tablesForRemoveDataByComponentMode as $table) {
            $connection->delete($installer->getTablesObject()->getFullName($table),
                                array('component_mode = ?' => 'buy'));
        }

        $connection->delete(
            $installer->getTablesObject()->getFullName('config'), array('`group` like ?' => '/component/buy/%')
        );

        $connection->delete(
            $installer->getTablesObject()->getFullName('config'), array('`group` like ?' => '/buy/%')
        );

        $connection->delete(
            $installer->getTablesObject()->getFullName('config'), array('`group` = ?' => '/view/common/component/')
        );

        $installer->getMainConfigModifier()->updateGroup(
            '/view/amazon/autocomplete/', array('`group` = ?' => '/view/common/autocomplete/')
        );

        $connection->delete(
            $installer->getTablesObject()->getFullName('synchronization_config'), array('`group` like ?' => '/buy/%')
        );

        $select = $connection->select()
            ->from($installer->getTablesObject()->getFullName('processing'), 'id')
            ->where('model like ?', 'M2ePro/Buy_%');

        $processingIdsForRemove = $connection->fetchCol($select);

        if (!empty($processingIdsForRemove)) {
            $connection->delete(
                $installer->getTablesObject()->getFullName('processing'),
                array('id IN (?)' => $processingIdsForRemove)
            );

            $connection->delete(
                $installer->getTablesObject()->getFullName('processing_lock'),
                array('processing_id IN (?)' => $processingIdsForRemove)
            );
        }

// - Remove Shipping Override
//########################################

        if ($installer->getTablesObject()->isExists('amazon_dictionary_shipping_override')) {
            $connection->dropTable($installer->getTablesObject()->getFullName('amazon_dictionary_shipping_override'));
        }

        if ($installer->getTablesObject()->isExists('amazon_template_shipping_override')) {
            $connection->dropTable($installer->getTablesObject()->getFullName('amazon_template_shipping_override'));
        }

        if ($installer->getTablesObject()->isExists('amazon_template_shipping_override_service')) {
            $connection->dropTable(
                $installer->getTablesObject()->getFullName('amazon_template_shipping_override_service')
            );
        }

        $installer->getTableModifier('amazon_listing_product')
            ->dropColumn('template_shipping_override_id', true, false)
            ->renameColumn('template_shipping_template_id', 'template_shipping_id', true, false)
            ->commit();

        $this->_installer->getTablesObject()->renameTable(
            'm2epro_amazon_template_shipping_template',
            'm2epro_amazon_template_shipping'
        );

        $installer->getTableModifier('amazon_account')
            ->dropColumn('shipping_mode');

        //########################################

        // - New Synchronization
        //########################################

        if (!$installer->getTablesObject()->isExists('listing_product_instruction')) {
            $installer->run(<<<SQL

CREATE TABLE `{$this->_installer->getTable('m2epro_listing_product_instruction')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `component` VARCHAR(10) DEFAULT NULL,
  `type` VARCHAR(255) NOT NULL,
  `initiator` VARCHAR(255) NOT NULL,
  `priority` INT(11) UNSIGNED NOT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `component` (`component`),
  INDEX `type` (`type`),
  INDEX `priority` (`priority`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        // ---------------------------------------

        $listingProductTable = $installer->getTablesObject()->getFullName('listing_product');
        $instructionTable = $installer->getTablesObject()->getFullName('listing_product_instruction');

        if ($installer->getTablesObject()->isExists('product_change')) {
            $productChangeTable = $installer->getTablesObject()->getFullName('product_change');

            $changedProductsListingsProductsData = $installer->getConnection()->query("
  SELECT `lp`.`id`, `lp`.`component_mode`, `pc`.`attribute` FROM `{$listingProductTable}` AS `lp`
  LEFT JOIN `{$productChangeTable}` AS `pc` ON `pc`.`product_id` = `lp`.`product_id`
  WHERE `pc`.`product_id` IS NOT NULL
")->fetchAll(PDO::FETCH_ASSOC);

            $instructionsInsertData = array();

            foreach ($changedProductsListingsProductsData as $listingProductData) {
                $instructionTypes = array(
                    'magento_product_qty_data_potentially_changed',
                    'magento_product_price_data_potentially_changed',
                    'magento_product_status_data_potentially_changed',
                );

                foreach ($instructionTypes as $instructionType) {
                    $instructionsInsertData[] = array(
                        'listing_product_id' => $listingProductData['id'],
                        'component'          => $listingProductData['component_mode'],
                        'type'               => $instructionType,
                        'priority'           => 80,
                        'create_date'        => date('Y-m-d H:i:s', gmdate('U'))
                    );
                }
            }

            $instructionsInsertDataParts = array_chunk($instructionsInsertData, 1000);

            foreach ($instructionsInsertDataParts as $instructionsInsertDataPart) {
                $connection->insertMultiple($instructionTable, $instructionsInsertDataPart);
            }

            $connection->dropTable($productChangeTable);
        }

        // ---------------------------------------

        if ($installer->getTableModifier('listing_product')->isColumnExists('synch_status')) {
            $synchStatusNeedListingsProductsData = $installer->getConnection()->query("
  SELECT `id`, `component_mode` FROM `{$listingProductTable}` WHERE `synch_status` = 1 AND `status` IN (2,6);
")->fetchAll(PDO::FETCH_ASSOC);

            $instructionsInsertData = array();

            foreach ($synchStatusNeedListingsProductsData as $listingProductData) {
                $instructionTypes = array(
                    'magento_product_qty_data_potentially_changed',
                    'magento_product_price_data_potentially_changed',
                    'magento_product_status_data_potentially_changed',
                );

                foreach ($instructionTypes as $instructionType) {
                    $instructionsInsertData[] = array(
                        'listing_product_id' => $listingProductData['id'],
                        'component'          => $listingProductData['component_mode'],
                        'type'               => $instructionType,
                        'priority'           => 60,
                        'create_date'        => date('Y-m-d H:i:s', gmdate('U'))
                    );
                }
            }

            $instructionsInsertDataParts = array_chunk($instructionsInsertData, 1000);

            foreach ($instructionsInsertDataParts as $instructionsInsertDataPart) {
                $connection->insertMultiple($instructionTable, $instructionsInsertDataPart);
            }

            $installer->getTableModifier('listing_product')
                ->dropColumn('tried_to_list', true, false)
                ->dropColumn('synch_status', true, false)
                ->dropColumn('synch_reasons', true, false)
                ->dropColumn('need_synch_rules_check', true, false)
                ->dropColumn('synch_rules_check_data', true, false)
                ->commit();
        }

        $installer->getTableModifier('amazon_listing_product')
            ->addColumn('online_handling_time', 'INT(11) UNSIGNED', 'NULL', 'online_qty', false, false)
            ->addColumn('online_restock_date', 'DATETIME', 'NULL', 'online_handling_time', false, false)
            ->addColumn('online_details_data', 'LONGTEXT', 'NULL', 'online_restock_date', false, false)
            ->addColumn('online_images_data', 'LONGTEXT', 'NULL', 'online_details_data', false, false)
            ->commit();

        $installer->getTableModifier('ebay_listing_product')
            ->renameColumn('online_category', 'online_main_category', true, false)
            ->addColumn('online_sub_title', 'VARCHAR(255)', 'NULL', 'online_title', false, false)
            ->addColumn('online_description', 'LONGTEXT', 'NULL', 'online_sub_title', false, false)
            ->addColumn('online_images', 'LONGTEXT', 'NULL', 'online_description', false, false)
            ->addColumn('online_categories_data', 'LONGTEXT', 'NULL', 'online_main_category', false, false)
            ->addColumn('online_shipping_data', 'LONGTEXT', 'NULL', 'online_categories_data', false, false)
            ->addColumn('online_payment_data', 'LONGTEXT', 'NULL', 'online_shipping_data', false, false)
            ->addColumn('online_return_data', 'LONGTEXT', 'NULL', 'online_payment_data', false, false)
            ->addColumn('online_other_data', 'LONGTEXT', 'NULL', 'online_return_data', false, false)
            ->commit();

        // ---------------------------------------

        $installer->getTableModifier('template_synchronization')
            ->dropColumn('revise_change_listing')
            ->dropColumn('revise_change_selling_format_template');

        $installer->getTableModifier('amazon_template_synchronization')
            ->dropColumn('relist_send_data', true, false)
            ->dropColumn('revise_change_description_template', true, false)
            ->dropColumn('revise_change_shipping_template', true, false)
            ->dropColumn('revise_change_product_tax_code_template', true, false)
            ->addColumn(
                'stop_mode', 'TINYINT(2) UNSIGNED NOT NULL', NULL, 'relist_qty_calculated_value_max', false, false
            )
            ->commit();

        $installer->getTableModifier('ebay_template_synchronization')
            ->dropColumn('relist_send_data', false, false)
            ->dropColumn('revise_change_description_template', false, false)
            ->dropColumn('schedule_mode', false, false)
            ->dropColumn('schedule_interval_settings', false, false)
            ->dropColumn('schedule_week_settings', false, false)
            ->renameColumn('revise_change_category_template', 'revise_update_categories', false, false)
            ->renameColumn('revise_change_payment_template', 'revise_update_payment', false, false)
            ->renameColumn('revise_change_shipping_template', 'revise_update_shipping', false, false)
            ->renameColumn('revise_change_return_template', 'revise_update_return', false, false)
            ->addColumn(
                'revise_update_other', 'TINYINT(2) UNSIGNED NOT NULL', NULL, 'revise_update_return', false, false
            )
            ->addColumn(
                'stop_mode', 'TINYINT(2) UNSIGNED NOT NULL', NULL, 'relist_qty_calculated_value_max', false, false
            )
            ->commit();

        // ---------------------------------------

        if (!$installer->getTablesObject()->isExists('listing_product_scheduled_action')) {
            $installer->run(<<<SQL

CREATE TABLE `{$this->_installer->getTable('m2epro_listing_product_scheduled_action')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `component` VARCHAR(10) DEFAULT NULL,
  `action_type` VARCHAR(12) NOT NULL,
  `is_force` TINYINT(2) NOT NULL DEFAULT 0,
  `tag` VARCHAR(255) DEFAULT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `component` (`component`),
  INDEX `action_type` (`action_type`),
  INDEX `tag` (`tag`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        // ---------------------------------------

        $ebayProcessingActionTable = $installer->getTablesObject()->getFullName('ebay_processing_action');
        $ebayListingProductActionProcessingTable = $installer->getTablesObject()->getFullName(
            'ebay_listing_product_action_processing'
        );

        if ($installer->getTablesObject()->isExists('ebay_processing_action') &&
            !$installer->getTablesObject()->isExists('ebay_listing_product_action_processing')
        ) {
            $connection->renameTable($ebayProcessingActionTable, $ebayListingProductActionProcessingTable);
        }

        $installer->getTableModifier('ebay_listing_product_action_processing')
            ->renameColumn('related_id', 'listing_product_id', true, false)
            ->dropColumn('account_id')
            ->dropColumn('marketplace_id')
            ->dropColumn('priority')
            ->dropColumn('start_date')
            ->commit();

        $installer->run(<<<SQL

UPDATE `m2epro_ebay_listing_product_action_processing`
SET `type` = 'list'
WHERE `type` = 0;

UPDATE `m2epro_ebay_listing_product_action_processing`
SET `type` = 'revise'
WHERE `type` = 1;

UPDATE `m2epro_ebay_listing_product_action_processing`
SET `type` = 'relist'
WHERE `type` = 2;

UPDATE `m2epro_ebay_listing_product_action_processing`
SET `type` = 'stop'
WHERE `type` = 3;

SQL
        );

        // ---------------------------------------

        $amazonProcessingActionTable = $installer->getTablesObject()->getFullName('amazon_processing_action');
        $amazonListingProductActionProcessingTable = $installer->getTablesObject()->getFullName(
            'amazon_listing_product_action_processing'
        );

        if ($installer->getTablesObject()->isExists('amazon_processing_action') &&
            !$installer->getTablesObject()->isExists('amazon_listing_product_action_processing')
        ) {
            $connection->renameTable($amazonProcessingActionTable, $amazonListingProductActionProcessingTable);
        }

        $installer->getTableModifier('amazon_listing_product_action_processing')
            ->renameColumn('related_id', 'listing_product_id', true, false)
            ->dropColumn('account_id')
            ->dropColumn('start_date')
            ->commit();

        $installer->run(<<<SQL

UPDATE `m2epro_amazon_listing_product_action_processing`
SET `type` = 'add'
WHERE `type` = 0;

UPDATE `m2epro_amazon_listing_product_action_processing`
SET `type` = 'update'
WHERE `type` = 1 OR `type` = 2;

UPDATE `m2epro_amazon_listing_product_action_processing`
SET `type` = 'delete'
WHERE `type` = 3;

SQL
        );

        if (!$installer->getTablesObject()->isExists('amazon_order_action_processing')) {
            $installer->run(<<<SQL

CREATE TABLE `{$this->_installer->getTable('m2epro_amazon_order_action_processing')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED DEFAULT NULL,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `request_pending_single_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `request_data` LONGTEXT NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id` (`order_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `request_pending_single_id` (`request_pending_single_id`),
  INDEX `type` (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        $installer->run(<<<SQL

INSERT INTO `{$this->_installer->getTable('m2epro_amazon_order_action_processing')}` (`order_id`,
                                                     `processing_id`,
                                                     `request_pending_single_id`,
                                                     `type`,
                                                     `request_data`,
                                                     `update_date`,
                                                     `create_date`)
SELECT `listing_product_id`,
       `processing_id`,
       `request_pending_single_id`,
       `type`,
       `request_data`,
       `update_date`,
       `create_date`
FROM `{$this->_installer->getTable('m2epro_amazon_listing_product_action_processing')}` WHERE `type` IN (3, 4, 5);

DELETE FROM `{$this->_installer->getTable('m2epro_amazon_listing_product_action_processing')}` WHERE `type` IN (3, 4, 5);

UPDATE `{$this->_installer->getTable('m2epro_amazon_order_action_processing')}`
SET `type` = 'update'
WHERE `type` = 3;

UPDATE `{$this->_installer->getTable('m2epro_amazon_order_action_processing')}`
SET `type` = 'cancel'
WHERE `type` = 4;

UPDATE `{$this->_installer->getTable('m2epro_amazon_order_action_processing')}`
SET `type` = 'refund'
WHERE `type` = 5;

SQL
        );

        // eBay Lot Size Feature
        //########################################

        $installer->getTableModifier('ebay_template_selling_format')
            ->addColumn('lot_size_mode', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'qty_max_posted_value', false, false)
            ->addColumn('lot_size_custom_value', 'INT(11) UNSIGNED', 'NULL', 'lot_size_mode', false, false)
            ->addColumn('lot_size_attribute', 'VARCHAR(255)', 'NULL', 'lot_size_custom_value', false, false)
            ->commit();

        // Applying Magento Order Id to Amazon Order
        //########################################

        $installer->getTableModifier('amazon_order')
            ->addColumn('seller_order_id', 'VARCHAR(255)', 'NULL', 'amazon_order_id', true);

        // New Cron Tasks
        //########################################

        $servicingInterval = rand(43200, 86400);

        $synchronizationConfigTable = $installer->getTablesObject()->getFullName('synchronization_config');

        $inspectorMode = 0;

        if ($installer->tableExists($synchronizationConfigTable)) {
            $inspectorMode = $connection
                ->select()
                ->from($synchronizationConfigTable, array('value'))
                ->where('`group` = ?', '/global/magento_products/inspector/')
                ->where('`key` = ?', 'mode')
                ->query()
                ->fetchColumn();
        }

        $connection->delete(
            $installer->getTablesObject()->getFullName('config'), array('`group` like \'/cron/task/%\'')
        );

        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/archive_old_orders/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/archive_old_orders/', 'interval', '3600', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/clear_old_logs/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/clear_old_logs/', 'interval', '86400', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/connector_command_pending/process_partial/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/connector_command_pending/process_partial/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/connector_command_pending/process_single/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/connector_command_pending/process_single/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/issues_resolver/remove_missed_processing_locks/', 'mode', '1',
            '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/issues_resolver/remove_missed_processing_locks/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/processing/process_result/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/processing/process_result/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/request_pending/process_partial/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/request_pending/process_partial/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/request_pending/process_single/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/request_pending/process_single/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/servicing/synchronize/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/system/servicing/synchronize/', 'interval', $servicingInterval, 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/magento/product/detect_directly_added/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/magento/product/detect_directly_added/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/magento/product/detect_directly_deleted/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/magento/product/detect_directly_deleted/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/inspect_direct_changes/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/inspect_direct_changes/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/process_revise_total/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/process_revise_total/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/auto_actions/process_magento_product_websites_updates/',
            'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/auto_actions/process_magento_product_websites_updates/',
            'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/stop_queue/process/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/stop_queue/process/', 'interval', '3600', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/stop_queue/remove_old/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/listing/product/stop_queue/remove_old/', 'interval', '86400', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/listing/product/inspector/', 'mode', $inspectorMode, '0 - disable, \r\n1 - enable'
        );

        $installer->getMainConfigModifier()->getEntity('/cron/', 'last_executed_slow_task')->updateValue(NULL);

        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/update_accounts_preferences/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/update_accounts_preferences/', 'interval', '86400', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/template/remove_unused/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/template/remove_unused/', 'interval', '3600', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/channel/synchronize_changes/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/channel/synchronize_changes/', 'interval', '300', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/feedbacks/download_new/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/feedbacks/download_new/', 'interval', '10800', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/feedbacks/send_response/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/feedbacks/send_response/', 'interval', '10800', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/other/resolve_sku/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/other/resolve_sku/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/other/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/other/channel/synchronize_data/', 'interval', '86400', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_instructions/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_instructions/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_scheduled_actions/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_scheduled_actions/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_actions/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/process_actions/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/remove_potential_duplicates/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/listing/product/remove_potential_duplicates/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/create_failed/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/create_failed/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/update/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert('/cron/task/ebay/order/update/', 'interval', '60', 'in seconds');
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/cancel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert('/cron/task/ebay/order/cancel/', 'interval', '60', 'in seconds');
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/reserve_cancel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/order/reserve_cancel/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/pickup_store/schedule_for_update/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/pickup_store/schedule_for_update/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/pickup_store/update_on_channel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/ebay/pickup_store/update_on_channel/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/listing/product/inspector/ebay/', 'max_allowed_instructions_count', '2000', NULL
        );
        $installer->getMainConfigModifier()->insert('/listing/product/revise/total/ebay/', 'mode', '0', NULL);
        $installer->getMainConfigModifier()->insert(
            '/listing/product/revise/total/ebay/', 'max_allowed_instructions_count', '2000', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/list/', 'priority_coefficient', '25', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/list/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/relist/', 'priority_coefficient', '125', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/relist/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_qty/', 'priority_coefficient', '500', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_qty/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_price/', 'priority_coefficient', '250', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_price/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_title/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_title/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_subtitle/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_subtitle/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_description/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_description/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_images/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_images/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_categories/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_categories/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_payment/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_payment/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_shipping/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_shipping/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_return/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_return/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_other/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/revise_other/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/stop/', 'priority_coefficient', '1000', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/action/stop/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/ebay/listing/product/scheduled_actions/', 'max_prepared_actions_count', '3000', NULL
        );

        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/resolve_title/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/resolve_title/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/channel/synchronize_data/', 'interval', '86400', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/channel/synchronize_data/blocked/', 'mode', '1',
            '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/other/channel/synchronize_data/blocked/', 'interval', '3600', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/', 'interval', '86400', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/blocked/', 'mode', '1',
            '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/blocked/', 'interval', '3600', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/defected/', 'mode', '1',
            '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/channel/synchronize_data/defected/', 'interval', '259200', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/run_variation_parent_processors/', 'mode', '1',
            '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/run_variation_parent_processors/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_instructions/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_instructions/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_actions/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_actions/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_actions_results/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/product/process_actions_results/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/receive/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/receive/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/create_failed/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/create_failed/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/update/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert('/cron/task/amazon/order/update/', 'interval', '60', 'in seconds');
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/update/seller_order_id/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/update/seller_order_id/', 'interval', '3600', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/refund/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert('/cron/task/amazon/order/refund/', 'interval', '60', 'in seconds');
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/cancel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert('/cron/task/amazon/order/cancel/', 'interval', '60', 'in seconds');
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/reserve_cancel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/reserve_cancel/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_update/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_update/', 'interval', '3600', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_refund/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_refund/', 'interval', '18000', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_cancel/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_cancel/', 'interval', '18000', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_results/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/order/action/process_results/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/inspect_products/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/inspect_products/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/update_settings/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/update_settings/', 'interval', '180', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/synchronize_general/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/synchronize_general/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/synchronize_actual_price/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/repricing/synchronize_actual_price/', 'interval', '60', 'in seconds'
        );
        $installer->getMainConfigModifier()->insert(
            '/listing/product/inspector/amazon/', 'max_allowed_instructions_count', '2000', NULL
        );
        $installer->getMainConfigModifier()->insert('/listing/product/revise/total/amazon/', 'mode', '0', NULL);
        $installer->getMainConfigModifier()->insert(
            '/listing/product/revise/total/amazon/', 'max_allowed_instructions_count', '2000', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/list/', 'priority_coefficient', '25', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/list/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/list/', 'min_allowed_wait_interval', '3600', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/relist/', 'priority_coefficient', '125', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/relist/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/relist/', 'min_allowed_wait_interval', '1800', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_qty/', 'priority_coefficient', '500', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_qty/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_qty/', 'min_allowed_wait_interval', '900', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_price/', 'priority_coefficient', '250', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_price/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_price/', 'min_allowed_wait_interval', '1800', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_details/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_details/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_details/', 'min_allowed_wait_interval', '7200', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_images/', 'priority_coefficient', '50', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_images/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/revise_images/', 'min_allowed_wait_interval', '7200', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/stop/', 'priority_coefficient', '1000', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/stop/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/stop/', 'min_allowed_wait_interval', '600', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/delete/', 'priority_coefficient', '1000', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/delete/', 'wait_increase_coefficient', '100', NULL
        );
        $installer->getMainConfigModifier()->insert(
            '/amazon/listing/product/action/delete/', 'min_allowed_wait_interval', '600', NULL
        );

        if ($installer->tableExists($synchronizationConfigTable)) {
            $connection->dropTable($synchronizationConfigTable);
        }

        $nickPrefix = "cron_task_";

        $lockItemTableName = $installer->getTablesObject()->getFullName("lock_item");

        $cronTaskLockItems = $installer->getConnection()->query("
  SELECT * FROM `{$lockItemTableName}` WHERE `nick` LIKE 'cron_task_%';
")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cronTaskLockItems as $cronTaskLockItem) {
            $nick = $cronTaskLockItem['nick'];

            if (strpos($nick, 'ebay_actions') !== false) {
                $connection->update(
                    $lockItemTableName,
                    array('nick' => 'cron_task_ebay_listing_product_process_actions'),
                    array('id = ?' => $cronTaskLockItem['id'])
                );
                continue;
            }

            $connection->delete($lockItemTableName, array('id = ?' => $cronTaskLockItem['id']));
        }

        $processingRunnerModelNameMap = array(
            'M2ePro/Amazon_Synchronization_OtherListings_Update_ProcessingRunner'
            => 'M2ePro/Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData_ProcessingRunner',
            'M2ePro/Amazon_Synchronization_OtherListings_Update_Blocked_ProcessingRunner'
            => 'M2ePro/Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData_Blocked_ProcessingRunner',
            'M2ePro/Amazon_Synchronization_ListingsProducts_Update_ProcessingRunner'
            => 'M2ePro/Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_ProcessingRunner',
            'M2ePro/Amazon_Synchronization_ListingsProducts_Update_Blocked_ProcessingRunner'
            => 'M2ePro/Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Blocked_ProcessingRunner',
            'M2ePro/Amazon_Synchronization_ListingsProducts_Update_Defected_ProcessingRunner'
            => 'M2ePro/Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Defected_ProcessingRunner',
            'M2ePro/Ebay_Synchronization_OtherListings_Update_ProcessingRunner'
            => 'M2ePro/Cron_Task_Ebay_Listing_Other_Channel_SynchronizeData_ProcessingRunner',
        );

        $responserModelNameMap = array(
            'M2ePro/Amazon_Synchronization_OtherListings_Update_Responser'
                => 'M2ePro/Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData_Responser',
            'M2ePro/Amazon_Synchronization_OtherListings_Update_Blocked_Responser'
                => 'M2ePro/Cron_Task_Amazon_Listing_Other_Channel_SynchronizeData_Blocked_Responser',
            'M2ePro/Amazon_Synchronization_ListingsProducts_Update_Responser'
                => 'M2ePro/Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Responser',
            'M2ePro/Amazon_Synchronization_ListingsProducts_Update_Blocked_Responser'
                => 'M2ePro/Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Blocked_Responser',
            'M2ePro/Amazon_Synchronization_ListingsProducts_Update_Defected_Responser'
                => 'M2ePro/Cron_Task_Amazon_Listing_Product_Channel_SynchronizeData_Defected_Responser',
            'M2ePro/Amazon_Synchronization_Orders_Cancel_Responser'
                => 'M2ePro/Cron_Task_Amazon_Order_Cancel_Responser',
            'M2ePro/Amazon_Synchronization_Orders_Receive_Details_Responser'
                => 'M2ePro/Cron_Task_Amazon_Order_Receive_Details_Responser',
            'M2ePro/Amazon_Synchronization_Orders_Refund_Responser'
                => 'M2ePro/Cron_Task_Amazon_Order_Refund_Responser',
            'M2ePro/Amazon_Synchronization_Orders_Update_Responser'
                => 'M2ePro/Cron_Task_Amazon_Order_Update_Responser',
            'M2ePro/Ebay_Synchronization_OtherListings_Update_Responser'
                => 'M2ePro/Cron_Task_Ebay_Listing_Other_Channel_SynchronizeData_Responser',
        );

        $processingTable = $installer->getTablesObject()->getFullName('processing');

        $processings = $installer->getConnection()->query("
    SELECT * FROM {$processingTable};
")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($processings as $processing) {
            $isUpdated = false;

            if (isset($processingRunnerModelNameMap[$processing['model']])) {
                $processing['model'] = $processingRunnerModelNameMap[$processing['model']];
                $isUpdated = true;
            }

            $params = json_decode($processing['params'], true);

            if (isset($params['responser_model_name']) &&
                isset($responserModelNameMap[$params['responser_model_name']]))
            {
                $params['responser_model_name'] = $responserModelNameMap[$params['responser_model_name']];
                $processing['params'] = json_encode($params);
                $isUpdated = true;
            }

            if ($isUpdated) {
                $connection->update(
                    $processingTable,
                    array('model' => $processing['model'], 'params' => $processing['params']),
                    array('id = ?' => $processing['id'])
                );
            }
        }

        // Processing improvement
        //########################################

        if ($installer->getTablesObject()->isExists('connector_pending_requester_single') &&
            !$installer->getTablesObject()->isExists('connector_command_pending_processing_single')
        ) {
            $connection->renameTable(
                $installer->getTablesObject()->getFullName('connector_pending_requester_single'),
                $installer->getTablesObject()->getFullName('connector_command_pending_processing_single')
            );
        }

        if ($installer->getTablesObject()->isExists('connector_pending_requester_partial') &&
            !$installer->getTablesObject()->isExists('connector_command_pending_processing_partial')
        ) {
            $connection->renameTable(
                $installer->getTablesObject()->getFullName('connector_pending_requester_partial'),
                $installer->getTablesObject()->getFullName('connector_command_pending_processing_partial')
            );
        }

        $installer->getTableModifier('processing')
            ->addColumn('type', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'params', true);

        $processingTable = $installer->getTablesObject()->getFullName('processing');
        $connectorCommandPendingProcessingPartialTable = $installer->getTablesObject()
            ->getFullName('connector_command_pending_processing_partial');

        $partialProcessings = $installer->getConnection()->query("
SELECT `p`.`id` FROM `{$processingTable}` AS `p`
INNER JOIN `{$connectorCommandPendingProcessingPartialTable}` AS `cprp` ON `cprp`.`processing_id` = `p`.`id`
WHERE `p`.`type` = 0
")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($partialProcessings as $partialProcessing) {
            $connection->update(
                $processingTable,
                array('type' => 2),
                array('id = ?' => $partialProcessing['id'])
            );
        }

        $singleProcessings = $installer->getConnection()->query("
SELECT `p`.`id` FROM `{$processingTable}` AS `p`
LEFT JOIN `{$connectorCommandPendingProcessingPartialTable}` AS `cprp` ON `cprp`.`processing_id` = `p`.`id`
WHERE `p`.`type` = 0 AND `cprp`.`id` IS NULL
")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($singleProcessings as $partialProcessing) {
            $connection->update(
                $processingTable,
                array('type' => 1),
                array('id = ?' => $partialProcessing['id'])
            );
        }
    }

    //########################################
}