<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_4_3__v6_4_4_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('amazon_marketplace')
            ->renameColumn('is_asin_available', 'is_new_asin_available', true);

        $this->_installer->run(<<<SQL
UPDATE `{$this->_installer->getTable('m2epro_amazon_marketplace')}`
SET `is_new_asin_available` = 1
WHERE `marketplace_id` = 24;

UPDATE `{$this->_installer->getTable('m2epro_amazon_marketplace')}`
SET `is_new_asin_available` = 0
WHERE `marketplace_id` IN (27, 32);
SQL
        );

        $this->_installer->getTableModifier('account')
            ->addColumn('additional_data', 'TEXT', 'NULL', 'component_mode');

        $this->_installer->run(<<<SQL
UPDATE `{$this->_installer->getTable('m2epro_amazon_listing_other')}`
SET `title` = '--'
WHERE `title` = '';
SQL
        );
    }

    //########################################
}