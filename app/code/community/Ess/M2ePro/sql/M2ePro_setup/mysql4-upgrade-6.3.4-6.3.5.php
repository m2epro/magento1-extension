<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    ### amazon dictionary tables improvements
    ### -------------------------------

    CREATE TABLE m2epro_amazon_dictionary_category_product_data (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        marketplace_id INT(11) UNSIGNED NOT NULL,
        browsenode_id INT(11) UNSIGNED NOT NULL,
        product_data_nick VARCHAR(255) NOT NULL,
        is_applicable TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        required_attributes TEXT DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX marketplace_id (marketplace_id),
        INDEX browsenode_id (browsenode_id),
        INDEX product_data_nick (product_data_nick),
        INDEX is_applicable (is_applicable)
    )
    ENGINE = MYISAM
    CHARACTER SET utf8
    COLLATE utf8_general_ci;

    ALTER TABLE `m2epro_amazon_dictionary_category`
        CHANGE COLUMN product_data_nick product_data_nicks varchar(500) DEFAULT NULL AFTER `browsenode_id`,
        DROP COLUMN `required_attributes`,
        DROP INDEX `product_data_nick`,
        ADD INDEX `product_data_nicks` (`product_data_nicks`);

    ### -------------------------------

    ### ebay shipping policy
    ### -------------------------------

    ALTER TABLE `m2epro_ebay_template_shipping`
        ADD COLUMN `country_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `is_custom_template`,
        CHANGE COLUMN `country` `country_custom_value` VARCHAR(255) NOT NULL AFTER `country_mode`,
        ADD COLUMN `country_custom_attribute` VARCHAR(255) NOT NULL AFTER `country_custom_value`,
        ADD COLUMN `postal_code_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `country_custom_attribute`,
        CHANGE COLUMN `postal_code` `postal_code_custom_value` VARCHAR(255) NOT NULL AFTER `postal_code_mode`,
        ADD COLUMN `postal_code_custom_attribute` VARCHAR(255) NOT NULL AFTER `postal_code_custom_value`,
        ADD COLUMN `address_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `postal_code_custom_attribute`,
        CHANGE COLUMN `address` `address_custom_value` VARCHAR(255) NOT NULL AFTER `address_mode`,
        ADD COLUMN `address_custom_attribute` VARCHAR(255) NOT NULL AFTER `address_custom_value`;

    ### -------------------------------

    ### amazon swatch image
    ### -------------------------------

    ALTER TABLE `m2epro_amazon_template_description_definition`
        ADD COLUMN `image_variation_difference_mode` TINYINT(2)
        UNSIGNED NOT NULL DEFAULT 0 AFTER `image_main_attribute`,
        ADD COLUMN `image_variation_difference_attribute`
        VARCHAR(255) NOT NULL AFTER `image_variation_difference_mode`;

    ### -------------------------------

    ### order variations matching
    ### -------------------------------

    RENAME TABLE `m2epro_order_repair` TO `m2epro_order_matching`;

    ALTER TABLE `m2epro_order_matching`
        DROP COLUMN `type`,
        CHANGE COLUMN `input_data` `input_variation_options` TEXT DEFAULT NULL AFTER `product_id`,
        CHANGE COLUMN `output_data` `output_variation_options` TEXT DEFAULT NULL AFTER `input_variation_options`;

    ALTER TABLE `m2epro_ebay_item`
        ADD COLUMN `variations` TEXT DEFAULT NULL AFTER `store_id`;

    ALTER TABLE `m2epro_amazon_item`
        CHANGE COLUMN `variation_options` `variation_product_options` TEXT DEFAULT NULL AFTER `store_id`,
        ADD COLUMN `variation_channel_options` TEXT DEFAULT NULL AFTER `variation_product_options`;

    ALTER TABLE `m2epro_buy_item`
        CHANGE COLUMN `variation_options` `variation_product_options` TEXT DEFAULT NULL AFTER `store_id`;

    ### -------------------------------
*/

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_dictionary_category_product_data');

if (!$installer->tableExists($tempTable)) {

    $installer->run(<<<SQL

    CREATE TABLE m2epro_amazon_dictionary_category_product_data (
        id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        marketplace_id INT(11) UNSIGNED NOT NULL,
        browsenode_id INT(11) UNSIGNED NOT NULL,
        product_data_nick VARCHAR(255) NOT NULL,
        is_applicable TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
        required_attributes TEXT DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX marketplace_id (marketplace_id),
        INDEX browsenode_id (browsenode_id),
        INDEX product_data_nick (product_data_nick),
        INDEX is_applicable (is_applicable)
    )
    ENGINE = MYISAM
    CHARACTER SET utf8
    COLLATE utf8_general_ci;
SQL
    );
}

// ---------------------------------------

$installer->run(<<<SQL

    TRUNCATE TABLE `m2epro_amazon_dictionary_category`;
    TRUNCATE TABLE `m2epro_amazon_dictionary_specific`;
    TRUNCATE TABLE `m2epro_amazon_dictionary_marketplace`;

SQL
);

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_dictionary_category');
$tempTableIndexList = $connection->getIndexList($tempTable);

if ($connection->tableColumnExists($tempTable, 'product_data_nicks') === false &&
    $connection->tableColumnExists($tempTable, 'product_data_nick') !== false) {
    $connection->changeColumn(
        $tempTable, 'product_data_nick', 'product_data_nicks',
        'VARCHAR(500) DEFAULT NULL AFTER `browsenode_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'required_attributes') !== false) {
    $connection->dropColumn($tempTable, 'required_attributes');
}

if (isset($tempTableIndexList[strtoupper('product_data_nick')])) {
    $connection->dropKey($tempTable, 'product_data_nick');
}

if (!isset($tempTableIndexList[strtoupper('product_data_nicks')])) {
    $connection->addKey($tempTable, 'product_data_nicks', 'product_data_nicks');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_shipping');

if ($connection->tableColumnExists($tempTable, 'country_mode') === false) {
    $connection->addColumn(
        $tempTable, 'country_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `is_custom_template`'
    );
}

if ($connection->tableColumnExists($tempTable, 'country_custom_value') === false &&
    $connection->tableColumnExists($tempTable, 'country') !== false) {
    $connection->changeColumn(
        $tempTable, 'country', 'country_custom_value',
        'VARCHAR(255) NOT NULL AFTER `country_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'country_custom_attribute') === false) {
    $connection->addColumn(
        $tempTable, 'country_custom_attribute',
        'VARCHAR(255) NOT NULL AFTER `country_custom_value`'
    );
}

if ($connection->tableColumnExists($tempTable, 'postal_code_mode') === false) {
    $connection->addColumn(
        $tempTable, 'postal_code_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `country_custom_attribute`'
    );
}

if ($connection->tableColumnExists($tempTable, 'postal_code_custom_value') === false &&
    $connection->tableColumnExists($tempTable, 'postal_code') !== false) {
    $connection->changeColumn(
        $tempTable, 'postal_code', 'postal_code_custom_value',
        'VARCHAR(255) NOT NULL AFTER `postal_code_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'postal_code_custom_attribute') === false) {
    $connection->addColumn(
        $tempTable, 'postal_code_custom_attribute',
        'VARCHAR(255) NOT NULL AFTER `postal_code_custom_value`'
    );
}

if ($connection->tableColumnExists($tempTable, 'address_mode') === false) {
    $connection->addColumn(
        $tempTable, 'address_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `postal_code_custom_attribute`'
    );
}

if ($connection->tableColumnExists($tempTable, 'address_custom_value') === false &&
    $connection->tableColumnExists($tempTable, 'address') !== false) {
    $connection->changeColumn(
        $tempTable, 'address', 'address_custom_value',
        'VARCHAR(255) NOT NULL AFTER `address_mode`'
    );
}

if ($connection->tableColumnExists($tempTable, 'address_custom_attribute') === false) {
    $connection->addColumn(
        $tempTable, 'address_custom_attribute',
        'VARCHAR(255) NOT NULL AFTER `address_custom_value`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_description_definition');

if ($connection->tableColumnExists($tempTable, 'image_variation_difference_mode') === false) {
    $connection->addColumn(
        $tempTable, 'image_variation_difference_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `image_main_attribute`'
    );
}

if ($connection->tableColumnExists($tempTable, 'image_variation_difference_attribute') === false) {
    $connection->addColumn(
        $tempTable, 'image_variation_difference_attribute',
        'VARCHAR(255) NOT NULL AFTER `image_variation_difference_mode`'
    );
}

// ---------------------------------------

$orderRepairTable = $installer->getTable('m2epro_order_repair');
$orderMatchingTable = $installer->getTable('m2epro_order_matching');

if ($installer->tableExists($orderMatchingTable) === false &&
    $installer->tableExists($orderRepairTable) !== false) {

    $installer->run(<<<SQL

RENAME TABLE m2epro_order_repair TO m2epro_order_matching;

SQL
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_order_matching');

if ($connection->tableColumnExists($tempTable, 'type') !== false) {
    $connection->dropColumn($tempTable, 'type');
}

if ($connection->tableColumnExists($tempTable, 'input_variation_options') === false &&
    $connection->tableColumnExists($tempTable, 'input_data') !== false) {
    $connection->changeColumn(
        $tempTable, 'input_data', 'input_variation_options',
        'TEXT DEFAULT NULL AFTER `product_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'output_variation_options') === false &&
    $connection->tableColumnExists($tempTable, 'output_data') !== false) {
    $connection->changeColumn(
        $tempTable, 'output_data', 'output_variation_options',
        'TEXT DEFAULT NULL AFTER `input_variation_options`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_item');

if ($connection->tableColumnExists($tempTable, 'variations') === false) {
    $connection->addColumn($tempTable, 'variations', 'TEXT DEFAULT NULL AFTER `store_id`');
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_item');

if ($connection->tableColumnExists($tempTable, 'variation_product_options') === false &&
    $connection->tableColumnExists($tempTable, 'variation_options') !== false) {
    $connection->changeColumn(
        $tempTable, 'variation_options', 'variation_product_options',
        'TEXT DEFAULT NULL AFTER `store_id`'
    );
}

if ($connection->tableColumnExists($tempTable, 'variation_channel_options') === false) {
    $connection->addColumn(
        $tempTable, 'variation_channel_options',
        'TEXT DEFAULT NULL AFTER `variation_product_options`'
    );
}

// ---------------------------------------

$tempTable = $installer->getTable('m2epro_buy_item');

if ($connection->tableColumnExists($tempTable, 'variation_product_options') === false &&
    $connection->tableColumnExists($tempTable, 'variation_options') !== false) {
    $connection->changeColumn(
        $tempTable, 'variation_options', 'variation_product_options',
        'TEXT DEFAULT NULL AFTER `store_id`'
    );
}

//########################################

$tempTable = $installer->getTable('m2epro_wizard');

$tempRow = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE `nick` = 'fullAmazonCategories'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

        INSERT INTO `m2epro_wizard` (`nick`, `view`, `status`, `step`, `type`, `priority`)
        VALUES ('fullAmazonCategories', 'common', 0, NULL, 1, 8);
SQL
);
}

$installer->run(<<<SQL

    UPDATE `m2epro_wizard` as `mw`
    SET `mw`.`status` = 3
    WHERE `mw`.`nick` = 'fullAmazonCategories'
    AND (
        (SELECT `mc`.`value`
         FROM `m2epro_config` as `mc`
         WHERE `mc`.`value` IS NOT NULL
         AND `mc`.`group` = '/component/amazon/'
         AND `mc`.`key` = 'mode'
         LIMIT 1) < 1

         OR

        (SELECT `mc`.`value`
         FROM `m2epro_config` as `mc`
         WHERE `mc`.`value` IS NOT NULL
         AND `mc`.`group` = '/component/amazon/'
         AND `mc`.`key` = 'allowed'
         LIMIT 1) < 1

         OR

         (SELECT COUNT(`mm`.`id`) FROM `m2epro_marketplace` as `mm`
          WHERE `mm`.`component_mode` = 'amazon'
          AND `mm`.`status` = 1) = 0
    );

SQL
);

//########################################

$tempTable = $installer->getTable('m2epro_processing_request');

$processingRequests = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE `responser_model` REGEXP '^M2ePro\/Connector_Buy_Product_*'
")->fetchAll();

foreach ($processingRequests as &$processingRequest) {

    if (empty($processingRequest['responser_params'])) {
        continue;
    }

    $responserParams = json_decode($processingRequest['responser_params'], true);

    if (empty($responserParams['products'])) {
        continue;
    }

    $products = array();

    foreach ($responserParams['products'] as $id => $productData) {

        $configuratorData = $productData['configurator'];

        if (empty($configuratorData['allowed_data_types'])) {
            $products[$id] = $productData;
            continue;
        }

        $allowedDataTypes = $configuratorData['allowed_data_types'];

        if (!in_array('selling', $allowedDataTypes)) {
            $products[$id] = $productData;
            continue;
        }

        $allowedDataTypes = array_diff($allowedDataTypes, array('selling'));
        $allowedDataTypes = array_merge($allowedDataTypes, array('qty', 'price'));

        $productData['configurator']['allowed_data_types'] = $allowedDataTypes;
        $products[$id] = $productData;
    }

    $responserParams['products'] = $products;
    $processingRequest['responser_params'] = json_encode($responserParams);
}

if (!empty($processingRequests)) {
    $connection->insertOnDuplicate($tempTable, $processingRequests, array('responser_params'));
}

//########################################

$tempTable = $installer->getTable('m2epro_ebay_template_description');

$result = $connection->query(
    "SELECT `template_description_id`, `product_details`
     FROM `{$tempTable}`
");

if ($result !== false) {

    $fieldsForUpdate = array();

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

        if (empty($row['product_details'])) {
            continue;
        }

        $hash = md5($row['product_details']);

        if (isset($fieldsForUpdate[$hash])) {
            $fieldsForUpdate[$hash]['ids'][] = $row['template_description_id'];
            continue;
        }

        $fieldsForUpdate[$hash]['ids'][] = $row['template_description_id'];
        $tempProductDetails = json_decode($row['product_details'], true);

        foreach (array('isbn', 'epid', 'upc', 'ean', 'brand', 'mpn') as $type) {

            if (empty($tempProductDetails[$type])) {
                $fieldsForUpdate[$hash]['product_details'][$type] = array(
                    'mode' => ($type == 'mpn') ? 1 : 0,
                    'attribute' => ''
                );
                continue;
            }

            $fieldsForUpdate[$hash]['product_details'][$type] = array(
                'mode' => 2,
                'attribute' => $tempProductDetails[$type]
            );
        }
    }

    foreach ($fieldsForUpdate as $fieldsData) {

        $where = 'WHERE template_description_id IN ( '.implode(',', $fieldsData['ids']).')';

        $productDetails = json_encode($fieldsData['product_details']);
        $productDetails = $connection->quote($productDetails);

        $installer->run('UPDATE `m2epro_ebay_template_description`
                         SET `product_details` = '.$productDetails.' '.
                         $where);
    }
}

//########################################

$tempTable = $installer->getTable('m2epro_registry');

$localVocabularyData = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE `key` = 'amazon_vocabulary_local'
")->fetchAll();

if (!empty($localVocabularyData)) {

    $localVocabularyData = reset($localVocabularyData);
    $localVocabularyData = (array)json_decode($localVocabularyData['value'], true);

    foreach ($localVocabularyData as $attribute => &$data) {

        if (!isset($data['names'])) {
            $data['names'] = array();
        }

        if (!isset($data['options'])) {
            $data['options'] = array();
        }
    }

    if (!empty($localVocabularyData)) {
        $connection->update(
            $tempTable,
            array('value' => json_encode($localVocabularyData)),
            array('`key` = ?' => 'amazon_vocabulary_local')
        );
    }
}

//########################################

$installer->run(<<<SQL

    UPDATE `m2epro_amazon_marketplace`
    SET `default_currency` = 'CAD'
    WHERE `marketplace_id` = 24;

    UPDATE `m2epro_ebay_marketplace`
    SET `is_stp` = 1
    WHERE `marketplace_id` = 2
    OR `marketplace_id` = 19;

    UPDATE `m2epro_synchronization_config`
    SET `value` = '86400'
    WHERE `group` = '/amazon/other_listings/update/'
    AND `key` = 'interval';

    UPDATE `m2epro_synchronization_config`
    SET `value` = '86400'
    WHERE `group` = '/buy/other_listings/update/'
    AND `key` = 'interval';

    UPDATE `m2epro_ebay_template_shipping`
    SET `postal_code_mode` = 1
    WHERE `postal_code_custom_value` != '';

    UPDATE `m2epro_ebay_template_shipping`
    SET `address_mode` = 1
    WHERE `address_custom_value` != '';

    UPDATE `m2epro_amazon_listing_product`
    SET `online_qty` = NULL
    WHERE `online_qty` IS NOT NULL
    AND `is_variation_parent` = 0
    AND `is_afn_channel` = 1;

    UPDATE `m2epro_amazon_listing_other`
    SET `online_qty` = NULL
    WHERE `online_qty` IS NOT NULL
    AND `is_afn_channel` = 1;

SQL
);

//########################################

$installer->endSetup();

//########################################