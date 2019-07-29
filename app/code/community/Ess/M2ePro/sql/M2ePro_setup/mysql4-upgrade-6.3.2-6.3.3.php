<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    ALTER TABLE `m2epro_amazon_listing`
        ADD COLUMN `gift_wrap_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0
            AFTER `gallery_images_attribute`,
        ADD COLUMN `gift_wrap_attribute` VARCHAR(255) NOT NULL
            AFTER `gift_wrap_mode`,
        ADD COLUMN `gift_message_mode` TINYINT(2) UNSIGNED NOT NULL
            AFTER `gift_wrap_attribute`,
        ADD COLUMN `gift_message_attribute` VARCHAR(255) NOT NULL
            AFTER `gift_message_mode`;

    ALTER TABLE `m2epro_ebay_template_synchronization`
        ADD COLUMN `revise_update_price_max_allowed_deviation_mode` TINYINT(2) UNSIGNED NOT NULL
            AFTER `revise_update_price`,
        ADD COLUMN `revise_update_price_max_allowed_deviation` INT(11) UNSIGNED DEFAULT NULL
            AFTER `revise_update_price_max_allowed_deviation_mode`;

    ALTER TABLE `m2epro_amazon_template_synchronization`
        ADD COLUMN `revise_update_price_max_allowed_deviation_mode` TINYINT(2) UNSIGNED NOT NULL
            AFTER `revise_update_price`,
        ADD COLUMN `revise_update_price_max_allowed_deviation` INT(11) UNSIGNED DEFAULT NULL
            AFTER `revise_update_price_max_allowed_deviation_mode`;

    ALTER TABLE `m2epro_buy_template_synchronization`
        ADD COLUMN `revise_update_price_max_allowed_deviation_mode` TINYINT(2) UNSIGNED NOT NULL
            AFTER `revise_update_price`,
        ADD COLUMN `revise_update_price_max_allowed_deviation` INT(11) UNSIGNED DEFAULT NULL
            AFTER `revise_update_price_max_allowed_deviation_mode`;

    ALTER TABLE `m2epro_amazon_listing_product`
        ADD COLUMN `defected_messages` TEXT DEFAULT NULL AFTER `is_general_id_owner`;

    ### Orders mode removing

    ALTER TABLE `m2epro_ebay_account`
        DROP COLUMN `orders_mode`;
    ALTER TABLE `m2epro_amazon_account`
        DROP COLUMN `orders_mode`;
    ALTER TABLE `m2epro_buy_account`
        DROP COLUMN `orders_mode`;

    ### eBay policies removing

    ALTER TABLE `m2epro_ebay_listing`
        DROP COLUMN `template_payment_policy_id`,
        DROP COLUMN `template_shipping_policy_id`,
        DROP COLUMN `template_return_policy_id`;

    ALTER TABLE `m2epro_ebay_listing_product`
        DROP COLUMN `template_payment_policy_id`,
        DROP COLUMN `template_shipping_policy_id`,
        DROP COLUMN `template_return_policy_id`;

    ### ignore_next_inventory_synch removing

    ALTER TABLE `m2epro_amazon_listing_product`
        DROP COLUMN `ignore_next_inventory_synch`;

    ALTER TABLE `m2epro_buy_listing_product`
        DROP COLUMN `ignore_next_inventory_synch`;

    ### Play component removing

    DELETE FROM `m2epro_lock_item` WHERE `nick` LIKE '%_play%';
    DELETE FROM `m2epro_lock_item` WHERE `nick` LIKE 'play%';

    DELETE FROM `m2epro_config` WHERE `group` LIKE '%/play/%';
    DELETE FROM `m2epro_synchronization_config` WHERE `group` LIKE '%/play/%';
    DELETE FROM `m2epro_primary_config` WHERE `group` LIKE '%/play/%';

    DELETE FROM `m2epro_order_change` WHERE `component` = 'play';
    DELETE FROM `m2epro_order_repair` WHERE `component` = 'play';
    DELETE FROM `m2epro_processing_request` WHERE `component` = 'play';

    DELETE FROM `m2epro_account` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_auto_category_group` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_log` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_other` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_other_log` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_product` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_product_variation` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_product_variation_option` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_marketplace` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_order` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_order_item` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_order_log` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_stop_queue` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_synchronization_log` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_template_selling_format` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_template_synchronization` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_template_description` WHERE `component_mode` = 'play';

    DROP TABLE IF EXISTS `m2epro_play_account`;
    DROP TABLE IF EXISTS `m2epro_play_item`;
    DROP TABLE IF EXISTS `m2epro_play_listing`;
    DROP TABLE IF EXISTS `m2epro_play_listing_auto_category_group`;
    DROP TABLE IF EXISTS `m2epro_play_listing_other`;
    DROP TABLE IF EXISTS `m2epro_play_listing_product`;
    DROP TABLE IF EXISTS `m2epro_play_listing_product_variation`;
    DROP TABLE IF EXISTS `m2epro_play_listing_product_variation_option`;
    DROP TABLE IF EXISTS `m2epro_play_marketplace`;
    DROP TABLE IF EXISTS `m2epro_play_order`;
    DROP TABLE IF EXISTS `m2epro_play_order_item`;
    DROP TABLE IF EXISTS `m2epro_play_processed_inventory`;
    DROP TABLE IF EXISTS `m2epro_play_template_selling_format`;
    DROP TABLE IF EXISTS `m2epro_play_template_synchronization`;

    DELETE FROM `m2epro_wizard` WHERE `nick` = 'play';

    INSERT INTO `m2epro_wizard` (`nick`, `view`, `status`, `step`, `type`, `priority`)
        SELECT 'removedPlay', 'common', 0, NULL, 0, MAX( `priority` )+1 FROM `m2epro_wizard`;
*/

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing');

if ($connection->tableColumnExists($tempTable, 'gift_wrap_mode') === false) {
    $connection->addColumn(
        $tempTable, 'gift_wrap_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `gallery_images_attribute`'
    );
}

if ($connection->tableColumnExists($tempTable, 'gift_wrap_attribute') === false) {
    $connection->addColumn(
        $tempTable, 'gift_wrap_attribute',
        'VARCHAR(255) NOT NULL AFTER `gift_wrap_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'gift_message_mode') === false) {
    $connection->addColumn(
        $tempTable, 'gift_message_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `gift_wrap_attribute`'
    );
}

if ($connection->tableColumnExists($tempTable, 'gift_message_attribute') === false) {
    $connection->addColumn(
        $tempTable, 'gift_message_attribute',
        'VARCHAR(255) NOT NULL AFTER `gift_message_mode`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_synchronization');

if ($connection->tableColumnExists($tempTable, 'revise_update_price_max_allowed_deviation_mode') === false) {
    $connection->addColumn(
        $tempTable, 'revise_update_price_max_allowed_deviation_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `revise_update_price`'
    );
}

if ($connection->tableColumnExists($tempTable, 'revise_update_price_max_allowed_deviation') === false) {
    $connection->addColumn(
        $tempTable, 'revise_update_price_max_allowed_deviation',
        'INT(11) UNSIGNED DEFAULT NULL AFTER `revise_update_price_max_allowed_deviation_mode`'
    );
}

$tempTable = $installer->getTable('m2epro_amazon_template_synchronization');

if ($connection->tableColumnExists($tempTable, 'revise_update_price_max_allowed_deviation_mode') === false) {
    $connection->addColumn(
        $tempTable, 'revise_update_price_max_allowed_deviation_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `revise_update_price`'
    );
}

if ($connection->tableColumnExists($tempTable, 'revise_update_price_max_allowed_deviation') === false) {
    $connection->addColumn(
        $tempTable, 'revise_update_price_max_allowed_deviation',
        'INT(11) UNSIGNED DEFAULT NULL AFTER `revise_update_price_max_allowed_deviation_mode`'
    );
}

$tempTable = $installer->getTable('m2epro_buy_template_synchronization');

if ($connection->tableColumnExists($tempTable, 'revise_update_price_max_allowed_deviation_mode') === false) {
    $connection->addColumn(
        $tempTable, 'revise_update_price_max_allowed_deviation_mode',
        'TINYINT(2) UNSIGNED NOT NULL AFTER `revise_update_price`'
    );
}

if ($connection->tableColumnExists($tempTable, 'revise_update_price_max_allowed_deviation') === false) {
    $connection->addColumn(
        $tempTable, 'revise_update_price_max_allowed_deviation',
        'INT(11) UNSIGNED DEFAULT NULL AFTER `revise_update_price_max_allowed_deviation_mode`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');

if ($connection->tableColumnExists($tempTable, 'defected_messages') === false) {
    $connection->addColumn(
        $tempTable, 'defected_messages',
        'TEXT DEFAULT NULL AFTER `is_general_id_owner`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_account');

if ($connection->tableColumnExists($tempTable, 'orders_mode') !== false) {
    $connection->dropColumn($tempTable, 'orders_mode');
}

$tempTable = $installer->getTable('m2epro_amazon_account');

if ($connection->tableColumnExists($tempTable, 'orders_mode') !== false) {
    $connection->dropColumn($tempTable, 'orders_mode');
}

$tempTable = $installer->getTable('m2epro_buy_account');

if ($connection->tableColumnExists($tempTable, 'orders_mode') !== false) {
    $connection->dropColumn($tempTable, 'orders_mode');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_listing');

if ($connection->tableColumnExists($tempTable, 'template_payment_policy_id') !== false) {
    $connection->dropColumn($tempTable, 'template_payment_policy_id');
}

if ($connection->tableColumnExists($tempTable, 'template_shipping_policy_id') !== false) {
    $connection->dropColumn($tempTable, 'template_shipping_policy_id');
}

if ($connection->tableColumnExists($tempTable, 'template_return_policy_id') !== false) {
    $connection->dropColumn($tempTable, 'template_return_policy_id');
}

$tempTable = $installer->getTable('m2epro_ebay_listing_product');

if ($connection->tableColumnExists($tempTable, 'template_payment_policy_id') !== false) {
    $connection->dropColumn($tempTable, 'template_payment_policy_id');
}

if ($connection->tableColumnExists($tempTable, 'template_shipping_policy_id') !== false) {
    $connection->dropColumn($tempTable, 'template_shipping_policy_id');
}

if ($connection->tableColumnExists($tempTable, 'template_return_policy_id') !== false) {
    $connection->dropColumn($tempTable, 'template_return_policy_id');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');

if ($connection->tableColumnExists($tempTable, 'ignore_next_inventory_synch') !== false) {
    $connection->dropColumn($tempTable, 'ignore_next_inventory_synch');
}

$tempTable = $installer->getTable('m2epro_buy_listing_product');

if ($connection->tableColumnExists($tempTable, 'ignore_next_inventory_synch') !== false) {
    $connection->dropColumn($tempTable, 'ignore_next_inventory_synch');
}

//########################################

$installer->run(<<<SQL

    UPDATE `m2epro_config`
    SET `group` = '/cron/task/logs_clearing/'
    WHERE `group` = '/cron/task/logs_cleaning/';

    UPDATE `m2epro_config`
    SET `group` = '/logs/clearing/listings/'
    WHERE `group` = '/logs/cleaning/listings/';

    UPDATE `m2epro_config`
    SET `group` = '/logs/clearing/other_listings/'
    WHERE `group` = '/logs/cleaning/other_listings/';

    UPDATE `m2epro_config`
    SET `group` = '/logs/clearing/synchronizations/'
    WHERE `group` = '/logs/cleaning/synchronizations/';

    UPDATE `m2epro_config`
    SET `group` = '/logs/clearing/orders/'
    WHERE `group` = '/logs/cleaning/orders/';

    UPDATE `m2epro_registry`
    SET `key` = '/wizard/new_amazon_description_templates/'
    WHERE `key` = 'wizard_new_amazon_description_templates';

    UPDATE `m2epro_registry`
    SET `key` = '/wizard/license_form_data/'
    WHERE `key` = 'wizard_license_form_data';

    UPDATE `m2epro_registry`
    SET `key` = '/wizard/migrationToV6_notes_html/'
    WHERE `key` = 'wizard_migrationToV6_notes_html';

    UPDATE `m2epro_cache_config`
    SET `group` = '/installation/'
    WHERE `group` = '/installation/version/'
    AND `key` = 'last_version';

    UPDATE `m2epro_ebay_account`
    SET `magento_orders_settings` = REPLACE(
        `magento_orders_settings`,
        '"qty_reservation":{"days":"0"}',
        '"qty_reservation":{"days":"1"}'
    );

    UPDATE `m2epro_amazon_account`
    SET `magento_orders_settings` = REPLACE(
        `magento_orders_settings`,
        '"qty_reservation":{"days":"0"}',
        '"qty_reservation":{"days":"1"}'
    );

    UPDATE `m2epro_ebay_template_selling_format`
    SET `qty_max_posted_value` = 100
    WHERE `qty_min_posted_value` = 1
    AND `qty_max_posted_value` = 10;

    UPDATE `m2epro_amazon_template_selling_format`
    SET `qty_max_posted_value` = 100
    WHERE `qty_min_posted_value` = 1
    AND `qty_max_posted_value` = 10;

    UPDATE `m2epro_buy_template_selling_format`
    SET `qty_max_posted_value` = 100
    WHERE `qty_min_posted_value` = 1
    AND `qty_max_posted_value` = 10;

    UPDATE `m2epro_ebay_template_synchronization`
    SET `revise_update_qty_max_applied_value` = 5
    WHERE `revise_update_qty_max_applied_value` = 10;

    UPDATE `m2epro_amazon_template_synchronization`
    SET `revise_update_qty_max_applied_value` = 5
    WHERE `revise_update_qty_max_applied_value` = 10;

    UPDATE `m2epro_buy_template_synchronization`
    SET `revise_update_qty_max_applied_value` = 5
    WHERE `revise_update_qty_max_applied_value` = 10;

    UPDATE `m2epro_ebay_dictionary_category`
    SET    `item_specifics` = NULL
    WHERE  `item_specifics` IS NOT NULL;

    DELETE FROM `m2epro_cache_config`
    WHERE `group` = '/server/baseurl/'
    AND   `key` = 'date_of_emergency_state';

    DELETE FROM `m2epro_synchronization_config`
    WHERE `group` = '/ebay/policies/'
    OR `group` = '/ebay/policies/receive/';

    UPDATE `m2epro_cache_config`
    SET `group` = '/ebay/category/recent/store/secondary/'
    WHERE `group` = 'ebay/category/recent/store/secondary/';

    DROP TABLE IF EXISTS `m2epro_ebay_account_policy`;
    DROP TABLE IF EXISTS `m2epro_ebay_template_policy`;

    UPDATE `m2epro_synchronization_config`
    SET `value` = '86400'
    WHERE `group` = '/amazon/defaults/update_listings_products/' AND `key` = 'interval';

    UPDATE `m2epro_synchronization_config`
    SET `value` = '86400'
    WHERE `group` = '/buy/defaults/update_listings_products/' AND `key` = 'interval';

    UPDATE `m2epro_processing_request`
    SET `next_part` = 1
    WHERE `perform_type` = 2 AND `next_part` IS NULL;

SQL
);

//########################################

$tempTable = $installer->getTable('m2epro_synchronization_config');

$tempRow = $connection->query("
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/amazon/defaults/update_defected_listings_products/'
    AND   `key` = 'mode'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/amazon/defaults/update_defected_listings_products/', 'interval', '259200', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
('/amazon/defaults/update_defected_listings_products/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
('/amazon/defaults/update_defected_listings_products/', 'last_time', NULL, 'Last check time',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
    );
}

//########################################

$tempTable = $installer->getTable('m2epro_ebay_template_category_specific');

$specifics = $connection->query("
    SELECT `id`, `value_custom_value`
    FROM   `{$tempTable}`
    WHERE  `value_custom_value` != ''
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($specifics as $specific) {

    $id = (int)$specific['id'];
    $values = $connection->quote(json_encode((array)$specific['value_custom_value']));

    $installer->run(<<<SQL
        UPDATE `m2epro_ebay_template_category_specific`
        SET    `value_custom_value` = {$values}
        WHERE  `id` = {$id};
SQL
    );
}

// Amazon Recent categories migration
//########################################

$tempTable = $installer->getTable('m2epro_cache_config');

$stmt = $connection->query("
    SELECT mcc.`group`, mcc.`value`
    FROM `{$tempTable}` mcc
    WHERE mcc.`group` LIKE '/amazon/category/recent/marketplace/%'
");

if (false !== $stmt) {

    $allRecentCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($allRecentCategories)) {

        $resultRecentCategories = array();

        foreach ($allRecentCategories as $recentCategory) {
            $recentCategoryParts = explode('/', trim($recentCategory['group'], '/'));
            $marketplaceId = array_pop($recentCategoryParts);
            $resultRecentCategories[$marketplaceId][] = @json_decode($recentCategory['value'], true);
        }

        $date = $connection->quote(date('Y-m-d H:i:s', gmdate('U')));
        $recentCategoriesData = $connection->quote(@json_encode($resultRecentCategories));

        $installer->run(<<<SQL
        INSERT INTO `m2epro_registry` (`key`, `value`, `update_date`, `create_date`)
        VALUES ('/amazon/category/recent/', {$recentCategoriesData}, {$date}, {$date});
SQL
        );
    }
}

$installer->run(<<<SQL
    DELETE FROM `m2epro_cache_config`
    WHERE `group` LIKE '/amazon/category/recent/marketplace/%';
SQL
);

// Ebay recent categories migration
// ---------------------------------------

$tempTable = $installer->getTable('m2epro_cache_config');

$stmt = $connection->query("
    SELECT mcc.`group`, mcc.`key`, mcc.`value`
    FROM `{$tempTable}` mcc
    WHERE mcc.`group` LIKE '/ebay/category/recent/%'
");

if (false !== $stmt) {

    $allRecentCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($allRecentCategories)) {

        $resultRecentCategories = array();

        foreach ($allRecentCategories as $recentCategory) {

            $availableGroups = array(
                '/ebay/category/recent/ebay/main/'      => '/ebay/main/',
                '/ebay/category/recent/ebay/secondary/' => '/ebay/secondary/',
                '/ebay/category/recent/store/main/'     => '/store/main/',
                '/ebay/category/recent/store/secondary/' => '/store/secondary/',
            );

            if (!isset($availableGroups[$recentCategory['group']])) {
                continue;
            }

            $group = $availableGroups[$recentCategory['group']];
            $resultRecentCategories[$group][$recentCategory['key']] = $recentCategory['value'];
        }

        $date = $connection->quote(date('Y-m-d H:i:s', gmdate('U')));
        $recentCategoriesData = $connection->quote(@json_encode($resultRecentCategories));

        $installer->run(<<<SQL
        INSERT INTO `m2epro_registry` (`key`, `value`, `update_date`, `create_date`)
        VALUES ('/ebay/category/recent/', {$recentCategoriesData}, {$date}, {$date});
SQL
        );
    }
}

$installer->run(<<<SQL
    DELETE FROM `m2epro_cache_config`
    WHERE `group` LIKE '/ebay/category/recent/%';
SQL
);

// Exceptions filters table migration
//########################################

$tempTable = $installer->getTable('m2epro_exceptions_filters');

if ($installer->tableExists($tempTable)) {

    $stmt = $connection->query(
        "SELECT `preg_match`, `type`
         FROM `{$tempTable}`"
    );

    if (false !== $stmt) {

        $allExceptionsFilters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($allExceptionsFilters)) {

            $date = $connection->quote(date('Y-m-d H:i:s', gmdate('U')));
            $exceptionsFiltersData = $connection->quote(@json_encode($allExceptionsFilters));

            $installer->run(<<<SQL
            INSERT INTO `m2epro_registry` (`key`, `value`, `update_date`, `create_date`)
            VALUES ('/exceptions_filters/', {$exceptionsFiltersData}, {$date}, {$date})
SQL
            );
        }
    }

    $installer->run(<<<SQL
        DROP TABLE IF EXISTS `m2epro_exceptions_filters`;
SQL
    );
}

// Versions history migration
// ---------------------------------------

$tempTable = $installer->getTable('m2epro_cache_config');

$stmt = $connection->query("
    SELECT  `group`, `key`, `value`, `create_date`
    FROM `{$tempTable}`
    WHERE `group` = '/installation/version/history/'
");

if (false !== $stmt) {

    $allVersionsHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($allVersionsHistory)) {

        $resultVersionsHistory = array();

        foreach ($allVersionsHistory as $versionHistory) {
            $resultVersionsHistory[] = array(
                'from' => $versionHistory['value'],
                'to'   => $versionHistory['key'],
                'date' => $versionHistory['create_date']
            );
        }

        $date = $connection->quote(date('Y-m-d H:i:s', gmdate('U')));
        $versionsHistoryData = $connection->quote(@json_encode($resultVersionsHistory));

        $installer->run(<<<SQL
        INSERT INTO `m2epro_registry` (`key`, `value`, `update_date`, `create_date`)
        VALUES ('/installation/versions_history/', {$versionsHistoryData}, {$date}, {$date})
SQL
        );
    }
}

$installer->run(<<<SQL
    DELETE FROM `m2epro_cache_config`
    WHERE `group` = '/installation/version/history/';
SQL
);

// Play removing
//########################################

$tempTable = $installer->getTable('m2epro_config');

$tempQuery = <<<SQL
    SELECT `value`
    FROM `{$tempTable}`
    WHERE `group` = '/component/play/'
    AND   `key` = 'mode'
SQL;

$playMode = $connection->query($tempQuery)->fetchColumn();

$tempQuery = <<<SQL
    SELECT `value`
    FROM `{$tempTable}`
    WHERE `group` = '/component/play/'
    AND   `key` = 'allowed'
SQL;

$playAllowed = $connection->query($tempQuery)->fetchColumn();
$wizardStatus = ($playMode && $playAllowed) ? 0 : 3;

$installer->run(<<<SQL

    DELETE FROM `m2epro_wizard` WHERE `nick` = 'play';

    INSERT INTO `m2epro_wizard` (`nick`, `view`, `status`, `step`, `type`, `priority`)
    SELECT 'removedPlay', 'common', {$wizardStatus}, NULL, 0, MAX( `priority` )+1 FROM `m2epro_wizard`;

SQL
);

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_config');

$tempQuery = <<<SQL
    SELECT `value`
    FROM `{$tempTable}`
    WHERE `group` = '/view/common/component/'
    AND   `key` = 'default'
SQL;

$defaultComponent = $connection->query($tempQuery)->fetchColumn();

if ($defaultComponent == 'play') {

    $tempQuery = <<<SQL
        SELECT `value`
        FROM `{$tempTable}`
        WHERE `group` = '/component/amazon/'
        AND   `key` = 'mode'
SQL;

    $amazonMode = $connection->query($tempQuery)->fetchColumn();

    $tempQuery = <<<SQL
        SELECT `value`
        FROM `{$tempTable}`
        WHERE `group` = '/component/amazon/'
        AND   `key` = 'allowed'
SQL;

    $amazonAllowed = $connection->query($tempQuery)->fetchColumn();

    if ($amazonMode && $amazonAllowed) {

        $installer->run(<<<SQL
            UPDATE `m2epro_config`
            SET `value` = 'amazon'
            WHERE `group` = '/view/common/component/'
            AND `key` = 'default';
SQL
        );

    } else {

        $tempQuery = <<<SQL
            SELECT `value`
            FROM `{$tempTable}`
            WHERE `group` = '/component/buy/'
            AND   `key` = 'mode'
SQL;

        $buyMode = $connection->query($tempQuery)->fetchColumn();

        $tempQuery = <<<SQL
            SELECT `value`
            FROM `{$tempTable}`
            WHERE `group` = '/component/buy/'
            AND   `key` = 'allowed'
SQL;

        $buyAllowed = $connection->query($tempQuery)->fetchColumn();

        if ($buyMode && $buyAllowed) {

            $installer->run(<<<SQL
                UPDATE `m2epro_config`
                SET `value` = 'buy'
                WHERE `group` = '/view/common/component/'
                AND `key` = 'default';
SQL
            );

        } else {

            $installer->run(<<<SQL
                UPDATE `m2epro_config`
                SET `value` = 'amazon'
                WHERE `group` = '/view/common/component/'
                AND `key` = 'default';
SQL
            );
        }
    }
}

// ---------------------------------------

$modelsArray = array(
    $installer->getTable('m2epro_account') => 'M2ePro/Account',
    $installer->getTable('m2epro_listing') => 'M2ePro/Listing',
    $installer->getTable('m2epro_listing_product') => 'M2ePro/Listing_Product'
);

foreach ($modelsArray as $table => $model) {

    $tempQuery = <<<SQL
        SELECT `id`
        FROM `{$table}`
        WHERE `component_mode` = 'play'
SQL;

    $ids = $connection->query($tempQuery)->fetchColumn();

    if (!empty($ids)) {
        $ids = is_array($ids) ? implode(',', $ids) : $ids;

        $installer->run(<<<SQL
        DELETE FROM `m2epro_locked_object`
        WHERE `model_name` LIKE '{$model}'
        AND `object_id` IN ({$ids});
SQL
        );
    }
}

// ---------------------------------------

$installer->run(<<<SQL

    DELETE FROM `m2epro_lock_item` WHERE `nick` LIKE '%_play%';
    DELETE FROM `m2epro_lock_item` WHERE `nick` LIKE 'play%';

    DELETE FROM `m2epro_config` WHERE `group` LIKE '%/play/%';
    DELETE FROM `m2epro_synchronization_config` WHERE `group` LIKE '%/play/%';
    DELETE FROM `m2epro_primary_config` WHERE `group` LIKE '%/play/%';

    DELETE FROM `m2epro_order_change` WHERE `component` = 'play';
    DELETE FROM `m2epro_order_repair` WHERE `component` = 'play';
    DELETE FROM `m2epro_processing_request` WHERE `component` = 'play';

    DELETE FROM `m2epro_account` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_auto_category_group` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_log` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_other` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_other_log` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_product` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_product_variation` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_listing_product_variation_option` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_marketplace` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_order` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_order_item` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_order_log` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_stop_queue` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_synchronization_log` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_template_selling_format` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_template_synchronization` WHERE `component_mode` = 'play';
    DELETE FROM `m2epro_template_description` WHERE `component_mode` = 'play';

    DROP TABLE IF EXISTS `m2epro_play_account`;
    DROP TABLE IF EXISTS `m2epro_play_item`;
    DROP TABLE IF EXISTS `m2epro_play_listing`;
    DROP TABLE IF EXISTS `m2epro_play_listing_auto_category_group`;
    DROP TABLE IF EXISTS `m2epro_play_listing_other`;
    DROP TABLE IF EXISTS `m2epro_play_listing_product`;
    DROP TABLE IF EXISTS `m2epro_play_listing_product_variation`;
    DROP TABLE IF EXISTS `m2epro_play_listing_product_variation_option`;
    DROP TABLE IF EXISTS `m2epro_play_marketplace`;
    DROP TABLE IF EXISTS `m2epro_play_order`;
    DROP TABLE IF EXISTS `m2epro_play_order_item`;
    DROP TABLE IF EXISTS `m2epro_play_processed_inventory`;
    DROP TABLE IF EXISTS `m2epro_play_template_selling_format`;
    DROP TABLE IF EXISTS `m2epro_play_template_synchronization`;

SQL
);

//########################################

$installer->endSetup();

//########################################