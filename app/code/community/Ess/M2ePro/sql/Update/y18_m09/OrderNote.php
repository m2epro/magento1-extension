<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_OrderNote extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        if ($this->_installer->tableExists($this->_installer->getTablesObject()->getFullName('order_note'))) {
            return;
        }

        $this->_installer->run(
            <<<SQL
            
CREATE TABLE `{$this->_installer->getTable('m2epro_order_note')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL,
  `note` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id` (`order_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );
    }

    //########################################
}