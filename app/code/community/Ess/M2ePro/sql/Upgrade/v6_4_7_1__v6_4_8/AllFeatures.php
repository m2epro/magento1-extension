<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_4_7_1__v6_4_8_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        // IncreaseCapacityOfSystemLogMessage
        //----------------------------------------

        $this->_installer->getTableModifier('system_log')
            ->changeColumn('description', 'LONGTEXT', 'NULL');

        // fix for is_repricing default value
        // ---------------------------------------

        $this->_installer->getTableModifier('amazon_listing_product')
            ->changeColumn('is_repricing', 'TINYINT(2) UNSIGNED NOT NULL', 0);

        $this->_installer->run(<<<SQL
TRUNCATE TABLE `{$this->_installer->getTable('m2epro_amazon_listing_product_repricing')}`;

UPDATE `{$this->_installer->getTable('m2epro_amazon_listing_product')}`
SET `is_repricing` = 0;

UPDATE `{$this->_installer->getTable('m2epro_amazon_listing_other')}`
SET `is_repricing` = 0, `is_repricing_disabled` = 0;

UPDATE `{$this->_installer->getTable('m2epro_amazon_account_repricing')}`
SET `total_products` = 0, `last_checked_listing_product_update_date` = NULL;
SQL
        );

        $this->_installer->getMainConfigModifier()
            ->getEntity('/cron/task/repricing_synchronization_general/', 'last_run')
            ->updateValue(NULL);

        // ebay orders job_token default null fix
        // ---------------------------------------

        $this->_installer->getTableModifier('ebay_account')
            ->changeColumn('job_token', 'VARCHAR(255)', 'NULL');

        // repricing_update_settings cron task config fix
        // ---------------------------------------

        $this->_installer->getMainConfigModifier()
            ->insert('/cron/task/repricing_update_settings/', 'mode', '1');
    }

    //########################################
}