<?php

class Ess_M2ePro_Sql_Upgrade_v6_5_0_7__v6_5_0_8_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->installer->getTableModifier('system_log')
            ->changeColumn('description', 'LONGTEXT', 'NULL');
    }

    //########################################
}