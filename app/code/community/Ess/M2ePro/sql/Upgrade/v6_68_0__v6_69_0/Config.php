<?php

class Ess_M2ePro_Sql_Upgrade_v6_68_0__v6_69_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y24_m09/AddWalmartProductTypes',
            '@y24_m10/EbayAccountAddSiteColumn',
        );
    }
}
