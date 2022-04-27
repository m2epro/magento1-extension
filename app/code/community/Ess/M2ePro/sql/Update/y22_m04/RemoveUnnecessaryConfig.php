<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m04_RemoveUnnecessaryConfig extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->delete('/server/exceptions/', 'send');
        $this->_installer->getMainConfigModifier()->delete('/server/exceptions/', 'filters');
        $this->_installer->getMainConfigModifier()->delete('/server/fatal_error/', 'send');
        $this->_installer->getMainConfigModifier()->delete('/server/logging/', 'send');
    }

    //########################################
}
