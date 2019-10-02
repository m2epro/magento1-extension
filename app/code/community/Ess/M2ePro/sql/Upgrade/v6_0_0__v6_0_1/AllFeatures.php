<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_0_0__v6_0_1_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $installer->run(<<<SQL

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_policy`;

CREATE TABLE IF NOT EXISTS `m2epro_ebay_account_policy` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  type TINYINT(2) UNSIGNED NOT NULL,
  api_name VARCHAR(255) NOT NULL,
  api_identifier VARCHAR(255) NOT NULL,
  api_info TEXT NOT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX api_identifier (api_identifier),
  INDEX api_name (api_name),
  INDEX marketplace_id (marketplace_id),
  INDEX type (type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );

        //########################################

        $tempTable = $installer->getTable('m2epro_synchronization_config');
        $tempQuery = <<<SQL
            SELECT * FROM `{$tempTable}`
            WHERE `group` = '/ebay/policies/receive/'
            AND   `key` = 'mode'
SQL;
        $tempRow = $connection->query($tempQuery)->fetch();

        if ($tempRow === false) {

            $installer->run(<<<SQL

INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/policies/', 'mode', '0', '0 - disable, \r\n1 - enable',
 '2013-08-05 00:00:00', '2013-08-05 00:00:00'),
('/ebay/policies/', 'mode', '0', '0 - disable, \r\n1 - enable',
 '2013-08-05 00:00:00', '2013-08-05 00:00:00'),
('/ebay/policies/receive/', 'mode', '0', '0 - disable, \r\n1 - enable',
 '2013-08-05 00:00:00', '2013-08-05 00:00:00');

SQL
            );
        }
    }

    //########################################
}