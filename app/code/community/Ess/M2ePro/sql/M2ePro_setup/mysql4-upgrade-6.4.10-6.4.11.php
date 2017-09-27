<?php

//########################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

// Remove Terapeak
//########################################

$installer->getMainConfigModifier()->delete('/view/ebay/terapeak/');

// TransactionalLocks
//########################################

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `m2epro_lock_transactional` (
   `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
   `nick` VARCHAR(255) NOT NULL,
   `create_date` DATETIME DEFAULT NULL,
   PRIMARY KEY (`id`),
   INDEX `nick` (`nick`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

// eBay Waste Recycling Fee
//########################################

/*
    ALTER TABLE `m2epro_ebay_order_item`
    ADD COLUMN `waste_recycling_fee` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000 AFTER `final_fee`;
 */
$installer->getTableModifier('ebay_order_item')
    ->addColumn('waste_recycling_fee', 'DECIMAL(12, 4) NOT NULL', '0.0000', 'final_fee');

//########################################

$installer->endSetup();

//########################################