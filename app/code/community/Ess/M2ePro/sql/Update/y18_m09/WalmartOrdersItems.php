<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_WalmartOrdersItems extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('walmart_order_item')
                         ->addColumn('merged_walmart_order_item_ids', 'VARCHAR(500)', 'NULL', 'walmart_order_item_id');
    }

    //########################################
}
