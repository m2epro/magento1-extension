<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m04_SaveEbayCategory extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_listing_other')
            ->addColumn(
                'online_main_category',
                'VARCHAR(255)',
                'NULL',
                'online_bids',
                false,
                false
            )
            ->addColumn(
                'online_categories_data',
                'LONGTEXT',
                'NULL',
                'online_main_category',
                false,
                false
            )
            ->commit();
    }

    //########################################
}
