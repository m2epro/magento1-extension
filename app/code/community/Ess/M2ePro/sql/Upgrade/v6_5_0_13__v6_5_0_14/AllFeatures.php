<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_5_0_13__v6_5_0_14_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        /** EbayApiTokenNotifications */
        //########################################

        $installer->getTableModifier('ebay_account')
            ->changeColumn('sell_api_token_session', 'VARCHAR(255)', 'NULL', NULL, false)
            ->changeColumn('sell_api_token_expired_date', 'DATETIME', 'NULL', NULL, false)
            ->commit();

        //########################################

        $installer->run(<<<SQL
UPDATE `m2epro_amazon_template_synchronization`
SET `stop_mode` = 1
WHERE (`stop_status_disabled`+`stop_out_off_stock`+`stop_qty_magento`+`stop_qty_calculated`) > 0;
SQL
        );

        $installer->run(<<<SQL
UPDATE `m2epro_ebay_template_synchronization`
SET `stop_mode` = 1
WHERE (`stop_status_disabled`+`stop_out_off_stock`+`stop_qty_magento`+`stop_qty_calculated`) > 0;
SQL
        );

        //########################################

        $installer->getTableModifier('amazon_listing_product_repricing')
            ->addColumn('last_updated_regular_price', 'DECIMAL(12, 4) UNSIGNED', 'NULL',
                        'online_max_price', false, false)
            ->addColumn('last_updated_min_price', 'DECIMAL(12, 4) UNSIGNED', 'NULL',
                        'last_updated_regular_price', false, false)
            ->addColumn('last_updated_max_price', 'DECIMAL(12, 4) UNSIGNED', 'NULL',
                        'last_updated_min_price', false, false)
            ->addColumn('last_updated_is_disabled', 'TINYINT(2) UNSIGNED', 'NULL',
                        'last_updated_max_price', false, false)
            ->commit();

        /** Amazon AU Automatic Token Retrieving */
        //########################################

        $connection->update($installer->getTablesObject()->getFullName('amazon_marketplace'),
                            array('is_automatic_token_retrieving_available' => 1),
                            array('marketplace_id = ?' => 35) // Australia
        );

        /** MovingEnvironmentVariable */
        //########################################

        $installer->getMainConfigModifier()->insert(
            NULL, 'environment', 'production', "Available values:\r\nproduction\r\ndevelopment\r\ntesting"
        );

        /** SaveOnlineIdentifiers */
        //########################################

        $queryStmt = $installer->getConnection()
            ->select()
            ->from(
                $installer->getTablesObject()->getFullName('listing_product_variation'),
                array('id', 'additional_data')
            )
            ->where("component_mode = 'ebay'")
            ->where("additional_data LIKE '%ebay_mpn_value%'")
            ->query();

        while ($row = $queryStmt->fetch()) {

            $additionalData = (array)@json_decode($row['additional_data'], true);
            $additionalData['online_product_details']['mpn'] = $additionalData['ebay_mpn_value'];
            unset($additionalData['ebay_mpn_value']);
            $additionalData = json_encode($additionalData);

            $connection->update(
                $installer->getTablesObject()->getFullName('listing_product_variation'),
                array('additional_data' => $additionalData),
                array('id = ?' => (int)$row['id'])
            );
        }
    }

    //########################################
}