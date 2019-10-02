<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m01_ChangeDevelopVersion
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        Mage::getResourceSingleton('core/resource')->setDbVersion('M2ePro_setup', '1.0.0');
        Mage::getResourceSingleton('core/resource')->setDataVersion('M2ePro_setup', '1.0.0');
    }

    //########################################
}
