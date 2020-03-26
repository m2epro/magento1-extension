<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m12_AmazonReviseDescription
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('amazon_template_synchronization')
            ->addColumn(
                'revise_update_details',
                'TINYINT(2) UNSIGNED NOT NULL',
                '0',
                'revise_update_price_max_allowed_deviation',
                false,
                false
            )
            ->addColumn(
                'revise_update_images',
                'TINYINT(2) UNSIGNED NOT NULL',
                '0',
                'revise_update_details',
                false,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('amazon_listing_product')
            ->dropColumn('is_details_data_changed', true, false)
            ->dropColumn('is_images_data_changed', true, false)
            ->commit();
    }

    //########################################
}
