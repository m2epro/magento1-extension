<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

//########################################

/*
    UPDATE `m2epro_config`
    SET `value` = 1
    WHERE `group` = '/amazon/repricing/' AND `key` = 'mode'
 */

$installer->getMainConfigModifier()->getEntity('/amazon/repricing/', 'mode')->updateValue(1);

//########################################

$installer->endSetup();

//########################################