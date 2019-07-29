<?php

class Ess_M2ePro_Sql_Upgrade_v5_2_5__v6_0_0_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        /** @var $migrationInstance Ess_M2ePro_Model_Upgrade_Migration_ToVersion6 */
        $migrationInstance = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion6');
        $migrationInstance->setInstaller($this->installer);

        $migrationInstance->backup();
        $migrationInstance->migrate();

    }

    //########################################
}