<?php

class Ess_M2ePro_Sql_Upgrade_v6_83_0__v6_83_3_Config
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y25_m07/DecreaseAmazonProcessingTime',
        );
    }
}

