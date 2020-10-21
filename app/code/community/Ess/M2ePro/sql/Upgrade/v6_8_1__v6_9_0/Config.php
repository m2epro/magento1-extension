<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Sql_Upgrade_v6_8_1__v6_9_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y20_m07/WalmartKeywordsFields',
            '@y20_m07/EbayShowSettingsStep',
            '@y20_m07/SellOnAnotherSite',

            '@y20_m08/EbayManagedPayments',
            '@y20_m08/EbayShippingSurcharge',
            '@y20_m08/GroupedProduct',
            '@y20_m08/AmazonSkipTax',
            '@y20_m08/AmazonTR',
            '@y20_m08/InventorySynchronization',

            '@y20_m09/AmazonSE',

            '@y20_m10/SellOnAnotherSite',
            '@y20_m10/FixAustraliaGroupTitle',
        );
    }

    //########################################
}
