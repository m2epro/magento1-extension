<?php

class Ess_M2ePro_Sql_Upgrade_v6_77_3__v6_78_0_Config
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y25_m04/AddConditionColumnsIntoWalmartListingTable',
            '@y25_m04/AddIntoWalmartProductIsNotMappedToExistingChannelItemColumn',
        );
    }
}

