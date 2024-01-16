<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_NewDevelopmentUpgrades extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installedFeaturesFile = Mage::getBaseDir() . '/var/M2ePro/development/installed_upgrades.json';
        $installedFeatures = json_decode(file_get_contents($installedFeaturesFile), true);

        foreach ($installedFeatures as $installedFeatureGroup => $installedFeaturesData) {
            if ($installedFeatureGroup !== 'y18_m09') {
                unset($installedFeatures[$installedFeatureGroup]);
            }
        }

        file_put_contents($installedFeaturesFile, json_encode($installedFeatures));
    }

    //########################################
}
