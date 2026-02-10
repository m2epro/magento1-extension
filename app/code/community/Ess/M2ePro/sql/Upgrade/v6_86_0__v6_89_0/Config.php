<?php

class Ess_M2ePro_Sql_Upgrade_v6_86_0__v6_89_0_Config
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y25_m09/RepricerMaxMinPriceUpdateWorkflow'
        );
    }
}

