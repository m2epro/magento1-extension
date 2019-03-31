<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

$tablesList = array(
    'processing_request',
    'product_change',
    'locked_object',
    'lock_item',

    'cache_config',
    'primary_config',
    'config',
    'synchronization_config'
);
$backup = new Ess_M2ePro_Model_Upgrade_Backup($installer, $tablesList);
if (!$backup->isExists()) {
    $backup->create();
}

//########################################

$schema = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_Schema($installer);

//########################################

$schema->schemaCreate();

//########################################

$migration = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_General($installer);
$migration->run();

$migration = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_NewProcessing($installer);
$migration->run();

$migration = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_Configs($installer);
$migration->run();

$migration = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_EbayTables($installer);
$migration->run();

$migration = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_AmazonTables($installer);
$migration->run();

//########################################

$schema->schemaDelete();

//########################################

$installer->endSetup();

//########################################