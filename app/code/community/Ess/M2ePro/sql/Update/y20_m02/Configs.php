<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m02_Configs extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->delete('/view/ebay/', 'mode');
        $this->_installer->getCacheConfigModifier()->delete(
            '/view/ebay/listing/motors_epids_attribute/',
            'notification_shown'
        );
    }

    //########################################
}