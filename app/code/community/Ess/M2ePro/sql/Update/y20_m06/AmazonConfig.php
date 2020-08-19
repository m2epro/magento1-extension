<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m06_AmazonConfig extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->getEntity('/amazon/business/', 'mode')
            ->updateGroup('/amazon/configuration/')
            ->updateKey('business_mode');
    }

    //########################################
}
