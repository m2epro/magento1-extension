<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_3_9__v6_4_0_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getSynchConfigModifier()->getEntity('/settings/product_change/', 'max_count')
            ->delete();
        $this->_installer->getSynchConfigModifier()->getEntity('/settings/product_change/', 'max_lifetime')
            ->updateValue('172800');
    }

    //########################################
}