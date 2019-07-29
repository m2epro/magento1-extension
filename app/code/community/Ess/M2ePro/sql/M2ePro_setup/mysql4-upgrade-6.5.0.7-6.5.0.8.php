<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

// IncreaseCapacityOfSystemLogMessage
//----------------------------------------

/*
    ALTER TABLE `m2epro_system_log`
    CHANGE COLUMN `description` `description` LONGTEXT DEFAULT NULL;
*/

$installer->getTableModifier('system_log')
    ->changeColumn('description', 'LONGTEXT', 'NULL');

//########################################

$installer->endSetup();

//########################################