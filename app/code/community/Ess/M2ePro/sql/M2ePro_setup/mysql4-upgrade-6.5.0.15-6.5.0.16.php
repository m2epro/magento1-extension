<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//-- UpdateIsNewAsinAvailableMXN
//########################################

$connection->update(
    $installer->getTablesObject()->getFullName('amazon_marketplace'),
    array('is_new_asin_available' => 1),
    array('marketplace_id = ?' => 34)
);

//-- AmazonB2BForSpainAndItaly
//########################################

$connection->update(
    $installer->getTablesObject()->getFullName('amazon_marketplace'),
    array('is_business_available' => 1,
          'is_product_tax_code_policy_available' => 1),
    array('marketplace_id IN (?)' => array(26, 30, 31))
);

//########################################

$installer->endSetup();

//########################################