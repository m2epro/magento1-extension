<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Upgrade_v6_12_0__v6_12_1_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y21_m02/EbayManagedPayments',
            '@y21_m02/EbayMotorsAddManagedPayments',

            '@y21_m03/IncludeeBayProductDetails'
        );
    }

    //########################################
}
