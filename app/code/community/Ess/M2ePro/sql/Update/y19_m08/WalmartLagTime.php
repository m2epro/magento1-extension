<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m08_WalmartLagTime extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $configModifier = $this->_installer->getMainConfigModifier();
        $configModifier->insert('/walmart/listing/product/action/revise_lag_time/', 'priority_coefficient', '250');
        $configModifier->insert('/walmart/listing/product/action/revise_lag_time/', 'wait_increase_coefficient', '100');
        $configModifier->insert(
            '/walmart/listing/product/action/revise_lag_time/', 'min_allowed_wait_interval', '7200'
        );
    }

    //########################################
}
