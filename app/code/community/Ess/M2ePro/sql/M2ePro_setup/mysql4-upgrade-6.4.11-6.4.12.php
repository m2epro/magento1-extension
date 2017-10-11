<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

// -- Remove Kill Now
//########################################

$installer->getTableModifier("lock_item")
    ->dropColumn("kill_now");

//########################################

// -- NewAmazonMarketplaces
//########################################

$installer->run(<<<SQL

INSERT INTO `m2epro_marketplace` VALUES
  (34, 9, 'Mexico', 'MX', 'amazon.com.mx', 0, 10, 'America', 'amazon', '2017-09-27 00:00:00', '2017-09-27 00:00:00'),
  (35, 10, 'Australia', 'AU', 'amazon.com.au', 0, 11, 'Asia / Pacific', 'amazon', '2017-09-27 00:00:00',
   '2017-09-27 00:00:00'),
  (36, 0, 'India', 'IN', 'amazon.in', 0, 12, 'Asia / Pacific', 'amazon', '2017-09-27 00:00:00', '2017-09-27 00:00:00');

INSERT INTO `m2epro_amazon_marketplace` VALUES
  (34, '8636-1433-4377', 'MXN', 0, 0),
  (35, '2770-5005-3793', 'AUD', 1, 0),
  (36, NULL, '', 0, 0);

SQL
);

//########################################

$installer->endSetup();

//########################################