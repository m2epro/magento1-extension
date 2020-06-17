<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_1_8__v6_1_9_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_ebay_dictionary_motor_specific');

        if ($connection->tableColumnExists($tempTable, 'marketplace_id') !== false) {
            $connection->dropColumn($tempTable, 'marketplace_id');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_order');

        if ($connection->tableColumnExists($tempTable, 'buyer_tax_id') === false) {
            $connection->addColumn(
                $tempTable,
                'buyer_tax_id',
                'VARCHAR(64) DEFAULT NULL AFTER `buyer_message`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_template_shipping_service');

        if ($connection->tableColumnExists($tempTable, 'cost_surcharge_value') === false) {
            $connection->addColumn(
                $tempTable,
                'cost_surcharge_value',
                'VARCHAR(255) NOT NULL AFTER `cost_additional_value`'
            );
        }

        //########################################

        $installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS `{$this->_installer->getTable('m2epro_ebay_dictionary_motor_ktype')}` (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id int(11) UNSIGNED NOT NULL,
  ktype int(11) UNSIGNED NOT NULL,
  make varchar(255) DEFAULT NULL,
  model varchar(255) DEFAULT NULL,
  variant varchar(255) DEFAULT NULL,
  body_style varchar(255) DEFAULT NULL,
  type varchar(255) DEFAULT NULL,
  from_year int(11) DEFAULT NULL,
  to_year int(11) DEFAULT NULL,
  engine varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX body_style (body_style),
  INDEX engine (engine),
  INDEX from_year (from_year),
  INDEX ktype (ktype),
  INDEX make (make),
  INDEX marketplace_id (marketplace_id),
  INDEX model (model),
  INDEX to_year (to_year),
  INDEX type (type),
  INDEX variant (variant)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

UPDATE `{$this->_installer->getTable('m2epro_ebay_marketplace')}`
SET `is_local_shipping_rate_table` = 1
WHERE `marketplace_id` = 9;

SQL
        );

        //########################################

        $tempTable = $installer->getTable('m2epro_synchronization_config');
        $tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/ebay/marketplaces/motors_ktypes/'
    AND   `key` = 'mode'
SQL;
        $tempRow = $connection->query($tempQuery)->fetch();

        if ($tempRow === false) {

            $installer->run(<<<SQL

INSERT INTO `{$this->_installer->getTable('m2epro_synchronization_config')}` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/ebay/marketplaces/motors_ktypes/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2013-09-18 00:00:00', '2013-09-18 00:00:00'),
('/ebay/marketplaces/motors_ktypes/', 'part_size', '10000', '0 - disable, \r\n1 - enable',
 '2013-09-18 00:00:00', '2013-09-18 00:00:00');

SQL
            );
        }
    }

    //########################################
}