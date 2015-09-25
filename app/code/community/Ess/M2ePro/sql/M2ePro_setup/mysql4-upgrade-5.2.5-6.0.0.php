<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

/** @var $migrationInstance Ess_M2ePro_Model_Upgrade_Migration_ToVersion6 */
$migrationInstance = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion6');
$migrationInstance->setInstaller($installer);

$migrationInstance->backup();
$migrationInstance->migrate();

//#############################################

$installer->endSetup();

//#############################################