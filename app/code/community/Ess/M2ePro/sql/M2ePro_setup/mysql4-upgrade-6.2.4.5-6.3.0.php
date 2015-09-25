<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE m2epro_amazon_dictionary_category
        DROP COLUMN product_data_required_specifics;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_dictionary_category');

if ($connection->tableColumnExists($tempTable, 'product_data_required_specifics') !== false) {
    $connection->dropColumn($tempTable, 'product_data_required_specifics');
}

//#############################################

$installer->run(<<<SQL

    DELETE FROM `m2epro_config`
    WHERE `group` = '/view/common/amazon/listing/' AND
          `key` = 'tutorial_shown';

    DELETE FROM `m2epro_config`
    WHERE `group` = '/view/common/buy/listing/' AND
          `key` = 'tutorial_shown';

    DELETE FROM `m2epro_config`
    WHERE `group` = '/view/common/play/listing/' AND
          `key` = 'tutorial_shown';

    DELETE FROM `m2epro_cache_config`
    WHERE `group` LIKE '/amazon/category/recent/marketplace/%';

    DELETE FROM `m2epro_lock_item`
    WHERE (`nick` REGEXP '^ebay_listing_[0-9]+$') OR
          (`nick` REGEXP '^(ebay|amazon|buy|play){1}_listing_product_[0-9]+$' AND
           `update_date` < DATE_SUB(NOW(), INTERVAL 7 DAY));

    UPDATE `m2epro_wizard`
    SET `status` = 0,
        `step` = NULL,
        `type` = 1
    WHERE `nick` = 'migrationNewAmazon';

SQL
);

//#############################################

$tempTable = $installer->getTable('m2epro_config');

$tempRow = $connection->query("
    SELECT *
    FROM `{$tempTable}`
    WHERE `group` = '/view/ebay/terapeak/' AND `key` = 'mode'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/view/ebay/terapeak/', 'mode', '1', NULL, '2015-02-05 00:00:00', '2015-02-05 00:00:00');

SQL
    );
}

//#############################################

$installer->endSetup();

//#############################################