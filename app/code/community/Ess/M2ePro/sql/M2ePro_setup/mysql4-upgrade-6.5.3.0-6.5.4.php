<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//-- WalmartOrdersItems
//########################################

$installer->getTableModifier('walmart_order_item')
    ->addColumn('merged_walmart_order_item_ids', 'VARCHAR(500)', 'NULL', 'walmart_order_item_id');

//########################################

$installer->endSetup();

//########################################