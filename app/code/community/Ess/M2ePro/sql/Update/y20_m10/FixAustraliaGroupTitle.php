<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m10_FixAustraliaGroupTitle extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('marketplace'),
            array('group_title' => 'Australia Region'),
            array('id = ?' => 35)
        );
    }

    //########################################
}
