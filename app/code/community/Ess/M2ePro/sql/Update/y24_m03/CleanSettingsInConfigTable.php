<?php

class Ess_M2ePro_Sql_Update_y24_m03_CleanSettingsInConfigTable
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    public function execute()
    {
        $this->_installer->getConnection()->delete(
            $this->_installer->getMainConfigModifier()->getTableName(),
            array('`group` LIKE ?' => '/server/location/%')
        );

        $this->_installer->getMainConfigModifier()
            ->insert('/server/', 'host', 'https://api.m2epro.com/');

    }
}
