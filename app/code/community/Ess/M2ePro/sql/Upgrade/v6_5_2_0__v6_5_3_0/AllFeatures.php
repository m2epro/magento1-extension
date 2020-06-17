<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_5_2_0__v6_5_3_0_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        //-- WalmartTaxCodes
        //########################################

        $installer->getTableModifier('walmart_dictionary_marketplace')
            ->addColumn('tax_codes', 'LONGTEXT', NULL, 'product_data');

        // -- InstructionsInTheFuture
        //########################################

        $installer->getTableModifier('listing_product_instruction')
            ->addColumn('skip_until', 'DATETIME', 'NULL', 'additional_data', true, false)
            ->commit();

        // -- GlobalNotifications
        //########################################

        $installer->getMainConfigModifier()
            ->insert('/cron/task/magento/global_notifications/', 'mode', '1', '0 - disable, \r\n1 - enable');
        $installer->getMainConfigModifier()
            ->insert('/cron/task/magento/global_notifications/', 'interval', '86400', 'in seconds');

        // -- WalmartListChanges
        //########################################

        $isListDateColumnExists = $installer->getTableModifier('walmart_listing_product')
            ->isColumnExists('list_date');

        $installer->getTableModifier('walmart_listing_product')
            ->addColumn('list_date', 'DATETIME', 'NULL', 'is_missed_on_channel', true, false)
            ->commit();

        if (!$isListDateColumnExists) {

            $productsStmt = $connection->select()
                ->from(
                    $installer->getTablesObject()->getFullName('listing_product'),
                    array('id', 'additional_data')
                )
                ->where('component_mode = ?', 'walmart')
                ->where('additional_data LIKE ?', '%"list_date":%')
                ->query();

            while ($row = $productsStmt->fetch()) {

                $additionalData = (array)json_decode($row['additional_data'], true);
                if (empty($additionalData['list_date'])) {
                    continue;
                }

                $connection->update(
                    $installer->getTablesObject()->getFullName('walmart_listing_product'),
                    array('list_date' => $additionalData['list_date']),
                    array('listing_product_id = ?' => (int)$row['id'])
                );

                unset($additionalData['list_date']);
                $additionalData = json_encode($additionalData);

                $connection->update(
                    $installer->getTablesObject()->getFullName('listing_product'),
                    array('additional_data' => $additionalData),
                    array('id = ?' => (int)$row['id'])
                );
            }
        }

        // ---------------------------------------

        $isCheckDateColumnExists = $installer->getTableModifier('walmart_listing_product_action_processing_list')
            ->isColumnExists('scheduled_check_date');

        $installer->getTableModifier('walmart_listing_product_action_processing_list')
            ->addColumn('stage', 'TINYINT(2) UNSIGNED NOT NULL', '1', 'sku', true, false)
            ->addColumn('relist_request_pending_single_id', 'INT(11) UNSIGNED', 'NULL', 'stage', false, false)
            ->addColumn('relist_request_data', 'LONGTEXT', 'NULL', 'relist_request_pending_single_id', false, false)
            ->addColumn('relist_configurator_data', 'LONGTEXT', 'NULL', 'relist_request_data', false, false)
            ->addIndex('listing_product_id', false)
            ->commit();

        if ($isCheckDateColumnExists) {

            $installer->run(<<<SQL
UPDATE `{$this->_installer->getTable('m2epro_walmart_listing_product_action_processing_list')}`
SET `stage` = 1 WHERE `scheduled_check_date` IS NULL;

UPDATE `{$this->_installer->getTable('m2epro_walmart_listing_product_action_processing_list')}`
SET `stage` = 2 WHERE `scheduled_check_date` IS NOT NULL;
SQL
            );
        }

        $installer->getTableModifier('walmart_listing_product_action_processing_list')
            ->dropColumn('scheduled_check_date');
    }

    //########################################
}