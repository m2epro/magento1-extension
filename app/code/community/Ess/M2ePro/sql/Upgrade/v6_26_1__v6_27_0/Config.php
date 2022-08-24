<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Upgrade_v6_26_1__v6_27_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y22_m08/AddAmazonMarketplacesBrSgInAe',
            '@y22_m08/FixDevKeyForJapanAmazonMarketplace',
        );
    }
}
