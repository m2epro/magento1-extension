<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Upgrade_v6_30_0__v6_31_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y22_m09/AddIsCriticalErrorReceivedFlag',
            '@y22_m10/AddEpidsForItaly',
            '@y22_m10/RemoveEpidsForAustralia',
            '@y22_m10/UpdateAmazonMarketplace',
        );
    }
}
