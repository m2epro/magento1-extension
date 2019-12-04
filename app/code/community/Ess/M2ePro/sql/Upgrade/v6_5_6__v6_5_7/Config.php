<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Sql_Upgrade_v6_5_6__v6_5_7_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y19_m08/WalmartLagTime',
            '@y19_m10/ConfigsNoticeRemoved',
            '@y19_m10/AdvancedSynchRules',
            '@y19_m10/SynchronizationConfigsModeRemoved',
            '@y19_m10/IsAllowedComponentRemoved',
            '@y19_m10/AutocompleteRemoved',
            '@y19_m10/Configs',
            '@y19_m10/DropEbayTranslations',
            '@y19_m10/DropEbaySimpleMode',
            '@y19_m10/StreetNameToEbayDictionaryMotorEpid',
            '@y19_m10/AddEpidsAu',
            '@y19_m11/ProductsStatisticsImprovements',
            '@y19_m11/LogsImprovements',
        );
    }

    //########################################
}