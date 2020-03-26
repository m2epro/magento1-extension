<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m01_WalmartShippingOverrideService
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTablesObject()->renameTable(
            'm2epro_walmart_template_selling_format_shipping_override_service',
            'm2epro_walmart_template_selling_format_shipping_override'
        );
    }

    //########################################
}
