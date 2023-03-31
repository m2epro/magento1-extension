<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m03_RemoveLicenseStatus extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getMainConfigModifier()->delete('/license/', 'status');
    }
}
