<?php

class Ess_M2ePro_Sql_Update_y18_m09_WalmartIsShippingAllowed extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $modifier = $this->installer->getTableModifier('walmart_template_selling_format_shipping_override_service');

        if ($modifier->isColumnExists('is_shipping_allowed')) {
            return;
        }

        $modifier->addColumn('is_shipping_allowed', 'VARCHAR(255) NOT NULL', NULL, 'method');

        //Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverrideService::IS_SHIPPING_ALLOWED_ADD_OR_OVERRIDE
        $this->installer->run(<<<SQL
UPDATE `m2epro_walmart_template_selling_format_shipping_override_service`
SET `is_shipping_allowed` = 1;
SQL
        );
    }

    //########################################
}