<?php

class Ess_M2ePro_Sql_Upgrade_v6_73_1__v6_74_0_Config
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y24_m12/AddAmazonMarketplaceSaudiArabia',
            '@y24_m12/AddAmazonMarketplaceIreland',
        );
    }
}

