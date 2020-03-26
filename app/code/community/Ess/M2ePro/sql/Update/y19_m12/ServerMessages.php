<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m12_ServerMessages
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getPrimaryConfigModifier()->delete('/server/', 'messages');
    }

    //########################################
}
