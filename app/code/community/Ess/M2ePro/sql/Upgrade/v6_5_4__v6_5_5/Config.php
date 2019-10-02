<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Sql_Upgrade_v6_5_4__v6_5_5_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y18_m09/NewUpgradesEngine',
            '@y18_m09/WalmartIsShippingAllowed',
            '@y18_m09/OrderNote',
            '@y19_m01/WalmartAuthentication',
            '@y19_m01/WalmartShippingOverrideService',
            '@y19_m03/WalmartAuthenticationForCA',
            '@y19_m03/WalmartOptionImagesURL',
            '@y19_m03/WalmartOrderActionProcessing',
            '@y19_m03/Maintenance',
        );
    }

    //########################################
}