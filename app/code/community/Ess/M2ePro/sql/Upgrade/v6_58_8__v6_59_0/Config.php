<?php

class Ess_M2ePro_Sql_Upgrade_v6_58_8__v6_59_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y24_m03/RemoveEbayTradingToken',
            '@y24_m03/CleanSettingsInConfigTable',
        );
    }
}
