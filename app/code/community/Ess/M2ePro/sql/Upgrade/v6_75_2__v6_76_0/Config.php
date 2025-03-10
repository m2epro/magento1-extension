<?php

class Ess_M2ePro_Sql_Upgrade_v6_75_2__v6_76_0_Config
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y25_m03/AddAmazonOriginalOrderIdColumn',
        );
    }
}

