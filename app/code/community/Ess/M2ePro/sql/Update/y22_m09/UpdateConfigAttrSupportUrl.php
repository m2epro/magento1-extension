<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m09_UpdateConfigAttrSupportUrl extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function  execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('config'),
            array( 'value' => 'https://m2epro.freshdesk.com'),
            array('`key` = ?' => 'support_url')
        );
    }
}