<?php

class Ess_M2ePro_Sql_Upgrade_v6_58_5__v6_58_6_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y24_m02/CombineInactiveEbayProductStatuses',
        );
    }
}
