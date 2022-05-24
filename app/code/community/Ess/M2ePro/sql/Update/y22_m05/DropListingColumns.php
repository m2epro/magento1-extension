<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m05_DropListingColumns extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('listing')
            ->dropColumn('products_total_count', true, false)
            ->dropColumn('products_active_count', true, false)
            ->dropColumn('products_inactive_count', true, false)
            ->dropColumn('items_active_count', true, false)
            ->commit();

        $this->_installer->getTableModifier('ebay_listing')
            ->dropColumn('products_sold_count', true, false)
            ->dropColumn('items_sold_count', true, false)
            ->commit();
    }

    //########################################
}
