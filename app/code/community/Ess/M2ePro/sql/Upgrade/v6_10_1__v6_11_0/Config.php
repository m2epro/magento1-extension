<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Upgrade_v6_10_1__v6_11_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y20_m11/EbayOrderCancelRefund',
            '@y20_m11/AmazonDuplicatedMarketplaceFeature',

            '@y21_m01/EbayRemoveClickAndCollect',
            '@y21_m01/WalmartCancelRefundOption',
            '@y21_m01/AmazonJP'
        );
    }

    //########################################
}
