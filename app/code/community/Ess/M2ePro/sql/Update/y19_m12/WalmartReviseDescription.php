<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m12_WalmartReviseDescription
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('walmart_template_synchronization')
            ->addColumn(
                'revise_update_details',
                'TINYINT(2) UNSIGNED NOT NULL',
                '0',
                'revise_update_promotions',
                false,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('walmart_listing_product')
            ->dropColumn('is_details_data_changed', true, false)
            ->commit();
    }

    //########################################
}
