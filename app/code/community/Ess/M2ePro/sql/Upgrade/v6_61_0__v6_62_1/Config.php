<?php

class Ess_M2ePro_Sql_Upgrade_v6_61_0__v6_62_1_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y24_m06/RemoveEbayCharity',
        );
    }
}
