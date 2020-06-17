<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_5_0_16__v6_5_6_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('amazon_template_synchronization')
            ->dropColumn('revise_update_details', true, false)
            ->dropColumn('revise_update_images', true, false)
            ->commit();

        $this->_installer->getTableModifier('amazon_listing_product')
            ->addColumn('is_details_data_changed', 'TINYINT(2) UNSIGNED NOT NULL', '0',
                        'online_images_data', false, false)
            ->addColumn('is_images_data_changed', 'TINYINT(2) UNSIGNED NOT NULL', '0',
                        'is_details_data_changed', false, false)
            ->commit();

        $this->_installer->getTableModifier('ebay_indexer_listing_product_parent')
            ->dropColumn('component_mode');

        $this->_installer->getTableModifier('amazon_indexer_listing_product_parent')
            ->dropColumn('component_mode');

        $this->_installer->getTableModifier('amazon_account')
            ->dropColumn('other_listings_move_mode');

        $registryTable = $this->_installer->getTablesObject()->getFullName('registry');

        $this->_installer->getConnection()
            ->update(
                $registryTable,
                array('key' => '/product/variation/vocabulary/server/'),
                array('`key` = ?' => 'amazon_vocabulary_server')
            );

        $this->_installer->getConnection()
            ->update(
                $registryTable,
                array('key' => '/product/variation/vocabulary/local/'),
                array('`key` = ?' => 'amazon_vocabulary_local')
            );

        $this->_installer->getConnection()
            ->delete(
                $registryTable,
                array('`key` IN (?)' => array('walmart_vocabulary_server', 'walmart_vocabulary_local'))
            );

        //----------------------------------------

        $this->_installer->getMainConfigModifier()->updateGroup(
            '/product/variation/vocabulary/attribute/auto_action/',
            array('`group` = ?' => '/amazon/vocabulary/attribute/auto_action/')
        );

        $this->_installer->getMainConfigModifier()->updateGroup(
            '/product/variation/vocabulary/option/auto_action/',
            array('`group` = ?' => '/amazon/vocabulary/option/auto_action/')
        );

        //----------------------------------------

        $this->_installer->getTableModifier('listing_product_instruction')
            ->addColumn('skip_until', 'DATETIME', 'NULL', 'additional_data', true, false)
            ->commit();

        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/magento/global_notifications/', 'mode', '1', '0 - disable, \r\n1 - enable'
        );
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/magento/global_notifications/', 'interval', '86400', 'in seconds'
        );
    }

    //########################################
}