<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_Dev_ReinstallHook extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $fileName = Mage::getBaseDir() .'/reinstall_module/ChangeServerLocation.php';
        if (!is_file($fileName)) {
            return;
        }

        $fileContent = '<?php
$installer = new Ess_M2ePro_Model_Upgrade_MySqlSetup("M2ePro_setup");

$myHost = $_SERVER["HTTP_HOST"];
$installer->getPrimaryConfigModifier()
    ->getEntity("/server/location/1/", "baseurl")
    ->updateValue("http://{$myHost}/server/worker/public/");';

        file_put_contents($fileName, $fileContent);
    }

    //########################################
}