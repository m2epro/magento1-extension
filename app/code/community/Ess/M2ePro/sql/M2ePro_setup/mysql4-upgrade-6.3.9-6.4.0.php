<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    DELETE FROM `m2epro_synchronization_config`
    WHERE `group` = '/settings/product_change/' AND
          `key` = 'max_count';

    UPDATE `m2epro_synchronization_config`
    SET `value` = '172800'
    WHERE `group` = '/settings/product_change/' AND
          `key` = 'max_lifetime';
*/

$installer->getSynchConfigModifier()->getEntity('/settings/product_change/', 'max_count')->delete();
$installer->getSynchConfigModifier()->getEntity('/settings/product_change/', 'max_lifetime')->updateValue('172800');

//########################################

$installer->endSetup();

//########################################