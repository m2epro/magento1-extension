<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m10_AutocompleteRemoved
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function getBackupTables()
    {
        return array(
            'config',
        );
    }

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->delete('/view/amazon/autocomplete/');
        $this->_installer->getMainConfigModifier()->delete('/view/walmart/autocomplete/');
    }

    //########################################
}
