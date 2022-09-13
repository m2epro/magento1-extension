<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Upgrade_v6_27_0__v6_28_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y22_m08/AddAfnProductActualQty',
            '@y22_m08/AddIsReplacementColumnToAmazonOrder',
            '@y22_m08/FixNullableGroupsInConfigs',
            '@y22_m09/AddAmazonMarketplaceBelgium',
            '@y22_m09/RemoveHitCounterFromEbayDescriptionPolicy',
        );
    }
}
