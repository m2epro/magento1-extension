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
    WHERE `group` = '/amazon/synchronization/settings/orders/update/'
    AND   `key` = 'max_deactivate_time'
")->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/amazon/synchronization/settings/orders/update/', 'max_deactivate_time', '86400', 'in seconds',
 '2013-04-23 00:00:00', '2013-04-23 00:00:00');

SQL
);
}

//#############################################

$installer->endSetup();

//#############################################