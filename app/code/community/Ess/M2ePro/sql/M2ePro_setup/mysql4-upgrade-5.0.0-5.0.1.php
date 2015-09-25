<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

/*
    ALTER TABLE `m2epro_buy_listing_product_variation`
    DROP COLUMN `online_price`,
    DROP COLUMN `online_qty`;
*/

//---------------------------------------------

$tempTable = $installer->getTable('m2epro_buy_listing_product_variation');

if ($connection->tableColumnExists($tempTable, 'online_price') === true) {
    $connection->dropColumn($tempTable, 'online_price');
}

if ($connection->tableColumnExists($tempTable, 'online_qty') === true) {
    $connection->dropColumn($tempTable, 'online_qty');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$buyComponentMode = (int)$connection->query("
    SELECT `value`
    FROM `{$tempTable}`
    WHERE `group` = '/component/buy/'
      AND `key` = 'mode'
")->fetchColumn();

$installer->run(<<<SQL

UPDATE `m2epro_marketplace`
SET `status` = {$buyComponentMode}
WHERE `id` = 33;

SQL
);

//#############################################

$installer->endSetup();

//#############################################