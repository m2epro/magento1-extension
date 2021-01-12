<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m11_SynchronizeInventoryConfigs extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/amazon/listing/synchronize_inventory/', 'interval_per_account', '86400'
        );

        $this->_installer->getMainConfigModifier()->insert(
            '/cron/task/walmart/listing/synchronize_inventory/', 'interval_per_account', '86400'
        );
    }

    //########################################
}
