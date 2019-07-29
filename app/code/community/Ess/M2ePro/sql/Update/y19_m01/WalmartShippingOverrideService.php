<?php

class Ess_M2ePro_Sql_Update_y19_m01_WalmartShippingOverrideService
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $renameFrom = $this->installer->getTable('m2epro_walmart_template_selling_format_shipping_override_service');
        $renameTo = $this->installer->getTable('m2epro_walmart_template_selling_format_shipping_override');

        if ($this->installer->tableExists($renameTo) === false &&
            $this->installer->tableExists($renameFrom) !== false) {

            $this->installer->run(<<<SQL
RENAME TABLE `m2epro_walmart_template_selling_format_shipping_override_service` 
TO `m2epro_walmart_template_selling_format_shipping_override`;
SQL
            );
        }
    }

    //########################################
}