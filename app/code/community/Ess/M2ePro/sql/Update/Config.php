<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Sql_Update_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            'dev' => array(
                'ReinstallHook',
                'EnabledEpidsAu'
            ),
            'y18_m09' => array(
                'AddWalmart',
                'ProductVocabulary',
                'InternationalReturns',
                'WalmartActionProcessorFixes',
                'WalmartOrderCancel',
                'WalmartTaxCodes',
                'InstructionsInTheFuture',
                'GlobalNotifications',
                'WalmartListChanges',
                'WalmartIsShippingAllowed',
                'NewDevelopmentUpgrades',
                'NewUpgradesEngine',
                'OrderNote',
                'WalmartOrdersItems'
            ),
            'y19_m01' => array(
                'WalmartAuthentication',
                'WalmartShippingOverrideService',
                'ChangeDevelopVersion'
            ),
            'y19_m03' => array(
              'WalmartAuthenticationForCA',
              'WalmartOptionImagesURL',
              'WalmartOrderActionProcessing',
              'Maintenance'
            ),
            'y19_m08' => array(
              'WalmartLagTime'
            ),
            'y19_m10' => array(
                'ConfigsNoticeRemoved',
                'AdvancedSynchRules',
                'SynchronizationConfigsModeRemoved',
                'IsAllowedComponentRemoved',
                'AutocompleteRemoved',
                'Configs',
                'DropEbayTranslations',
                'DropEbaySimpleMode',
                'StreetNameToEbayDictionaryMotorEpid',
                'AddEpidsAu'
            ),
            'y19_m11' => array(
                'ProductsStatisticsImprovements',
                'LogsImprovements'
            ),
        );
    }

    //########################################

    public function getMultiRunFeaturesList()
    {
        return array();
    }

    //########################################
}
