<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m10_WalmartCustomCarrier extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('walmart_account')->addColumn(
            'other_carriers',
            'TEXT',
            'NULL',
            'magento_orders_settings'
        );
    }

    //########################################
}
