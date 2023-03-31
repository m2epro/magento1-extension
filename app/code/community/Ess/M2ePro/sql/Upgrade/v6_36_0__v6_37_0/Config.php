<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Upgrade_v6_36_0__v6_37_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    public function getFeaturesList()
    {
        return array(
            '@y23_m01/AmazonRemoveUnnecessaryData',
            '@y23_m03/RemoveLicenseStatus',
            '@y23_m03/WalmartProductIdentifiers',
            '@y23_m03/RenameClientsToAccounts',
        );
    }
}
