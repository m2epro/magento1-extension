<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_4_14__v6_5_0_16_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $tablesList = array(
            'processing_request',
            'product_change',
            'locked_object',
            'lock_item',

            'cache_config',
            'primary_config',
            'config',
            'synchronization_config'
        );

        $backup = new Ess_M2ePro_Model_Upgrade_Backup(array($this->_installer, $tablesList));
        if (!$backup->isExists()) {
            $backup->create();
        }

        //########################################

        $schema = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_Schema($this->_installer);

        //########################################

        $schema->schemaCreate();

        //########################################

        $migration = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_General($this->_installer);
        $migration->run();

        $migration = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_NewProcessing($this->_installer);
        $migration->run();

        $migration = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_Configs($this->_installer);
        $migration->run();

        $migration = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_EbayTables($this->_installer);
        $migration->run();

        $migration = new Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_AmazonTables($this->_installer);
        $migration->run();

        //########################################

        $schema->schemaDelete();
    }

    //########################################
}