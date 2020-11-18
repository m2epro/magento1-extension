<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m05_RemovePriceDeviationRules extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        foreach (array('ebay', 'amazon', 'walmart') as $component) {
            $this->_installer->getTableModifier("{$component}_template_synchronization")
                ->dropColumn('revise_update_price_max_allowed_deviation_mode', true, false)
                ->dropColumn('revise_update_price_max_allowed_deviation', true, false)
                ->commit();
        }

        $this->_installer->getConnection()
            ->delete(
                $this->_installer->getFullTableName('listing_product_instruction'),
                array('type = ?' => 'template_synchronization_revise_price_settings_changed')
            );
    }

    //########################################
}
