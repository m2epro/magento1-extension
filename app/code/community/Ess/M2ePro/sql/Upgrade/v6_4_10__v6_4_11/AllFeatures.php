<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_4_10__v6_4_11_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        // Remove Terapeak
        //########################################

        $this->_installer->getMainConfigModifier()->delete('/view/ebay/terapeak/');

        // TransactionalLocks
        //########################################

        $this->_installer->run(<<<SQL
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

        $this->_installer->getTableModifier('ebay_order_item')
            ->addColumn('waste_recycling_fee', 'DECIMAL(12, 4) NOT NULL', '0.0000', 'final_fee');
    }

    //########################################
}