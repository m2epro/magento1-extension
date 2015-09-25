<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_ebay_template_selling_format`
    ADD COLUMN `restricted_to_business` tinyint(2) UNSIGNED DEFAULT 0 AFTER `listing_is_private`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_selling_format');
$columnName = 'restricted_to_business';

if ($connection->tableColumnExists($tempTable, $columnName) === false) {
    $connection->addColumn(
        $tempTable, $columnName, 'tinyint(2) UNSIGNED DEFAULT 0 AFTER `listing_is_private`'
    );
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');

$tempRow = $connection->query("
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/cron/'
    AND   `key` = 'last_type_change'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/cron/', 'last_type_change', NULL, 'Time of last change cron type', '2014-06-25 00:00:00', '2014-06-25 00:00:00');

SQL
    );
}

// clearing of forgotten indexes from previous versions
//#############################################

$indexesToDrop = array('title', 'type');
dropIndexes($installer, 'm2epro_exceptions_filters', $indexesToDrop);

$indexesToDrop = array('native_id', 'title', 'code', 'url', 'status', 'sorder', 'group_title');
dropIndexes($installer, 'm2epro_marketplace', $indexesToDrop);

$indexesToDrop = array('type');
dropIndexes($installer, 'm2epro_order_repair', $indexesToDrop);

$indexesToDrop = array('mode', 'token_session', 'token_expired_date', 'other_listings_synchronization',
                       'other_listings_last_synchronization', 'feedbacks_receive', 'feedbacks_auto_response',
                       'feedbacks_auto_response_only_positive', 'feedbacks_last_used_id', 'orders_mode',
                       'messages_receive');
dropIndexes($installer, 'm2epro_ebay_account', $indexesToDrop);

$indexesToDrop = array('ebay_item_title', 'buyer_name', 'buyer_feedback_type', 'seller_feedback_type');
dropIndexes($installer, 'm2epro_ebay_feedback', $indexesToDrop);

$indexesToDrop = array('group_id');
dropIndexes($installer, 'm2epro_ebay_listing_auto_category', $indexesToDrop);

$indexesToDrop = array('developer_key', 'default_currency');
dropIndexes($installer, 'm2epro_amazon_marketplace', $indexesToDrop);

$indexesToDrop = array('xsd_hash', 'node_title', 'category_path', 'worldwide_id_mode', 'item_package_quantity_mode',
                       'number_of_items_mode', 'title');
dropIndexes($installer, 'm2epro_amazon_template_new_product', $indexesToDrop);

$indexesToDrop = array('title_mode', 'brand_mode', 'manufacturer_mode', 'manufacturer_part_number_mode',
                       'package_weight_mode', 'shipping_weight_mode', 'shipping_weight_unit_of_measure_mode',
                       'package_weight_unit_of_measure_mode', 'target_audience_mode', 'search_terms_mode',
                       'bullet_points_mode', 'description_mode', 'image_main_mode', 'image_main_attribute',
                       'gallery_images_mode');
dropIndexes($installer, 'm2epro_amazon_template_new_product_description', $indexesToDrop);

$indexesToDrop = array('server_hash', 'web_login', 'ftp_login', 'ftp_new_sku_access', 'ftp_inventory_access',
                       'ftp_orders_access', 'related_store_id', 'other_listings_synchronization',
                       'other_listings_mapping_mode', 'other_listings_move_mode');
dropIndexes($installer, 'm2epro_buy_account', $indexesToDrop);

$indexesToDrop = array('category_id', 'node_title', 'category_path', 'title');
dropIndexes($installer, 'm2epro_buy_template_new_product', $indexesToDrop);

$indexesToDrop = array('attribute_name', 'mode');
dropIndexes($installer, 'm2epro_buy_template_new_product_attribute', $indexesToDrop);

$indexesToDrop = array('server_hash', 'login', 'related_store_id', 'other_listings_synchronization',
                       'other_listings_mapping_mode', 'other_listings_move_mode');
dropIndexes($installer, 'm2epro_play_account', $indexesToDrop);

//---------------------------------------------

function dropIndexes(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer, $tableName, $indexesToDrop)
{
    $tempTable = $installer->getTable($tableName);

    if (!$tempTable) {
        return;
    }

    $tempTableIndexesList = $installer->getConnection()->getIndexList($tempTable);

    foreach ($indexesToDrop as $indexToDrop) {
        if (isset($tempTableIndexesList[strtoupper($indexToDrop)])) {
            $installer->getConnection()->dropKey($tempTable, $indexToDrop);
        }
    }
}

//#############################################

$installer->run(<<<SQL

    UPDATE `m2epro_ebay_marketplace`
    SET `is_freight_shipping` = 1
    WHERE `marketplace_id` = 2;

    UPDATE `m2epro_ebay_marketplace`
    SET `is_stp` = 1
    WHERE `marketplace_id` = 13 -- ES --
    OR    `marketplace_id` = 7 -- FR --
    OR    `marketplace_id` = 10; -- IT --

SQL
);

//#############################################

$installer->endSetup();

//#############################################