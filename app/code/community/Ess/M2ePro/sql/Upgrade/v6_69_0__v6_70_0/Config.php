<?php

class Ess_M2ePro_Sql_Upgrade_v6_69_0__v6_70_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y24_m11/AddDeliveryDateFromColumnToAmazonOrder',
        );
    }
}