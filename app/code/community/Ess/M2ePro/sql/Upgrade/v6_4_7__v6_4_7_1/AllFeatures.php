<?php

class Ess_M2ePro_Sql_Upgrade_v6_4_7__v6_4_7_1_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->installer->getMainConfigModifier()->getEntity('/amazon/repricing/', 'mode')->updateValue(1);
    }

    //########################################
}