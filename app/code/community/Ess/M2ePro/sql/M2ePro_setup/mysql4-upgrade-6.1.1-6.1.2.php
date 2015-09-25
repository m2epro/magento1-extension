<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $connection->query("
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/product/force_qty/'
    AND   `key` = 'mode'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/product/force_qty/', 'mode', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
('/product/force_qty/', 'value', '10', 'min qty value', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
    );
}

//#############################################

$installer->endSetup();

//#############################################