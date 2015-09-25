<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_amazon_listing_product`
        ADD COLUMN `variation_parent_need_processor` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `variation_parent_id`,
        ADD INDEX `variation_parent_need_processor` (`variation_parent_need_processor`);
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');

if ($connection->tableColumnExists($tempTable, 'variation_parent_need_processor') === false) {
    $connection->addColumn(
        $tempTable, 'variation_parent_need_processor',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `variation_parent_id`'
    );
}

$tempTableIndexList = $connection->getIndexList($tempTable);

if (!isset($tempTableIndexList[strtoupper('variation_parent_need_processor')])) {
    $connection->addKey($tempTable, 'variation_parent_need_processor', 'variation_parent_need_processor');
}

//#############################################

$tempTable = $installer->getTable('m2epro_synchronization_config');

$tempRow = $connection->query("
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/amazon/defaults/run_parent_processors/'
    AND   `key` = 'mode'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/amazon/defaults/run_parent_processors/', 'interval', '300', 'in seconds',
 '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
('/amazon/defaults/run_parent_processors/', 'mode', '1', '0-disable, \r\n1-enable',
 '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
('/amazon/defaults/run_parent_processors/', 'last_time', NULL, 'last check time',
 '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
    );
}

//#############################################

$tempTable = $installer->getTable('m2epro_primary_config');

$tempRow = $connection->query("
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/M2ePro/license/info/'
    AND   `key` = 'email'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_primary_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/M2ePro/license/info/', 'email', NULL, 'Associated Email', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
    );
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_config`
SET `value` = 'https://clients.m2epro.com/'
WHERE `group` = '/support/' AND `key` = 'clients_portal_url';

SQL
);

//#############################################

$installer->endSetup();

//#############################################