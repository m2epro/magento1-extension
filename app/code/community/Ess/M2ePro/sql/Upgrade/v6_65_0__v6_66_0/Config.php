<?php

class Ess_M2ePro_Sql_Upgrade_v6_65_0__v6_66_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y24_m08/AddDateOfInvoiceSendingToAmazonOrder',
        );
    }
}
