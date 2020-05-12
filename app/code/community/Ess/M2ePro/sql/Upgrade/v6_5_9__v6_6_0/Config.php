<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Sql_Upgrade_v6_5_9__v6_6_0_Config extends Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return array(
            '@y20_m03/AmazonSendInvoice',
            '@y20_m03/SynchronizationConfigsModeRemovedFix',
            '@y20_m03/CronStrategy',
            '@y20_m03/AmazonNL',
            '@y20_m03/RemoveOldWizards',

            '@y20_m04/RemoveModePrefixFromChannelAccounts',
            '@y20_m04/BrowsenodeIdFix',

            '@y20_m05/DisableUploadInvoicesAvailableNl',
        );
    }

    //########################################
}
