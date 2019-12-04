<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m11_ProductsStatisticsImprovements
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getCacheConfigModifier()->delete('/servicing/statistic/', 'last_run');
    }

    //########################################
}
