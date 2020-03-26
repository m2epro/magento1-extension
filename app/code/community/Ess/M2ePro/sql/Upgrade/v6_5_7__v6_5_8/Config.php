<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Sql_Upgrade_v6_5_7__v6_5_8_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y19_m11/WalmartProductIdOverride',

            '@y19_m12/AmazonReviseDescription',
            '@y19_m12/SynchDataFromM2',
            '@y19_m12/RenameTableIndexerParent',
            '@y19_m12/WalmartReviseDescription',
            '@y19_m12/ServerMessages',

            '@y20_m01/RemoveOutOfStockControl',
            '@y20_m01/FulfillmentCenter',
            '@y20_m01/WalmartRemoveChannelUrl',
            '@y20_m01/EbayOrderUpdates',

            '@y20_m02/ReviewPriorityCoefficients',
            '@y20_m02/RemoveUnsupportedMarketplaces',
            '@y20_m02/EbayCharity',
            '@y20_m02/TemplateReturn',
            '@y20_m02/Configs',
            '@y20_m02/RemoveReviseTotal',
            '@y20_m02/ProductCustomTypes',

            '@y20_m02/ProductCustomTypes',

            '@y20_m03/SynchronizationConfigsModeRemovedFix',
        );
    }

    //########################################
}
