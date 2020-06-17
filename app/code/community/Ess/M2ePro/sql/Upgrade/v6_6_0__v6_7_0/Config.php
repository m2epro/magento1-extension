<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Sql_Upgrade_v6_6_0__v6_7_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y19_m12/EbayCategories',
            '@y19_m12/RemoveReviseTotal',
            '@y19_m12/WalmartReviseDescription',

            '@y20_m04/SaveEbayCategory',

            '@y20_m05/Logs',
            '@y20_m05/RemoveMagentoQtyRules',
            '@y20_m05/RemovePriceDeviationRules',
            '@y20_m05/PrimaryConfigs',
            '@y20_m05/ConvertIntoInnoDB',
            '@y20_m05/CacheConfigs',

            '@y20_m06/RemoveCronDomains'
        );
    }

    //########################################
}
