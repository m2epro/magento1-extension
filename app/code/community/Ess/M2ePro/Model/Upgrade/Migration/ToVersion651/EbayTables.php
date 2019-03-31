<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_EbayTables extends Ess_M2ePro_Model_Upgrade_Migration_Abstract
{
    //########################################

    public function run()
    {
        $this->marketplace();
        $this->account();
        $this->listings();
        $this->templates();
        $this->orders();
    }

    //########################################

    private function marketplace()
    {
        $this->installer->getTableModifier('ebay_marketplace')
            ->addColumn('is_in_store_pickup', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_click_and_collect', true, false)
            ->addColumn('is_epid', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_holiday_return', true, false)
            ->addColumn('is_ktype', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_epid', true, false)
            ->dropColumn('is_multi_currency', true, false)
            ->commit();

        $this->installer->run(<<<SQL

UPDATE `m2epro_ebay_marketplace`
SET `is_in_store_pickup` = 1
WHERE `marketplace_id` = 1 AND `origin_country` = 'us'
OR    `marketplace_id` = 4 AND `origin_country` = 'au'
OR    `marketplace_id` = 3 AND `origin_country` = 'gb';

UPDATE `m2epro_ebay_marketplace`
SET `is_epid` = 1
WHERE `marketplace_id` IN (3, 8, 9);

UPDATE `m2epro_ebay_marketplace`
SET `is_ktype` = 1
WHERE `marketplace_id` IN (3, 4, 7, 8, 10, 13);

UPDATE `m2epro_marketplace`
SET `url` = 'ebay.com/motors'
WHERE `id` = 9;

UPDATE `m2epro_ebay_marketplace`
SET `currency` = 'CAD'
WHERE `marketplace_id` IN (2, 19);

UPDATE `m2epro_marketplace`
SET `group_title` = 'Australia Region'
WHERE `id` IN (4);

SQL
        );

        $this->installer->getTableModifier('ebay_dictionary_motor_epid')
            ->addColumn('scope', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_custom', true);

        $this->installer->run(<<<SQL
UPDATE `m2epro_ebay_dictionary_motor_epid`
SET `scope` = 1;
SQL
        );
    }

    private function account()
    {
        $this->installer->getTableModifier('ebay_account')
            ->addColumn('user_preferences', 'TEXT', 'NULL', 'info', false, false)
            ->addColumn('rate_tables', 'TEXT', 'NULL', 'user_preferences', false, false)
            ->addColumn('sell_api_token_session', 'VARCHAR(255)', 'NULL', 'token_expired_date', false, false)
            ->addColumn('sell_api_token_expired_date', 'DATETIME', 'NULL', 'sell_api_token_session', false, false)
            ->commit();
    }

    private function listings()
    {
        $this->installer->getTableModifier('ebay_listing_product')
            ->addColumn('online_duration', 'INT(11) UNSIGNED', 'NULL', 'online_title', false, false)
            ->addColumn('item_uuid', 'VARCHAR(32)', 'NULL', 'ebay_item_id', true, false)
            ->addColumn('is_duplicate', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'item_uuid', true, false)
            ->addColumn('online_is_variation', 'TINYINT(2) UNSIGNED', 'NULL', 'is_duplicate', true, false)
            ->addColumn("online_is_auction_type", "TINYINT(2) UNSIGNED", "NULL", "online_is_variation", true, false)
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

        $this->installer->getTableModifier('ebay_listing_other')
            ->addColumn('online_duration', 'INT(11) UNSIGNED', 'NULL', 'currency', false, false)
            ->dropColumn('old_items', true, false)
            ->commit();

        $this->installer->run(<<<SQL

UPDATE `m2epro_listing_other`
SET `status` = 3
WHERE `component_mode` = 'ebay' AND `status` = 6;

UPDATE `m2epro_amazon_listing_other`
SET `title` = '--'
WHERE `title` = '' OR `title` = 'Unknown (can\'t be received)' OR `title` IS NULL;

SQL
        );

        $listingTable = $this->getFullTableName('listing');
        $listings = $this->installer->getConnection()->query("
SELECT * FROM {$listingTable} WHERE `additional_data` LIKE '%mode_same_category_data%';
")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($listings as $listing) {

            $listingId      = $listing['id'];
            $additionalData = (array)@json_decode($listing['additional_data'], true);

            if (!empty($additionalData['mode_same_category_data']['specifics'])) {

                foreach ($additionalData['mode_same_category_data']['specifics'] as &$specific) {

                    unset($specific['attribute_id'], $specific['mode_relation_id']);

                    if (!empty($specific['value_ebay_recommended'])) {

                        $recommendedValues = (array)@json_decode($specific['value_ebay_recommended'], true);

                        foreach ($recommendedValues as &$recommendedValue) {
                            if (!empty($recommendedValue['value'])) {
                                $recommendedValue = $recommendedValue['value'];
                            }
                        }
                        unset($recommendedValue);

                        $specific['value_ebay_recommended'] = json_encode($recommendedValues);
                    }
                }
                unset($specific);
            }

            $this->installer->getConnection()->update(
                $listingTable,
                array('additional_data' => json_encode($additionalData)),
                array('id = ?' => $listingId)
            );
        }

        $this->installer->getTableModifier('ebay_listing')
            ->addColumn('parts_compatibility_mode', 'VARCHAR(10)', 'NULL', 'product_add_ids');

        $this->installer->run(<<<SQL
UPDATE `m2epro_ebay_listing` mel
INNER JOIN `m2epro_listing` ml ON ml.id = mel.listing_id
SET `parts_compatibility_mode` = 'ktypes'
WHERE ml.marketplace_id IN (3, 8);
SQL
        );

        $mainTableName      = $this->getFullTableName("ebay_listing_product");
        $productTableName   = $this->getFullTableName("listing_product");
        $variationTableName = $this->getFullTableName("listing_product_variation");
        $optionTableName    = $this->getFullTableName("listing_product_variation_option");

        $stmt = $this->installer->getConnection()->query(<<<SQL
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

                $this->installer->getConnection()->update($mainTableName,
                    array("online_is_variation" => 1),
                    array("listing_product_id IN (?)" => $itemsIds)
                );
                $itemsIds = array();
            }

            if ($row === false) {
                break;
            }
        }

        $stmt = $this->installer->getConnection()->query(<<<SQL
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

                $this->installer->getConnection()->update($mainTableName,
                    array("online_is_auction_type" => 1),
                    array("listing_product_id IN (?)" => $itemsIds)
                );
                $itemsIds = array();
            }

            if ($row === false) {
                break;
            }
        }

        $listingProductVariationOption = $this->getFullTableName('listing_product_variation_option');
        $listingProductVariation       = $this->getFullTableName('listing_product_variation');

        $ebayListingProductVariationOption = $this->getFullTableName('ebay_listing_product_variation_option');
        $ebayListingProductVariation       = $this->getFullTableName('ebay_listing_product_variation');

        $this->installer->run(<<<SQL
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

        $stmt = $this->installer->getConnection()->query(<<<SQL
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
            $this->installer->getConnection()->query(<<<SQL
UPDATE `{$ebayListingProductVariation}`
SET `add` = 0, `delete` = 0
WHERE `listing_product_variation_id` IN ({$originalVariationsIdsPart});
SQL
            );
        }

        foreach (array_chunk($duplicatedVariationsIds, 1000) as $duplicatedVariationsIdsPart) {

            $duplicatedVariationsIdsPart = implode(',', $duplicatedVariationsIdsPart);
            $this->installer->getConnection()->query(<<<SQL
DELETE `lpv`, `elpv`
    FROM `{$listingProductVariation}` `lpv`
INNER JOIN `{$ebayListingProductVariation}` `elpv`
    ON `lpv`.`id` = `elpv`.`listing_product_variation_id`
WHERE `lpv`.`id` IN ({$duplicatedVariationsIdsPart});
SQL
            );

            $this->installer->getConnection()->query(<<<SQL
DELETE `lpvo`, `elpvo`
    FROM `{$listingProductVariationOption}` `lpvo`
INNER JOIN `{$ebayListingProductVariationOption}` `elpvo`
    ON `lpvo`.`id` = `elpvo`.`listing_product_variation_option_id`
WHERE `lpvo`.`listing_product_variation_id` IN ({$duplicatedVariationsIdsPart});
SQL
            );
        }

        $queryStmt = $this->installer->getConnection()
            ->select()
            ->from(
                $this->installer->getFullTableName('listing_product_variation'),
                array('id', 'additional_data')
            )
            ->where("component_mode = 'ebay'")
            ->where("additional_data LIKE '%ebay_mpn_value%'")
            ->query();

        while ($row = $queryStmt->fetch()) {

            $additionalData = (array)@json_decode($row['additional_data'], true);
            $additionalData['online_product_details']['mpn'] = $additionalData['ebay_mpn_value'];
            unset($additionalData['ebay_mpn_value']);
            $additionalData = json_encode($additionalData);

            $this->installer->getConnection()->update(
                $this->installer->getFullTableName('listing_product_variation'),
                array('additional_data' => $additionalData),
                array('id = ?' => (int)$row['id'])
            );
        }
    }

    private function templates()
    {
        $this->installer->getTableModifier('ebay_template_shipping')
            ->addColumn(
                'local_shipping_rate_table', 'TEXT', 'NULL',
                'local_shipping_rate_table_mode', false, false
            )
            ->addColumn(
                'international_shipping_rate_table', 'TEXT', 'NULL',
                'international_shipping_rate_table_mode', false, false
            )
            ->commit();

        $isColumnExists = $this->installer->getTableModifier('ebay_template_shipping')
                ->isColumnExists('local_shipping_rate_table_mode');

        if ($isColumnExists) {

            $query = $this->installer->getConnection()->query("
SELECT `account_id` FROM {$this->getFullTableName('ebay_account')}
");
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

            $tableName = $this->installer->getTablesObject()->getFullName('ebay_template_shipping');

            $this->installer->getConnection()->update(
                $tableName,
                array('local_shipping_rate_table' => json_encode($enabledShippingRateTable)),
                array('local_shipping_rate_table_mode = ?' => 1)
            );

            $this->installer->getConnection()->update(
                $tableName,
                array('international_shipping_rate_table' => json_encode($enabledShippingRateTable)),
                array('international_shipping_rate_table_mode = ?' => 1)
            );

            $this->installer->getConnection()->update(
                $tableName,
                array('local_shipping_rate_table' => json_encode($disabledShippingRateTable)),
                array('local_shipping_rate_table_mode = ?' => 0)
            );

            $this->installer->getConnection()->update(
                $tableName,
                array('international_shipping_rate_table' => json_encode($disabledShippingRateTable)),
                array('international_shipping_rate_table_mode = ?' => 0)
            );
        }

        $this->installer->getTableModifier('ebay_template_shipping')
            ->dropColumn('local_shipping_rate_table_mode', true, false)
            ->dropColumn('international_shipping_rate_table_mode', true, false)
            ->addColumn(
                'dispatch_time_mode', 'TINYINT(2) UNSIGNED NOT NULL', 1, 'address_custom_attribute', false, false
            )
            ->addColumn('dispatch_time_attribute', 'VARCHAR(255)', NULL, 'dispatch_time_value', false, false)
            ->renameColumn('dispatch_time', 'dispatch_time_value', true, false)
            ->renameColumn('local_shipping_discount_mode', 'local_shipping_discount_promotional_mode', true, false)
            ->renameColumn(
                'local_shipping_discount_profile_id', 'local_shipping_discount_combined_profile_id', true, false
            )
            ->renameColumn(
                'international_shipping_discount_mode', 'international_shipping_discount_promotional_mode',
                true, false
            )
            ->renameColumn(
                'international_shipping_discount_profile_id', 'international_shipping_discount_combined_profile_id',
                true, false
            )
            ->commit();

        $this->installer->getTableModifier('ebay_template_synchronization')
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

        $this->installer->run(<<<SQL
UPDATE `m2epro_ebay_template_synchronization`
SET `stop_mode` = 1
WHERE (`stop_status_disabled`+`stop_out_off_stock`+`stop_qty_magento`+`stop_qty_calculated`) > 0;
SQL
        );

        $this->installer->getTableModifier('ebay_template_selling_format')
            ->addColumn('lot_size_mode', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'qty_max_posted_value', false, false)
            ->addColumn('lot_size_custom_value', 'INT(11) UNSIGNED', 'NULL', 'lot_size_mode', false, false)
            ->addColumn('lot_size_attribute', 'VARCHAR(255)', 'NULL', 'lot_size_custom_value', false, false)
            ->commit();
    }

    private function orders()
    {
        $this->installer->getTableModifier('ebay_order')
            ->addIndex('purchase_create_date');

        $this->installer->getTableModifier('ebay_order_item')
            ->addColumn('waste_recycling_fee', 'DECIMAL(12, 4) NOT NULL', '0.0000', 'final_fee');
    }

    //########################################
}