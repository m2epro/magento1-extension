<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_product_change`
        CHANGE COLUMN `creator_type` `initiators` VARCHAR(16) NOT NULL,
        DROP INDEX `creator_type`,
        ADD INDEX `initiators`(`initiators`);
*/

// --------------------------------------------

$tempTable = $installer->getTable('m2epro_product_change');

if ($connection->tableColumnExists($tempTable, 'initiators') === false &&
    $connection->tableColumnExists($tempTable, 'creator_type') !== false
) {
    $connection->changeColumn($tempTable, 'creator_type', 'initiators', 'VARCHAR(16) NOT NULL');
}

$tempTableIndexList = $connection->getIndexList($tempTable);

if (isset($tempTableIndexList[strtoupper('creator_type')])) {
    $connection->dropKey($tempTable, 'creator_type');
}

if (!isset($tempTableIndexList[strtoupper('initiators')])) {
    $connection->addKey($tempTable, 'initiators', 'initiators');
}

//#############################################

$installer->endSetup();

//#############################################