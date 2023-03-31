<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m03_RenameClientsToAccounts extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('config'),
            array(
                'key' => 'accounts_url',
                'value' => 'https://accounts.m2e.cloud/'
            ),
            array('`key` = ?' => 'clients_portal_url')
        );
    }
}
