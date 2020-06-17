<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_3_8__v6_3_9_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;

        $modifier = $installer->getTableModifier('amazon_template_description_specific');

        if (!$modifier->isColumnExists('is_required')) {

            $modifier->addColumn('is_required', 'TINYINT(2) UNSIGNED', 0, 'mode');

            $installer->run(<<<SQL
UPDATE `{$this->_installer->getTable('m2epro_amazon_template_description_specific')}`
SET `is_required` = 1;
SQL
            );
        }

        // ---------------------------------------

        $installer->getTableModifier('amazon_account')
            ->addColumn('repricing', 'TEXT NULL', 'NULL', 'magento_orders_settings');

        // ---------------------------------------

        $installer->getTableModifier('amazon_listing_product')
            ->addColumn('is_repricing', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_general_id_owner', true);

        // ---------------------------------------

        $installer->getTableModifier('amazon_listing_other')
            ->addColumn('is_repricing', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_isbn_general_id', true);

        // ---------------------------------------

        $installer->getTableModifier('amazon_marketplace')
            ->addColumn('is_merchant_fulfillment_available', 'TINYINT(2) UNSIGNED NOT NULL',
                        0, 'is_asin_available', true);

        // ---------------------------------------

        $installer->getTableModifier('order')
            ->addColumn('additional_data', 'TEXT NULL', 'NULL', 'component_mode');

        // ---------------------------------------

        $installer->getTableModifier('amazon_order')
            ->addColumn('merchant_fulfillment_data', 'TEXT NULL', 'NULL', 'purchase_create_date', false, false)
            ->addColumn('merchant_fulfillment_label', 'BLOB NULL', 'NULL', 'merchant_fulfillment_data', false, false)
            ->addColumn('is_prime', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'is_afn_channel', true, false)
            ->commit();

        //########################################

        $installer->getMainConfigModifier()
            ->insert('/amazon/repricing/', 'mode', '0', '0 - disable, \r\n1 - enable');

        $installer->getMainConfigModifier()
            ->insert('/amazon/repricing/', 'base_url', 'http://repricer.m2epro.com/', 'Repricing Tool base url');

        $installer->getSynchConfigModifier()
            ->insert('/amazon/defaults/update_repricing/', 'interval', '86400', 'in seconds');

        $installer->getSynchConfigModifier()
            ->insert('/amazon/defaults/update_repricing/', 'mode', '1', '0 - disable, \r\n1 - enable');

        $installer->getSynchConfigModifier()
            ->insert('/amazon/defaults/update_repricing/', 'last_time', NULL, 'Last check time');

        //########################################

        $installer->run(<<<SQL
UPDATE `{$this->_installer->getTable('m2epro_amazon_marketplace')}`
SET `is_merchant_fulfillment_available` = 1
WHERE `marketplace_id` IN (25, 28, 29);
SQL
        );
    }

    //########################################
}