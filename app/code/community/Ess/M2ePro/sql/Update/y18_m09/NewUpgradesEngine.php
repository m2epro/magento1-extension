<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_NewUpgradesEngine extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        if (!$this->_installer->tableExists($this->_installer->getTablesObject()->getFullName('setup'))) {
            $this->_installer->run(
                <<<SQL

CREATE TABLE `m2epro_setup` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version_from` VARCHAR(32) DEFAULT NULL,
  `version_to` VARCHAR(32) DEFAULT NULL,
  `is_backuped` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_completed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `profiler_data` LONGTEXT DEFAULT  NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `version_from` (`version_from`),
  INDEX `version_to` (`version_to`),
  INDEX `is_backuped` (`is_backuped`),
  INDEX `is_completed` (`is_completed`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }
    }

    //########################################
}
