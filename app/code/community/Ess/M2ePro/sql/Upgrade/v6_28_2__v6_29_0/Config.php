<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Upgrade_v6_28_2__v6_29_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y22_m09/AddWalmartCustomerOrderId',
            '@y22_m09/UpdateConfigAttrSupportUrl',
        );
    }
}
