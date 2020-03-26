<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m10_ConfigsNoticeRemoved extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('config')->dropColumn('notice');
        $this->_installer->getTableModifier('primary_config')->dropColumn('notice');
        $this->_installer->getTableModifier('cache_config')->dropColumn('notice');
    }

    //########################################
}
