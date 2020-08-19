<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Sql_Upgrade_v6_7_2__v6_8_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y20_m06/WalmartConsumerId',
            '@y20_m06/GeneralConfig',
            '@y20_m06/EbayConfig',
            '@y20_m06/AmazonConfig',
            '@y20_m06/RefundShippingCost',

            '@y20_m07/EbayTemplateStoreCategory',
            '@y20_m07/HashLongtextFields',
            '@y20_m07/WalmartOrderItemQty'
        );
    }

    //########################################
}
