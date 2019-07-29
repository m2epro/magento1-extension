<?php

class Ess_M2ePro_Sql_Upgrade_v6_5_0_15__v6_5_0_16_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->installer;
        $connection = $installer->getConnection();

        //-- UpdateIsNewAsinAvailableMXN
        //########################################

        $connection->update(
            $installer->getTablesObject()->getFullName('amazon_marketplace'),
            array('is_new_asin_available' => 1),
            array('marketplace_id = ?' => 34)
        );

        //-- AmazonB2BForSpainAndItaly
        //########################################

        $connection->update(
            $installer->getTablesObject()->getFullName('amazon_marketplace'),
            array('is_business_available' => 1,
                  'is_product_tax_code_policy_available' => 1),
            array('marketplace_id IN (?)' => array(26, 30, 31))
        );
    }

    //########################################
}