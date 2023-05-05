<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Upgrade_v6_38_0__v6_39_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y23_m04/RenameNotificationConfig',
            '@y23_m04/SetIsVatEbayMarketplacePL',
            '@y23_m04/ChangeTypeProductAddIds',
            '@y23_m04/UpdateEbayVatMode',
        );
    }
}