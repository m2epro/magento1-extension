<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_WalmartListChanges extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $isListDateColumnExists = $this->_installer->getTableModifier('walmart_listing_product')
                                                   ->isColumnExists('list_date');

        $this->_installer->getTableModifier('walmart_listing_product')
                         ->addColumn('list_date', 'DATETIME', 'NULL', 'is_missed_on_channel', true, false)
                         ->commit();

        if (!$isListDateColumnExists) {
            $productsStmt = $this->_installer->getConnection()->select()
                ->from(
                    $this->_installer->getTablesObject()->getFullName('listing_product'),
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

                $this->_installer->getConnection()->update(
                    $this->_installer->getTablesObject()->getFullName('walmart_listing_product'),
                    array('list_date' => $additionalData['list_date']),
                    array('listing_product_id = ?' => (int)$row['id'])
                );

                unset($additionalData['list_date']);
                $additionalData = json_encode($additionalData);

                $this->_installer->getConnection()->update(
                    $this->_installer->getTablesObject()->getFullName('listing_product'),
                    array('additional_data' => $additionalData),
                    array('id = ?' => (int)$row['id'])
                );
            }
        }

        // ---------------------------------------

        $isCheckDateColumnExists = $this->_installer->getTableModifier('walmart_listing_product_action_processing_list')
                                                    ->isColumnExists('scheduled_check_date');

        $this->_installer
            ->getTableModifier('walmart_listing_product_action_processing_list')
            ->addColumn('stage', 'TINYINT(2) UNSIGNED NOT NULL', '1', 'sku', true, false)
            ->addColumn('relist_request_pending_single_id', 'INT(11) UNSIGNED', 'NULL', 'stage', false, false)
            ->addColumn('relist_request_data', 'LONGTEXT', 'NULL', 'relist_request_pending_single_id', false, false)
            ->addColumn('relist_configurator_data', 'LONGTEXT', 'NULL', 'relist_request_data', false, false)
            ->addIndex('listing_product_id', false)
            ->commit();

        if ($isCheckDateColumnExists) {
            $this->_installer->run(
                <<<SQL
UPDATE `m2epro_walmart_listing_product_action_processing_list`
SET `stage` = 1 WHERE `scheduled_check_date` IS NULL;

UPDATE `m2epro_walmart_listing_product_action_processing_list`
SET `stage` = 2 WHERE `scheduled_check_date` IS NOT NULL;
SQL
            );
        }

        $this->_installer->getTableModifier('walmart_listing_product_action_processing_list')
                         ->dropColumn('scheduled_check_date');
    }

    //########################################
}
