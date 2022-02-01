<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m01_ChangeRegistryKey extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('registry'),
            array('key' => '/registration/user_info/'),
            array('`key` = ?' => '/wizard/license_form_data/')
        );
    }

    //########################################
}
