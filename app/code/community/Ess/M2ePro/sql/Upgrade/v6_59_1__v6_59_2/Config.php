<?php

class Ess_M2ePro_Sql_Upgrade_v6_59_1__v6_59_2_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y24_m02/CombineInactiveProductStatuses',
        );
    }
}
