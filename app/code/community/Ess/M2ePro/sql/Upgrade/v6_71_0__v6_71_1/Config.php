<?php

class Ess_M2ePro_Sql_Upgrade_v6_71_0__v6_71_1_Config
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y24_m11/RemoveImageRelatedDataFromAmazonProcessing',
        );
    }
}
