<?php

class Ess_M2ePro_Sql_Update_y24_m02_RemoveInstallationKeyFromConfigTable
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $connection = $this->_installer->getConnection();
        $connection->delete(
            $this->_installer->getFullTableName('config'),
            array('`key` = ?' => 'installation_key')
        );
    }
}
