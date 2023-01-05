<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Upgrade_v6_31_2__v6_32_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y22_m10/AddRepricingAccountTokenValidityField',
            '@y22_m10/RemoveWalmartLegacySettings',
            '@y22_m10/RemoveRepricingDisablingConfig',
            '@y22_m10/RemovePickupInStoreTablesAndColumns',
            '@y23_m01/FixEbayQtyReservationDays',
        );
    }
}
