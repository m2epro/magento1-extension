<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m10_StreetNameToEbayDictionaryMotorEpid
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function getBackupTables()
    {
        return array(
            'ebay_dictionary_motor_epid'
        );
    }

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_dictionary_motor_epid')
            ->addColumn('street_name', 'VARCHAR(255)', null, 'submodel', false, false)
            ->addIndex('street_name', false)
            ->commit();
    }

    //########################################
}