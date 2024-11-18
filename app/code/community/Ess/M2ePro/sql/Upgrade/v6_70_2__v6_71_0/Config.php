<?php

class Ess_M2ePro_Sql_Upgrade_v6_70_2__v6_71_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y24_m10/AddAmazonProductTypes',
            '@y24_m10/AddAmazonShippingTemplates',
        );
    }
}