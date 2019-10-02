<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_5_3_0__v6_5_4_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;

        //-- WalmartOrdersItems
        //########################################

        $installer->getTableModifier('walmart_order_item')
                  ->addColumn('merged_walmart_order_item_ids', 'VARCHAR(500)', 'NULL', 'walmart_order_item_id');

        //########################################
    }

    //########################################
}