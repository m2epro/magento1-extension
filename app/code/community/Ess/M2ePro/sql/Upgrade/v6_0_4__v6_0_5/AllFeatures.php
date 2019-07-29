<?php

class Ess_M2ePro_Sql_Upgrade_v6_0_4__v6_0_5_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->installer;
        $connection = $installer->getConnection();

        $tempTable = $installer->getTable('m2epro_ebay_template_shipping');

        if ($connection->tableColumnExists($tempTable, 'excluded_locations') === false) {
            $connection->addColumn(
                $tempTable,
                'excluded_locations',
                'TEXT DEFAULT NULL AFTER `international_shipping_combined_discount_profile_id`'
            );
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_listing_auto_category');

        if ($connection->tableColumnExists($tempTable, 'adding_duplicate') !== false) {
            $connection->dropColumn(
                $tempTable,
                'adding_duplicate'
            );
        }
    }

    //########################################
}