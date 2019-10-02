<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_5_0_8__v6_5_0_9_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_marketplace')->addColumn(
            'is_epid', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_holiday_return', true
        );

        $this->_installer->getTableModifier('ebay_marketplace')->addColumn(
            'is_ktype', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_epid', true
        );

        $this->_installer->run(<<<SQL
UPDATE `{$this->_installer->getTable('m2epro_ebay_marketplace')}`
SET `is_epid` = 1
WHERE `marketplace_id` = 9;
SQL
        );

        $this->_installer->run(<<<SQL
UPDATE `{$this->_installer->getTable('m2epro_ebay_marketplace')}`
SET `is_ktype` = 1
WHERE `marketplace_id` IN (3, 4, 7, 8, 10);
SQL
        );

        // SearchSettingsDataCapacity
        //########################################

        $this->_installer->getTableModifier('amazon_listing_product')
            ->changeColumn('search_settings_data', 'LONGTEXT', 'NULL');

        $this->_installer->getTableModifier('amazon_listing_product')
            ->addColumn(
                'variation_parent_afn_state', 'SMALLINT(4) UNSIGNED', 'NULL', 'is_general_id_owner', true, false
            )
            ->addColumn(
                'variation_parent_repricing_state', 'SMALLINT(4) UNSIGNED', 'NULL',
                'variation_parent_afn_state', true, false
            )
            ->commit();
    }

    //########################################
}