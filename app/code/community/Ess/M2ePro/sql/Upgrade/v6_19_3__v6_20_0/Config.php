<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Upgrade_v6_19_3__v6_20_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y22_m02/RemoveForumUrl',
            '@y22_m02/ImportTaxRegistrationId'
        );
    }

    //########################################
}
