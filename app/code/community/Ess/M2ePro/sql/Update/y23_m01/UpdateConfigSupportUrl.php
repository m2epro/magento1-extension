<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m01_UpdateConfigSupportUrl extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function  execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('config'),
            array('value' => 'https://help.m2epro.com'),
            array('`key` = ?' => 'support_url')
        );
    }
}
