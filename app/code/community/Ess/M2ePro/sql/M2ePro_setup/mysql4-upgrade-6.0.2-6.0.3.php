<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

$tempTable = $installer->getTable('m2epro_primary_config');
$tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/M2ePro/server/'
    AND   `key` = 'installation_key'
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_primary_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/M2ePro/server/', 'installation_key', '{$installer->generateRandomHash()}', 'Unique identifier of M2E instance',
 '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
);
}

//########################################

$tempTable = $installer->getTable('m2epro_config');
$tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/view/ebay/support/'
    AND   `key` = 'video_tutorials_url'
SQL;
$tempRow = $connection->query($tempQuery)->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/view/common/support/', 'documentation_url', 'http://docs.m2epro.com/display/eBayMagentoV6/M2E+Pro+v.6.x+Overview',
 NULL, '2013-05-08 00:00:00','2013-05-08 00:00:00'),
('/view/common/support/', 'video_tutorials_url', 'http://docs.m2epro.com/display/eBayMagentoV6/Video+Tutorials',
 NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
('/view/ebay/support/', 'documentation_url', 'http://docs.m2epro.com/display/eBayMagentoV6/M2E+Pro+v.6.x+Overview',
 NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
('/view/ebay/support/', 'video_tutorials_url', 'http://docs.m2epro.com/display/eBayMagentoV6/Video+Tutorials',
 NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00');

SQL
);
}

//########################################

$installer->run(<<<SQL

DELETE FROM `m2epro_config`
WHERE `group` = '/support/'
AND   `key` = 'documentation_url';

DELETE FROM `m2epro_config`
WHERE `group` = '/support/'
AND   `key` = 'video_tutorials_url';

SQL
);

//########################################

$installer->endSetup();

//########################################