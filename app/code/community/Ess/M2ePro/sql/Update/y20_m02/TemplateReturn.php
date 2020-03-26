<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m02_TemplateReturn extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTablesObject()->renameTable(
            'ebay_template_return',
            'ebay_template_return_policy'
        );

        // ---------------------------------------

        $ebayListingTableModifier =  $this->_installer->getTableModifier('ebay_listing');

        $ebayListingTableModifier->renameColumn(
            'template_return_mode',
            'template_return_policy_mode',
            true,
            false
        );
        $ebayListingTableModifier->renameColumn(
            'template_return_id',
            'template_return_policy_id',
            true,
            false
        );
        $ebayListingTableModifier->renameColumn(
            'template_return_custom_id',
            'template_return_policy_custom_id',
            true,
            false
        );
        $ebayListingTableModifier->commit();

        // ---------------------------------------

        $ebayListingProductTableModifier =  $this->_installer->getTableModifier('ebay_listing_product');

        $ebayListingProductTableModifier->renameColumn(
            'template_return_mode',
            'template_return_policy_mode',
            true,
            false
        );
        $ebayListingProductTableModifier->renameColumn(
            'template_return_id',
            'template_return_policy_id',
            true,
            false
        );
        $ebayListingProductTableModifier->renameColumn(
            'template_return_custom_id',
            'template_return_policy_custom_id',
            true,
            false
        );
        $ebayListingProductTableModifier->commit();
    }

    //########################################
}
